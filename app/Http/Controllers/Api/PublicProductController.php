<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * List all active products (paginated, cached).
     * Supports ?search=keyword for name-based search.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $perPage = $request->integer('per_page', 15);

        $products = $this->productService->getActiveProducts($search, $perPage);

        return $this->successResponse($products, 'Products retrieved successfully.');
    }

    /**
     * View a single active product.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findActiveProduct($id);

        if (! $product) {
            return $this->errorResponse('Product not found or inactive.', 404);
        }

        return $this->successResponse($product, 'Product retrieved successfully.');
    }
}
