<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $model
    ) {}

    /**
     * Get all products for a specific vendor (paginated).
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('vendor_id', $vendorId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all active products with optional search (paginated).
     */
    public function getActiveProducts(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->active()
            ->search($search)
            ->with('vendor:id,name')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a product by ID.
     */
    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    /**
     * Find an active product by ID (with vendor info).
     */
    public function findActiveById(int $id): ?Product
    {
        return $this->model
            ->active()
            ->with('vendor:id,name')
            ->find($id);
    }

    /**
     * Find a product by ID with pessimistic lock for concurrent updates.
     */
    public function findByIdForUpdate(int $id): ?Product
    {
        return $this->model
            ->lockForUpdate()
            ->find($id);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Decrement stock atomically using the query builder.
     */
    public function decrementStock(Product $product, int $quantity): bool
    {
        $affected = $this->model
            ->where('id', $product->id)
            ->where('stock_quantity', '>=', $quantity)
            ->update([
                'stock_quantity' => \DB::raw("stock_quantity - {$quantity}")
            ]);

        return $affected > 0;
    }
}
