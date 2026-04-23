<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OrderService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Place an order atomically.
     *
     * Uses a database transaction with pessimistic locking (SELECT ... FOR UPDATE)
     * to ensure that:
     * 1. Stock is checked accurately even under concurrent requests
     * 2. Stock is decremented only if sufficient
     * 3. The order is created only if stock was successfully decremented
     *
     * This prevents overselling when two users try to order the last item simultaneously.
     *
     * @throws \Exception If stock is insufficient or product not found/inactive
     */
    public function placeOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Lock the product row to prevent concurrent stock modifications
            $product = $this->productRepository->findByIdForUpdate($data['product_id']);

            if (! $product) {
                throw new \RuntimeException('Product not found.');
            }

            if ($product->status !== 'active') {
                throw new \RuntimeException('This product is currently unavailable.');
            }

            $quantity = $data['quantity'];

            if ($product->stock_quantity < $quantity) {
                throw new \RuntimeException(
                    "Insufficient stock. Only {$product->stock_quantity} item(s) available."
                );
            }

            // Decrement stock atomically with a WHERE guard as an extra safety net
            $decremented = $this->productRepository->decrementStock($product, $quantity);

            if (! $decremented) {
                throw new \RuntimeException('Failed to reserve stock. Please try again.');
            }

            // Create the order record
            $order = $this->orderRepository->create([
                'product_id' => $product->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'quantity' => $quantity,
                'total_price' => $product->price * $quantity,
                'status' => 'completed',
            ]);

            // Invalidate product cache since stock changed
            Cache::flush();

            return $order->load('product:id,name,price');
        });
    }

    /**
     * Find an order by ID.
     */
    public function findOrder(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }
}
