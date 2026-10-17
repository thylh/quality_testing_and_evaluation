<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $cart = Cart::where('user_id', Auth::id())
            ->with('items.product')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Giỏ hàng đang trống.');
        }

        return view('checkout', [
            'cartItems' => $cart->items,
            'totalPrice' => $cart->items->sum(fn ($item) => $item->price * $item->quantity),
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(['cod', 'simulated'])],
            'simulated_result' => [
                'exclude_unless:payment_method,simulated',
                'required',
                Rule::in(['success', 'failure']),
            ],
        ]);

        if ($validated['payment_method'] === 'simulated' && $validated['simulated_result'] === 'failure') {
            return back()
                ->withInput()
                ->with('error', 'Thanh toán mô phỏng thất bại. Giỏ hàng vẫn được giữ nguyên.');
        }

        $order = DB::transaction(function () use ($validated): Order {
            $cart = Cart::where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                throw ValidationException::withMessages(['cart' => 'Không tìm thấy giỏ hàng.']);
            }

            $items = $cart->items()
                ->with('product')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
            }

            $totalCents = 0;
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => (string) Str::uuid(),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cod' ? 'unpaid' : 'paid',
                'status' => 'placed',
                'total' => 0,
            ]);

            foreach ($items as $item) {
                $unitPriceCents = (int) round((float) $item->price * 100);
                $lineTotalCents = $unitPriceCents * $item->quantity;
                $totalCents += $lineTotalCents;

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
                    'line_total' => number_format($lineTotalCents / 100, 2, '.', ''),
                ]);
            }

            $order->total = number_format($totalCents / 100, 2, '.', '');
            $order->save();
            $cart->items()->delete();

            return $order;
        });

        return redirect()
            ->route('checkout.orders.show', $order->order_number)
            ->with('success', 'Đơn hàng đã được tạo.');
    }

    public function showOrder(string $orderNumber): View
    {
        $order = Order::where('user_id', Auth::id())
            ->where('order_number', $orderNumber)
            ->with('items')
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }
}