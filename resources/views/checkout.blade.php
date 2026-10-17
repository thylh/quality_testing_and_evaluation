@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <main class="section-p1">
        <h1>Thanh toán</h1>

        @if(session('error'))
            <p class="auth-error" role="alert">{{ session('error') }}</p>
        @endif
        @error('cart')
            <p class="auth-error" role="alert">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf
            <section>
                <h2>Thông tin nhận hàng</h2>
                <p>
                    <label for="customer_name">Họ tên</label><br>
                    <input id="customer_name" name="customer_name" type="text" maxlength="255"
                        value="{{ old('customer_name', $user->name) }}" autocomplete="name" required>
                    @error('customer_name') <span class="auth-error">{{ $message }}</span> @enderror
                </p>
                <p>
                    <label for="customer_email">Email</label><br>
                    <input id="customer_email" name="customer_email" type="email" maxlength="255"
                        value="{{ old('customer_email', $user->email) }}" autocomplete="email" required>
                    @error('customer_email') <span class="auth-error">{{ $message }}</span> @enderror
                </p>
                <p>
                    <label for="customer_phone">Số điện thoại</label><br>
                    <input id="customer_phone" name="customer_phone" type="tel" maxlength="30"
                        value="{{ old('customer_phone') }}" autocomplete="tel" required>
                    @error('customer_phone') <span class="auth-error">{{ $message }}</span> @enderror
                </p>
                <p>
                    <label for="shipping_address">Địa chỉ nhận hàng</label><br>
                    <textarea id="shipping_address" name="shipping_address" maxlength="1000"
                        required>{{ old('shipping_address') }}</textarea>
                    @error('shipping_address') <span class="auth-error">{{ $message }}</span> @enderror
                </p>
            </section>

            <section>
                <h2>Phương thức thanh toán</h2>
                <p>
                    <label>
                        <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod')>
                        Thanh toán khi nhận hàng (COD)
                    </label>
                </p>
                <p>
                    <label>
                        <input type="radio" name="payment_method" value="simulated"
                            @checked(old('payment_method') === 'simulated')>
                        Thanh toán mô phỏng
                    </label>
                </p>
                <p>
                    <label for="simulated_result">Kết quả thanh toán mô phỏng</label><br>
                    <select id="simulated_result" name="simulated_result">
                        <option value="success" @selected(old('simulated_result', 'success') === 'success')>Thành công
                        </option>
                        <option value="failure" @selected(old('simulated_result') === 'failure')>Thất bại</option>
                    </select>
                    <small>Chỉ dùng để kiểm thử; không kết nối cổng thanh toán và không nhập thông tin thẻ.</small>
                    @error('simulated_result') <span class="auth-error">{{ $message }}</span> @enderror
                </p>
            </section>

            <section>
                <h2>Đơn hàng</h2>
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Size</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cartItems as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->size ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price * $item->quantity, 2) }} USD</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p><strong>Tổng thanh toán: {{ number_format($totalPrice, 2) }} USD</strong></p>
                <button class="normal" type="submit">Đặt hàng</button>
            </section>
        </form>
    </main>
@endsection