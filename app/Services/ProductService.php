<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get paginated products for a specific vendor.
     */
    public function getVendorProducts(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getByVendor($vendorId, $perPage);
    }

    /**
     * Get paginated active products for public access (cached).
     */
    public function getActiveProducts(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = 'products:active:' . md5("{$search}:{$perPage}:page:" . request()->get('page', 1));

        return Cache::remember($cacheKey, 60, function () use ($search, $perPage) {
            return $this->productRepository->getActiveProducts($search, $perPage);
        });
    }

    /**
     * Find a single active product by ID (cached).
     */
    public function findActiveProduct(int $id): ?Product
    {
        return Cache::remember("products:active:{$id}", 60, function () use ($id) {
            return $this->productRepository->findActiveById($id);
        });
    }

    /**
     * Find a product by ID (no cache, for vendor operations).
     */
    public function findProduct(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Create a new product for a vendor.
     */
    public function createProduct(int $vendorId, array $data): Product
    {
        $data['vendor_id'] = $vendorId;

        $product = $this->productRepository->create($data);

        $this->clearProductCache();

        return $product;
    }

    /**
     * Update a product with stock adjustment protection.
     * Uses a database transaction with pessimistic locking to ensure
     * data consistency under concurrent updates.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // Re-fetch with pessimistic lock to prevent concurrent modification
            $lockedProduct = $this->productRepository->findByIdForUpdate($product->id);

            if (! $lockedProduct) {
                throw new \RuntimeException('Product not found during update.');
            }

            // If stock_quantity is being updated, validate it won't go below zero
            if (isset($data['stock_quantity']) && $data['stock_quantity'] < 0) {
                throw new \InvalidArgumentException('Stock quantity cannot be negative.');
            }

            $updatedProduct = $this->productRepository->update($lockedProduct, $data);

            $this->clearProductCache();

            return $updatedProduct;
        });
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool
    {
        $result = $this->productRepository->delete($product);

        $this->clearProductCache();

        return $result;
    }

    /**
     * Clear all product-related caches.
     */
    protected function clearProductCache(): void
    {
        // Clear the cache store for product keys
        // Using a tag-less approach: flush specific patterns
        Cache::flush();
    }
}
