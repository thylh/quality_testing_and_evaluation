<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $request->user()->cart()->with('items.product')->first();

        return response()->json([
            'data' => $cart?->items->map(fn (CartItem $item): array => $this->formatItem($item))->values() ?? [],
            'meta' => [
                'total' => number_format($cart?->items->sum(fn (CartItem $item): float => (float) $item->price * $item->quantity) ?? 0, 2, '.', ''),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:99'],
            'size' => ['nullable', Rule::in(['XXL', 'XL', 'Medium', 'Small'])],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $cart = $request->user()->cart()->firstOrCreate([]);
        $quantity = $validated['quantity'] ?? 1;
        $size = $validated['size'] ?? null;
        $itemQuery = $cart->items()->where('product_id', $product->id);
        $size === null ? $itemQuery->whereNull('size') : $itemQuery->where('size', $size);
        $item = $itemQuery->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->save();
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'size' => $size,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return response()->json(['data' => $this->formatItem($item->load('product'))], 201);
    }

    public function update(Request $request, int $item): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cartItem = $request->user()->cart?->items()->findOrFail($item);
        abort_unless($cartItem, 404);
        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json(['data' => $this->formatItem($cartItem->load('product'))]);
    }

    public function destroy(Request $request, int $item): JsonResponse
    {
        $cartItem = $request->user()->cart?->items()->findOrFail($item);
        abort_unless($cartItem, 404);
        $cartItem->delete();

        return response()->json(['message' => 'Đã xóa sản phẩm khỏi giỏ hàng.']);
    }

    private function formatItem(CartItem $item): array
    {
        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product->name,
            'size' => $item->size,
            'quantity' => $item->quantity,
            'unit_price' => $item->price,
            'line_total' => number_format((float) $item->price * $item->quantity, 2, '.', ''),
        ];
    }
}
