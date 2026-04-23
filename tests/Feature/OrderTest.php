<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_place_an_order_for_active_product(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->active()->create([
            'vendor_id' => $vendor->id,
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 3,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order placed successfully.',
            ]);

        // Stock should be reduced
        $this->assertEquals(7, $product->fresh()->stock_quantity);

        // Order record should exist
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'customer_email' => 'john@example.com',
            'quantity' => 3,
            'total_price' => 300.00,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function order_is_rejected_when_stock_is_insufficient(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->active()->create([
            'vendor_id' => $vendor->id,
            'stock_quantity' => 2,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        // Stock should remain unchanged
        $this->assertEquals(2, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function order_is_rejected_for_inactive_product(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->inactive()->create([
            'vendor_id' => $vendor->id,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function order_cannot_be_placed_with_zero_quantity(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->active()->create([
            'vendor_id' => $vendor->id,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function order_validates_required_fields(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'customer_name', 'customer_email', 'quantity']);
    }

    /** @test */
    public function guest_can_view_an_order(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->active()->create([
            'vendor_id' => $vendor->id,
            'stock_quantity' => 10,
            'price' => 50.00,
        ]);

        // Place order first
        $orderResponse = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'quantity' => 2,
        ]);

        $orderId = $orderResponse->json('data.id');

        $response = $this->getJson("/api/orders/{$orderId}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function stock_is_correctly_decremented_on_multiple_orders(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->active()->create([
            'vendor_id' => $vendor->id,
            'stock_quantity' => 10,
            'price' => 25.00,
        ]);

        // First order
        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'Buyer 1',
            'customer_email' => 'buyer1@example.com',
            'quantity' => 3,
        ])->assertStatus(201);

        $this->assertEquals(7, $product->fresh()->stock_quantity);

        // Second order
        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'Buyer 2',
            'customer_email' => 'buyer2@example.com',
            'quantity' => 5,
        ])->assertStatus(201);

        $this->assertEquals(2, $product->fresh()->stock_quantity);

        // Third order should fail (only 2 left, trying to order 3)
        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'customer_name' => 'Buyer 3',
            'customer_email' => 'buyer3@example.com',
            'quantity' => 3,
        ])->assertStatus(422);

        // Stock should still be 2
        $this->assertEquals(2, $product->fresh()->stock_quantity);
    }
}
