@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
    <main class="auth-page">
        <section class="auth-panel">
            <h1>Đăng nhập</h1>
            <form class="auth-form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email')
                <p class="auth-error">{{ $message }}</p> @enderror

                <label for="password">Mật khẩu</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password')
                <p class="auth-error">{{ $message }}</p> @enderror

                <label class="auth-remember" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1">
                    Ghi nhớ đăng nhập
                </label>

                <button class="normal" type="submit">Đăng nhập</button>
            </form>
            <p class="auth-switch">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
        </section>
    </main>
@endsection