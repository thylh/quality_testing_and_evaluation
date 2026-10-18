<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()->with('items')->latest()->get();

        return response()->json(['data' => $orders->map(fn (Order $order): array => $this->formatOrder($order))]);
    }

    public function store(Request $request): JsonResponse
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
            throw ValidationException::withMessages([
                'payment' => ['Thanh toán mô phỏng thất bại. Giỏ hàng vẫn được giữ nguyên.'],
            ]);
        }

        $order = DB::transaction(function () use ($request, $validated): Order {
            $cart = Cart::where('user_id', $request->user()->id)->lockForUpdate()->first();
            if (! $cart) {
                throw ValidationException::withMessages(['cart' => ['Không tìm thấy giỏ hàng.']]);
            }

            $items = $cart->items()->with('product')->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => ['Giỏ hàng đang trống.']]);
            }

            $totalCents = 0;
            $order = $request->user()->orders()->create([
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

            $order->update(['total' => number_format($totalCents / 100, 2, '.', '')]);
            $cart->items()->delete();

            return $order->load('items');
        });

        return response()->json(['data' => $this->formatOrder($order)], 201);
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = $request->user()->orders()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json(['data' => $this->formatOrder($order)]);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'total' => $order->total,
            'items' => $order->items->map(fn ($item): array => [
                'product_name' => $item->product_name,
                'size' => $item->size,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->values(),
        ];
    }
}
