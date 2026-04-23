<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new vendor and return an API token.
     */
    public function register(array $data): array
    {
        $vendor = Vendor::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // Hashed via model cast
        ]);

        $token = $vendor->createToken('vendor-api-token')->plainTextToken;

        return [
            'vendor' => $vendor,
            'token' => $token,
        ];
    }

    /**
     * Authenticate a vendor and return an API token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $vendor = Vendor::where('email', $credentials['email'])->first();

        if (! $vendor || ! Hash::check($credentials['password'], $vendor->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $vendor->createToken('vendor-api-token')->plainTextToken;

        return [
            'vendor' => $vendor,
            'token' => $token,
        ];
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Vendor $vendor): void
    {
        $vendor->currentAccessToken()->delete();
    }
}
