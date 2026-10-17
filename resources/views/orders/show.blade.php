@extends('layouts.app')

@section('title', 'Đơn hàng')

@section('content')
    <main class="section-p1">
        <h1>Đặt hàng thành công</h1>
        <p>Mã đơn hàng: <strong>{{ $order->order_number }}</strong></p>
        <p>Trạng thái đơn hàng: {{ $order->status === 'placed' ? 'Đã tiếp nhận' : $order->status }}</p>
        <p>Thanh toán:
            {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Thanh toán mô phỏng' }}
            ({{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }})
        </p>
        <p>Người nhận: {{ $order->customer_name }} · {{ $order->customer_phone }}</p>
        <p>Địa chỉ: {{ $order->shipping_address }}</p>

        <table width="100%">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Size</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->size ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} USD</td>
                        <td>{{ number_format($item->line_total, 2) }} USD</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p><strong>Tổng cộng: {{ number_format($order->total, 2) }} USD</strong></p>
        <a class="normal" href="{{ route('shop') }}">Tiếp tục mua sắm</a>
    </main>
@endsection