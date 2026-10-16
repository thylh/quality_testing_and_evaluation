<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class AddProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::query()->delete();

        $products = [
            [
                'name' => 'Cartoon Astronaut T-Shirt',
                'brand' => 'adidas',
                'image' => 'f1.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Floral Printed Shirt',
                'brand' => 'H&M',
                'image' => 'f2.jpg',
                'price' => 82,
            ],
            [
                'name' => 'Classic Denim Jacket',
                'brand' => 'Levi\'s',
                'image' => 'f3.jpg',
                'price' => 120,
            ],
            [
                'name' => 'Urban Street Hoodie',
                'brand' => 'Nike',
                'image' => 'f4.jpg',
                'price' => 95,
            ],
            [
                'name' => 'Summer Polo Shirt',
                'brand' => 'Puma',
                'image' => 'f5.jpg',
                'price' => 88,
            ],
            [
                'name' => 'Modern Cotton Tee',
                'brand' => 'Uniqlo',
                'image' => 'f6.jpg',
                'price' => 74,
            ],
            [
                'name' => 'Premium Oversize Jacket',
                'brand' => 'adidas',
                'image' => 'f7.jpg',
                'price' => 150,
            ],
            [
                'name' => 'Lightweight Sport Tee',
                'brand' => 'Nike',
                'image' => 'f8.jpg',
                'price' => 68,
            ],
            [
                'name' => 'Elegant Casual Shirt',
                'brand' => 'Zara',
                'image' => 'n1.jpg',
                'price' => 90,
            ],
            [
                'name' => 'Minimalist Long Sleeve',
                'brand' => 'H&M',
                'image' => 'n2.jpg',
                'price' => 76,
            ],
            [
                'name' => 'Travel Cotton Hoodie',
                'brand' => 'Puma',
                'image' => 'n3.jpg',
                'price' => 110,
            ],
            [
                'name' => 'Essential Crew Neck',
                'brand' => 'Uniqlo',
                'image' => 'n4.jpg',
                'price' => 70,
            ],
            [
                'name' => 'Weekend Fashion Tee',
                'brand' => 'Zara',
                'image' => 'n5.jpg',
                'price' => 85,
            ],
            [
                'name' => 'City Runner Jacket',
                'brand' => 'Nike',
                'image' => 'n6.jpg',
                'price' => 130,
            ],
            [
                'name' => 'Everyday Casual Polo',
                'brand' => 'Levi\'s',
                'image' => 'n7.jpg',
                'price' => 98,
            ],
            [
                'name' => 'Street Style Oversized Tee',
                'brand' => 'adidas',
                'image' => 'n8.jpg',
                'price' => 79,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                [
                    'name' => $product['name'],
                    'brand' => $product['brand'],
                    'image' => $product['image'],
                ],
                [
                    'price' => $product['price'],
                ]
            );
        }
    }
}