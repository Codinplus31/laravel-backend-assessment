<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Services\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Place a new order (atomic operation with stock check).
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->placeOrder($request->validated());

            return $this->successResponse($order, 'Order placed successfully.', 201);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * View an order by ID.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->findOrder($id);

        if (! $order) {
            return $this->errorResponse('Order not found.', 404);
        }

        return $this->successResponse($order, 'Order retrieved successfully.');
    }
}
