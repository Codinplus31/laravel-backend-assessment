<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Vendor 1
        $vendor1 = Vendor::create([
            'name' => 'Fashion Hub Nigeria',
            'email' => 'fashionhub@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create Vendor 2
        $vendor2 = Vendor::create([
            'name' => 'Luxe Accessories',
            'email' => 'luxeaccessories@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Products for Vendor 1
        $vendor1Products = [
            [
                'name' => 'Ankara Print Maxi Dress',
                'description' => 'Beautiful handcrafted Ankara print maxi dress. Perfect for any occasion.',
                'price' => 15000.00,
                'stock_quantity' => 25,
                'status' => 'active',
            ],
            [
                'name' => 'Silk Evening Gown',
                'description' => 'Elegant silk evening gown with intricate beadwork.',
                'price' => 45000.00,
                'stock_quantity' => 10,
                'status' => 'active',
            ],
            [
                'name' => 'Casual Linen Shirt',
                'description' => 'Lightweight linen shirt ideal for everyday wear.',
                'price' => 8500.00,
                'stock_quantity' => 50,
                'status' => 'active',
            ],
            [
                'name' => 'Denim Jacket (Vintage)',
                'description' => 'Vintage-styled denim jacket with custom patches.',
                'price' => 12000.00,
                'stock_quantity' => 15,
                'status' => 'active',
            ],
            [
                'name' => 'Hand-Dyed Adire Skirt',
                'description' => 'Traditional hand-dyed Adire fabric skirt.',
                'price' => 9500.00,
                'stock_quantity' => 0,
                'status' => 'inactive',
            ],
            [
                'name' => 'Wool Blend Blazer',
                'description' => 'Premium wool blend blazer for formal occasions.',
                'price' => 28000.00,
                'stock_quantity' => 8,
                'status' => 'active',
            ],
            [
                'name' => 'Cotton Palazzo Pants',
                'description' => 'Wide-leg cotton palazzo pants in multiple colors.',
                'price' => 7500.00,
                'stock_quantity' => 35,
                'status' => 'active',
            ],
        ];

        // Products for Vendor 2
        $vendor2Products = [
            [
                'name' => 'Gold Plated Necklace Set',
                'description' => 'Elegant gold-plated necklace with matching earrings.',
                'price' => 22000.00,
                'stock_quantity' => 20,
                'status' => 'active',
            ],
            [
                'name' => 'Leather Crossbody Bag',
                'description' => 'Genuine leather crossbody bag with adjustable strap.',
                'price' => 18500.00,
                'stock_quantity' => 12,
                'status' => 'active',
            ],
            [
                'name' => 'Beaded Statement Bracelet',
                'description' => 'Handmade beaded bracelet with semi-precious stones.',
                'price' => 5500.00,
                'stock_quantity' => 40,
                'status' => 'active',
            ],
            [
                'name' => 'Silk Head Wrap',
                'description' => 'Luxurious silk head wrap in vibrant African prints.',
                'price' => 4000.00,
                'stock_quantity' => 60,
                'status' => 'active',
            ],
            [
                'name' => 'Crystal Drop Earrings',
                'description' => 'Swarovski crystal drop earrings for special events.',
                'price' => 12000.00,
                'stock_quantity' => 0,
                'status' => 'inactive',
            ],
            [
                'name' => 'Woven Straw Tote',
                'description' => 'Artisan woven straw tote bag — eco-friendly and stylish.',
                'price' => 9000.00,
                'stock_quantity' => 18,
                'status' => 'active',
            ],
        ];

        foreach ($vendor1Products as $product) {
            $vendor1->products()->create($product);
        }

        foreach ($vendor2Products as $product) {
            $vendor2->products()->create($product);
        }

        $this->command->info('Seeded 2 vendors with 13 products total.');
    }
}
