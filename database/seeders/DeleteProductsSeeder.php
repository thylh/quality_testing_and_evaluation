<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class DeleteProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Product::truncate(); // Xóa toàn bộ dữ liệu cũ
        // Product::where('id', 1)->delete(); // Xóa 1 sản phẩm chỉ định
        // Product::whereIn('id', [1, 2, 3])->delete(); // Xóa nhiều sản phẩm chỉ định
    }
}
