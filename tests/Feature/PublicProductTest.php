<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_can_view_active_products(): void
    {
        $vendor = Vendor::factory()->create();
        Product::factory()->count(3)->active()->create(['vendor_id' => $vendor->id]);
        Product::factory()->count(2)->inactive()->create(['vendor_id' => $vendor->id]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Only active products should be returned
        $this->assertCount(3, $response->json('data.data'));
    }

    /** @test */
    public function guests_can_view_a_single_active_product(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->active()->create(['vendor_id' => $vendor->id]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function guests_cannot_view_an_inactive_product(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->inactive()->create(['vendor_id' => $vendor->id]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function guests_can_search_products_by_name(): void
    {
        $vendor = Vendor::factory()->create();
        Product::factory()->active()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Ankara Dress',
        ]);
        Product::factory()->active()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Silk Gown',
        ]);

        $response = $this->getJson('/api/products?search=Ankara');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Ankara Dress', $response->json('data.data.0.name'));
    }

    /** @test */
    public function product_listing_is_paginated(): void
    {
        $vendor = Vendor::factory()->create();
        Product::factory()->count(20)->active()->create(['vendor_id' => $vendor->id]);

        $response = $this->getJson('/api/products?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(20, $response->json('data.total'));
    }
}
