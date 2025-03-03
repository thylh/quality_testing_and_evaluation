<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class AddProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f1.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f2.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f3.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f4.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f5.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f6.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f7.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'f8.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n1.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n2.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n3.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n4.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n5.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n6.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n7.jpg',
                'price' => 78,
            ],
            [
                'name' => 'Cartoon Astronaut T-Shirts',
                'brand' => 'adidas',
                'image' => 'n8.jpg',
                'price' => 78,
            ],
        ];

        foreach ($products as $product) { // Duyệt mảng và thêm từng sp 1 
            Product::create($product);
        }

    }
    // public function run()
    // {
    //   Thêm 1 sp  
    //     Product::create([
    //         'name' => 'Sản phẩm mới',
    //         'brand' => 'New Brand',
    //         'image' => 'new-product.jpg',
    //         'price' => 99.99
    //     ]);

    //  Thêm nhiều sp nhanh
    //     Product::insert([
    //     [
    //          'name' => 'Áo Thun Nam Nike',
    //          'brand' => 'Nike',
    //          'image' => 'nike-tshirt.jpg',
    //          'price' => 299.99,
    //     ],
    //     [
    //          'name' => 'Giày Adidas Superstar',
    //          'brand' => 'Adidas',
    //          'image' => 'adidas-superstar.jpg',
    //          'price' => 999.99,
    //     ]
    //     ]);
    // }
}