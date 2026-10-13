@extends('layouts.app')

@section('title', 'Đăng ký')

@section('content')
    <main class="auth-page">
        <section class="auth-panel">
            <h1>Tạo tài khoản</h1>
            <form class="auth-form" method="POST" action="{{ route('register.store') }}">
                @csrf
                <label for="name">Họ tên</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required>
                @error('name')
                <p class="auth-error">{{ $message }}</p> @enderror

                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email')
                <p class="auth-error">{{ $message }}</p> @enderror

                <label for="password">Mật khẩu</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password')
                <p class="auth-error">{{ $message }}</p> @enderror

                <label for="password_confirmation">Nhập lại mật khẩu</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                    required>

                <button class="normal" type="submit">Đăng ký</button>
            </form>
            <p class="auth-switch">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
        </section>
    </main>
@endsection