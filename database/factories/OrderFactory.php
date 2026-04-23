<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);

        return [
            'product_id' => Product::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'quantity' => $quantity,
            'total_price' => fake()->randomFloat(2, 10, 1000),
            'status' => 'completed',
        ];
    }
}
