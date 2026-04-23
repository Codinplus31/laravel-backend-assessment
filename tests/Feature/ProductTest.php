<?php

namespace Tests\Feature;

use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = Vendor::factory()->create();
        $this->token = $this->vendor->createToken('test')->plainTextToken;
    }

    /** @test */
    public function vendor_can_create_a_product(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/vendor/products', [
                'name' => 'Test Product',
                'description' => 'A great product.',
                'price' => 99.99,
                'stock_quantity' => 50,
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Product created successfully.']);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'vendor_id' => $this->vendor->id,
        ]);
    }

    /** @test */
    public function vendor_can_list_their_products(): void
    {
        Product::factory()->count(3)->create(['vendor_id' => $this->vendor->id]);

        // Create products for another vendor (should not appear)
        Product::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/vendor/products');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertCount(3, $response->json('data.data'));
    }

    /** @test */
    public function vendor_can_view_their_own_product(): void
    {
        $product = Product::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/vendor/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function vendor_cannot_view_another_vendors_product(): void
    {
        $otherProduct = Product::factory()->create(); // belongs to a different vendor

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/vendor/products/{$otherProduct->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function vendor_can_update_their_product(): void
    {
        $product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Old Name',
            'stock_quantity' => 10,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/vendor/products/{$product->id}", [
                'name' => 'New Name',
                'stock_quantity' => 25,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
            'stock_quantity' => 25,
        ]);
    }

    /** @test */
    public function vendor_cannot_set_negative_stock(): void
    {
        $product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/vendor/products/{$product->id}", [
                'stock_quantity' => -5,
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function vendor_can_delete_their_product(): void
    {
        $product = Product::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/vendor/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function vendor_cannot_delete_another_vendors_product(): void
    {
        $otherProduct = Product::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/vendor/products/{$otherProduct->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $otherProduct->id]);
    }

    /** @test */
    public function product_creation_validates_required_fields(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/vendor/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock_quantity']);
    }
}
