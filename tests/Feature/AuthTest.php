<?php

namespace Tests\Feature;

use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function vendor_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['vendor' => ['id', 'name', 'email'], 'token', 'token_type'],
            ])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('vendors', ['email' => 'vendor@test.com']);
    }

    /** @test */
    public function vendor_cannot_register_with_duplicate_email(): void
    {
        Vendor::factory()->create(['email' => 'existing@test.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Another Vendor',
            'email' => 'existing@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function vendor_cannot_register_with_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function vendor_can_login_with_valid_credentials(): void
    {
        Vendor::factory()->create([
            'email' => 'vendor@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'vendor@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['vendor', 'token', 'token_type'],
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function vendor_cannot_login_with_wrong_password(): void
    {
        Vendor::factory()->create([
            'email' => 'vendor@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'vendor@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function vendor_can_logout(): void
    {
        $vendor = Vendor::factory()->create();
        $token = $vendor->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Logged out successfully.']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/vendor/products');

        $response->assertStatus(401);
    }
}
