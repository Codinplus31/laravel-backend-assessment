<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * List all products for the authenticated vendor (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $products = $this->productService->getVendorProducts(
            $request->user()->id,
            $perPage
        );

        return $this->successResponse($products, 'Products retrieved successfully.');
    }

    /**
     * Create a new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(
            $request->user()->id,
            $request->validated()
        );

        return $this->successResponse($product, 'Product created successfully.', 201);
    }

    /**
     * View a single product owned by the vendor.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->findProduct($id);

        if (! $product) {
            return $this->errorResponse('Product not found.', 404);
        }

        // Ensure vendor can only view their own products
        if ($product->vendor_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized. This product does not belong to you.', 403);
        }

        return $this->successResponse($product, 'Product retrieved successfully.');
    }

    /**
     * Update a product owned by the vendor.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->findProduct($id);

        if (! $product) {
            return $this->errorResponse('Product not found.', 404);
        }

        if ($product->vendor_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized. This product does not belong to you.', 403);
        }

        $updated = $this->productService->updateProduct($product, $request->validated());

        return $this->successResponse($updated, 'Product updated successfully.');
    }

    /**
     * Delete a product owned by the vendor.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->findProduct($id);

        if (! $product) {
            return $this->errorResponse('Product not found.', 404);
        }

        if ($product->vendor_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized. This product does not belong to you.', 403);
        }

        $this->productService->deleteProduct($product);

        return $this->successResponse(null, 'Product deleted successfully.');
    }
}
