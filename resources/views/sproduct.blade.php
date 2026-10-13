@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
  <section id="page-header">
    <h2>Trang sản phẩm</h2>
    <p>Save more with coupons & up to 70% off!</p>
  </section>
  <section id="prodetails" class="section-p1">
    <div class="single-product-image">
      <img src="{{ asset('img/products/' . $product->image) }}" alt="" width="100%" id="mainImg" />
    </div>

    <div class="single-product-details">
      <h6>Home / T-Shirt</h6>
      <h4>Cartoon Astronaut T-Shirts</h4>
      <h2>$78</h2>
      <form action="{{ route('addtocart', $product->id ?? 1) }}" method="POST">
        @csrf
        <select name="size" required>
          <option value="" selected disabled>Select Size</option>
          <option value="XXL">XXL</option>
          <option value="XL">XL</option>
          <option value="Medium">Medium</option>
          <option value="Small">Small</option>
        </select>
        @error('size')
        <p class="auth-error">{{ $message }}</p> @enderror
        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="99" required />
        @error('quantity')
        <p class="auth-error">{{ $message }}</p> @enderror
        <button type="submit">Thêm vào giỏ hàng</button>
      </form>
      <h4>Thông tin sản phẩm</h4>
      <span>Lorem ipsum dolor sit...</span>
    </div>
  </section>
  <section>
    <a href="{{ route('shop') }}" class="back-to-shop"><i class="fa fa-arrow-left"></i> Back to Shop</a>
  </section>
@endsection