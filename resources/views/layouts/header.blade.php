<header class="header">
    <a href="{{ route('index') }}"><img src="{{ asset('img/logo.png') }}" class="logo" alt="Logo"></a>

    <div>
        <ul id="navbar">
            <li><a class="{{ request()->routeIs('index') ? 'active' : '' }}" href="{{ route('index') }}">Home</a></li>
            <li><a class="{{ request()->routeIs('shop') ? 'active' : '' }}" href="{{ route('shop') }}">Shop</a></li>
            <li><a class="{{ request()->routeIs('test-history.*') ? 'active' : '' }}" href="{{ route('test-history.index') }}">Kết quả kiểm thử</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
            @auth
                <li><span class="header-account">{{ Auth::user()->name }}</span></li>
                <li>
                    <form class="header-logout" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Đăng xuất</button>
                    </form>
                </li>
            @else
                <li><a class="{{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Đăng nhập</a>
                </li>
                <li><a class="{{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Đăng
                        ký</a></li>
            @endauth
            <li id="lg-bag"><a href="{{ route('cart') }}"><i class="fas fa-shopping-bag"></i></a></li>
            <a href="#" id="close"><i class="fas fa-times"></i></a>
        </ul>
        <div id="mobile">
            <a href="{{ route('cart') }}"><i class="fas fa-shopping-bag"></i></a>
            <i id="bar" class="fas fa-outdent"></i>
        </div>
    </div>
</header>