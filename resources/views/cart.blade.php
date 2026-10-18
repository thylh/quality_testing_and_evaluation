@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <section id="cart" class="section-p1">
        @if(count($cartItems) > 0)
            <table width="100%">
                <thead>
                    <tr>
                        <th>Xóa</th>
                        <th>Ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                        <tr>
                            <td>
                                {{-- Nút xóa sản phẩm --}}
                                <form action="{{ route('remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cart-remove-button" aria-label="Remove {{ $item->product->name }} from cart">
                                        <i class="fa fa-times-circle cart-remove-icon" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                            <td><img src="{{ asset('img/products/' . $item->product->image) }}" alt="{{ $item->product->name }}" />
                            </td>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ number_format($item->price) }} USD</td>
                            <td>
                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="cart-update-form">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99"
                                        class="cart-quantity-input" />
                                    <button type="submit" class="normal">Cập nhật</button>
                                </form>
                            </td>
                            <td>{{ number_format($item->price * $item->quantity) }} USD</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Giỏ hàng trống!</p>
        @endif
    </section>

    <section id="cart-add" class="section-p1">
        <div id="coupon">
            <h3>Mã giảm giá</h3>
            <div>
                <input type="text" placeholder="Nhập mã giảm giá" />
                <button class="normal">Áp dụng</button>
            </div>
        </div>
        <div id="subtotal">
            <h3>Tổng đơn hàng</h3>
            <table>
                <tr>
                    <td>Tổng tiền:</td>
                    <td>{{ number_format($totalPrice) }} USD</td>
                </tr>
                <tr>
                    <td>Vận chuyển:</td>
                    <td>Miễn phí</td>
                </tr>
                <tr>
                    <td><strong>Tiền thanh toán:</strong></td>
                    <td><strong>{{ number_format($totalPrice) }} USD</strong></td>
                </tr>
            </table>
            @if(count($cartItems) > 0)
                <a class="normal" href="{{ route('checkout') }}">Tiến hành thanh toán</a>
            @endif
        </div>
    </section>
    <section>
        <a href="{{ route('shop') }}" class="back-to-shop"><i class="fa fa-arrow-left"></i> Back to Shop</a>
    </section>
@endsection