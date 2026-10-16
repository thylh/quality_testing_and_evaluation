<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/mycontroller', [StudentController::class, 'show'] );
Route::get('/new/{id}', [NewsController::class, 'show'] );

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

use App\Http\Controllers\ShopController;

Route::get('/home', [ShopController::class, 'index'])->name('index');
Route::get('/shop', [ShopController::class, 'shop'])->name('shop');
Route::get('/product/{id}', [ShopController::class, 'sproduct'])->name('sproduct');
Route::post('/addtocart/{id}', [ShopController::class, 'addtocart'])->name('addtocart');
Route::get('/cart', [ShopController::class, 'cart'])->name('cart');
Route::put('/cart/update/{id}', [ShopController::class, 'updateQuantity'])->name('cart.update');
Route::delete('/cart/remove/{id}', [ShopController::class, 'remove'])->name('remove');
