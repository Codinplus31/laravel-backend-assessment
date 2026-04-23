<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Get all products for a specific vendor (paginated).
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all active products with optional search (paginated).
     */
    public function getActiveProducts(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a product by ID.
     */
    public function findById(int $id): ?Product;

    /**
     * Find an active product by ID.
     */
    public function findActiveById(int $id): ?Product;

    /**
     * Find a product by ID with pessimistic lock.
     */
    public function findByIdForUpdate(int $id): ?Product;

    /**
     * Create a new product.
     */
    public function create(array $data): Product;

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool;

    /**
     * Decrement stock atomically.
     */
    public function decrementStock(Product $product, int $quantity): bool;
}
