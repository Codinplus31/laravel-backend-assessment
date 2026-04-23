<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        protected Order $model
    ) {}

    /**
     * Create a new order.
     */
    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    /**
     * Find an order by ID with product details.
     */
    public function findById(int $id): ?Order
    {
        return $this->model
            ->with('product:id,name,price,vendor_id')
            ->find($id);
    }
}
