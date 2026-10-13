<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_endpoint_returns_a_json_list(): void
    {
        Product::create([
            'name' => 'API Product',
            'brand' => 'Test Brand',
            'image' => 'api-product.jpg',
            'price' => 125.50,
        ]);

        $this->getJson(route('api.products.index'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'brand',
                    'image',
                    'price',
                ]],
            ])
            ->assertJsonPath('data.0.name', 'API Product');
    }

    public function test_product_endpoint_returns_a_json_product(): void
    {
        $product = Product::create([
            'name' => 'Single API Product',
            'brand' => 'Test Brand',
            'image' => 'single-product.jpg',
            'price' => 99.99,
        ]);

        $this->getJson(route('api.products.show', $product))
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Single API Product');
    }

    public function test_product_endpoint_returns_not_found_for_an_unknown_id(): void
    {
        $this->getJson(route('api.products.show', 999999))
            ->assertNotFound();
    }
}
