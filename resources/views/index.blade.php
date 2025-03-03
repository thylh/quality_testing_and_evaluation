@extends('layouts.app')

@section('title', 'Cara')

@section('content')
<section id="hero">
    <h4>Trade-in-offer</h4>
    <h2>Super value deals</h2>
    <h1>On all products</h1>
    <p>Save more with coupons & up to 70% off!</p>
    <button>Shop Now</button>
</section>

<!---- feature section -------->
<section id="feature" class="section-p1">
    <div class="fe-box">
        <img src="{{ asset('img/features/f1.png') }}" alt="">
        <h6>Free Shipping</h6>
    </div>
    <div class="fe-box">
        <img src="{{ asset('img/features/f2.png') }}" alt="">
        <h6>Online Order</h6>
    </div>
    <div class="fe-box">
        <img src="{{ asset('img/features/f3.png') }}" alt="">
        <h6>Save Money</h6>
    </div>
    <div class="fe-box">
        <img src="{{ asset('img/features/f4.png') }}" alt="">
        <h6>PromotionsPromotions</h6>
    </div>
    <div class="fe-box">
        <img src="{{ asset('img/features/f5.png') }}" alt="">
        <h6>Happy Sell</h6>
    </div>
    <div class="fe-box">
        <img src="{{ asset('img/features/f6.png') }}" alt="">
        <h6>F24/7 Supper</h6>
    </div>
</section>

<!---- feature product section -------->
<section id="product1" class="section-p1">
    <h2>Featured Products</h2>
    <p>Summer Collection New Mordern Design</p>
    <div class="pro-container">
        {{-- <a href="{{ route('sproduct', $product->id) }}">
            <div class="pro">
                <img src="./img/products/f1.jpg" alt="">
                <div class="des">
                    <span>adidas</span>
                    <h5>Cartoon Astronaut T-Shirts</h5>
                    <div class="star">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>$78.00</h4>
                </div>
            </div>
        </a> --}}
        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 1]) }}">
                <img src="{{ asset('img/products/f1.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 1]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>
    
        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 2]) }}">
                <img src="{{ asset('img/products/f2.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 2]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>

        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 6]) }}">
                <img src="{{ asset('img/products/f6.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 6]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>

        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 8]) }}">
                <img src="{{ asset('img/products/f8.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 8]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>
    </div>
</section>

<!---- arrivals section start  -->
<section id="banner" class="section-m1">
    <h4>Repair Services</h4>
    <h2>Up to <span> 70% off</span> - All t-Shirts & Accessories</h2>
    <button class="normal">Explore More</button>
</section>

 <!---- feature product section -------->
 <section id="product1" class="section-p1">
    <h2>New Arrivals</h2>
    <p>Summer Collection New Mordern Design</p>
    <div class="pro-container">
        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 12]) }}">
                <img src="{{ asset('img/products/n4.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 12]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>

        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 13]) }}">
                <img src="{{ asset('img/products/n5.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 13]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>

        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 14]) }}">
                <img src="{{ asset('img/products/n6.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 14]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>

        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 15]) }}">
                <img src="{{ asset('img/products/n7.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 15]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>

        <div class="pro">
            <a href="{{ route('sproduct', ['id' => 16]) }}">
                <img src="{{ asset('img/products/n8.jpg') }}" alt="">
            </a>
            <div class="des">
                <span>adidas</span>
                <h5>
                    <a href="{{ route('sproduct', ['id' => 16]) }}">Cartoon Astronaut T-Shirts</a>
                </h5>
                <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <h4>$78.00</h4>
            </div>
        </div>
    </div>
</section>

<!---- sm banner section start  -->

<section id="sm-banner" class="section-p1">
    <div class="banner-box">
        <h4>crazy deals</h4>
        <h2>buy 1 get 1 free</h2>
        <span>The best claassis dress is on sale at cara</span>
        <button class="white">Leran More</button>
    </div>
    <div class="banner-box banner-box2">
        <h4>spring/summer</h4>
        <h2>upcomming season</h2>
        <span>The best claassis dress is on sale at cara</span>
        <button class="white">Leran More</button>
    </div>

</section>

<section id="banner3">
    <div class="banner-box">
        <h2>SEASONAL SALE</h2>
        <h3>Winter Collection - 50% OFF</h3>
    </div>
    <div class="banner-box banner-box2">
        <h2>SEASONAL SALE</h2>
        <h3>Winter Collection - 50% OFF</h3>
    </div>
    <div class="banner-box banner-box3">
        <h2>SEASONAL SALE</h2>
        <h3>Winter Collection - 50% OFF</h3>
    </div>
</section>
@endsection
