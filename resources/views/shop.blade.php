@extends('layouts.app')

@section('title', 'Shop')

@section('content')
<section id="page-header">
    <h2>Trang sản phẩm</h2>
    <p>Save more with coupons & up to 70% off!</p>
</section>

<section id="product1" class="section-p1">
    <div class="pro-container">
        {{-- @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif --}}
        @foreach($products as $product)
        <div class="pro">
            <a href="{{ route('sproduct', $product->id) }}">
                    <img src="{{ asset('img/products/' . $product->image) }}" alt="">
                <div class="des">
                    <span>{{ $product->brand }}</span>
                    <h5>{{ $product->name }}</h5>
                    <div class="star">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>${{ $product->price }}</h4>
                </div>
            </a>
            {{-- <a href="#" onclick="event.preventDefault(); document.getElementById('add-to-cart-{{ $product->id }}').submit();">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
            <form id="add-to-cart-{{ $product->id }}" action="{{ route('addtocart', ['id' => $product->id]) }}" method="POST" style="display: none;">
                @csrf
            </form> --}}

            <form id="add-to-cart-{{ $product->id }}" action="{{ route('addtocart', ['id' => $product->id]) }}" method="POST" style="display: none;">
                @csrf
            </form>
            <a href="#" onclick="event.preventDefault(); document.getElementById('add-to-cart-{{ $product->id }}').submit();">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
            
            
            {{-- <form action="{{ route('cart', $product->id) }}" method="GET">
                @csrf
                <button type="submit">
                    <i class="fa-solid fa-cart-shopping"></i>
                </button>
            </form> --}}
            
        </div>
        @endforeach
    </div>
</section>

<!-- pagination  -->
<section id="pagination" class="section-p1">
    <a href="#">1</a>
    <a href="#">2</a>
    <a href="#">3</a>
    <a href="#"><i class="fa fa-long-arrow-alt-right"></i></a>
</section>
@endsection
