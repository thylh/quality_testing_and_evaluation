<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class JMeterCheckoutSeeder extends Seeder
{
    public function run(): void
    {
        $database = DB::connection()->getDatabaseName();
        if (! app()->environment(['local', 'testing']) || ! str_contains(strtolower($database), 'jmeter')) {
            throw new RuntimeException(
                'JMeter checkout fixtures require a local/testing environment and a database name containing "jmeter".'
            );
        }

        $userCount = (int) env('JMETER_USER_COUNT', 100);
        $productId = (int) env('JMETER_PRODUCT_ID', 1);
        if ($userCount < 1 || ! Product::query()->whereKey($productId)->exists()) {
            throw new RuntimeException('Set JMETER_USER_COUNT to a positive number and ensure JMETER_PRODUCT_ID exists.');
        }

        $password = env('JMETER_PASSWORD', 'Jmeter-Checkout-Only-2026!');

        DB::transaction(function () use ($userCount, $password): void {
            $userIds = [];

            for ($number = 1; $number <= $userCount; $number++) {
                $user = User::updateOrCreate(
                    ['email' => "load-test-{$number}@example.test"],
                    [
                        'name' => "JMeter User {$number}",
                        'password' => Hash::make($password),
                    ]
                );

                $userIds[] = $user->id;
            }

            Cart::query()->whereIn('user_id', $userIds)->delete();
        });
    }
}
