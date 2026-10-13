<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return view('index', compact('products'));
    }

    public function shop()
    {
        $products = Product::all();

        return view('shop', compact('products'));
    }

    public function sproduct($id)
    {
        $product = Product::findOrFail($id);
        $relatedProducts = Product::where('id', '!=', $id)->limit(4)->get();

        return view('sproduct', compact('product', 'relatedProducts'));
    }

    public function addToCart(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:99'],
            'size' => ['nullable', Rule::in(['XXL', 'XL', 'Medium', 'Small'])],
        ]);

        $product = Product::findOrFail($id);
        $quantity = $validated['quantity'] ?? 1;
        $size = $validated['size'] ?? null;

        $cart = $this->getOrCreateCurrentCart();

        // Kiểm tra nếu sản phẩm đã có trong giỏ thì tăng số lượng
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where(function ($query) use ($size) {
                if ($size === null) {
                    $query->whereNull('size');
                } else {
                    $query->where('size', $size);
                }
            })
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            // Thêm sản phẩm mới vào giỏ hàng
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'size' => $size,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return redirect()->back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
    }

    public function cart()
    {
        $cart = $this->currentCart()?->load('items.product');

        if (! $cart) {
            return view('cart', [
                'cartItems' => [],
                'totalPrice' => 0,
            ]);
        }

        // Tính tổng tiền
        $totalPrice = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return view('cart', [
            'cartItems' => $cart->items,
            'totalPrice' => $totalPrice,
        ]);
    }

    public function remove($id)
    {
        $cartItem = $this->currentCart()?->items()->find($id);
        abort_unless($cartItem, 404);

        $cartItem->delete();

        return redirect()->route('cart')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }

    private function currentCart(): ?Cart
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->first();
        }

        $sessionId = session('cart_session_id');
        if (! $sessionId) {
            return null;
        }

        return Cart::whereNull('user_id')->where('session_id', $sessionId)->first();
    }

    private function getOrCreateCurrentCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['session_id' => null]
            );
        }

        if (! session()->has('cart_session_id')) {
            session()->put('cart_session_id', (string) Str::uuid());
        }

        return Cart::firstOrCreate(
            ['session_id' => session('cart_session_id')],
            ['user_id' => null]
        );
    }
}
