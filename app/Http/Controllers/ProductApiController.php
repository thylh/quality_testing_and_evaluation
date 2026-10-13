<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->orderBy('id')
            ->get(['id', 'name', 'brand', 'image', 'price']);

        return response()->json(['data' => $products]);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->only(['id', 'name', 'brand', 'image', 'price']),
        ]);
    }
}
