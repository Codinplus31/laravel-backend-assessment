<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * Create a new order.
     */
    public function create(array $data): Order;

    /**
     * Find an order by ID.
     */
    public function findById(int $id): ?Order;
}
