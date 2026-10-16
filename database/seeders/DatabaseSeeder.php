<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AddProductsSeeder::class,
        ]);
    }
}

// Cách chạy nhanh:
// php artisan migrate:fresh --seed
// hoặc
// php artisan db:seed --class=AddProductsSeeder