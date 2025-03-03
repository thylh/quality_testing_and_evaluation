<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function index() {
        $products = Product::all();
        return view('index', compact('products'));
    }

    public function shop() {
        $products = Product::all();
        return view('shop', compact('products'));
    }

    public function sproduct($id) {
        $product = Product::findOrFail($id);
        $relatedProducts = Product::where('id', '!=', $id)->limit(4)->get();
        return view('sproduct', compact('product', 'relatedProducts'));
    }

    public function addToCart(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại!');
        }

        // Lấy hoặc tạo session_id
        if (!session()->has('cart_session_id')) {
            session()->put('cart_session_id', (string) Str::uuid());
        }
        $session_id = session()->get('cart_session_id');

        // Lấy hoặc tạo giỏ hàng
        $cart = Cart::firstOrCreate(['session_id' => $session_id]);

        // Kiểm tra nếu sản phẩm đã có trong giỏ thì tăng số lượng
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $product->id)
                            ->where(function($query) use ($request) {
                                $query->where('size', $request->size)
                                    ->orWhereNull('size');
                            })
                            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity ?? 1;
            $cartItem->save();
        } else {
            // Thêm sản phẩm mới vào giỏ hàng
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'size' => $request->size ?? null,
                'quantity' => $request->quantity ?? 1,
                'price' => $product->price,
            ]);
        }

        return redirect()->back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
    }

    public function cart()
    {
        $session_id = session()->get('cart_session_id');
        $cart = Cart::where('session_id', $session_id)->with('items.product')->first();

        if (!$cart) {
            return view('cart', [
                'cartItems' => [],
                'totalPrice' => 0
            ]);
        }

        // Tính tổng tiền
        $totalPrice = $cart->items->sum(function($item) {
            return $item->price * $item->quantity;
        });

        return view('cart', [
            'cartItems' => $cart->items,
            'totalPrice' => $totalPrice
        ]);
    }

    public function remove($id)
    {
        // Tìm và xóa sản phẩm khỏi giỏ hàng
        $cartItem = CartItem::find($id);
        if ($cartItem) {
            $cartItem->delete();
        }
        return redirect()->route('cart')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }

}
