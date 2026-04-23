<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new vendor.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse([
            'vendor' => [
                'id' => $result['vendor']->id,
                'name' => $result['vendor']->name,
                'email' => $result['vendor']->email,
            ],
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Vendor registered successfully.', 201);
    }

    /**
     * Login a vendor.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->successResponse([
            'vendor' => [
                'id' => $result['vendor']->id,
                'name' => $result['vendor']->name,
                'email' => $result['vendor']->email,
            ],
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    /**
     * Logout current vendor (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Logged out successfully.');
    }
}
