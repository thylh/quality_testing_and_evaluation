<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class UpdateProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update 1 sản phẩm
        // Product::where('id', 1)->update([
        //     'name' => 'Áo Thun Nam Adidas',
        //     'price' => 399.99
        // ]);

        // update nhiều sp
        // Product::whereIn('id', [1, 2, 3])->update([
        //     'price' => 499.99
        // ]);
    }
}
