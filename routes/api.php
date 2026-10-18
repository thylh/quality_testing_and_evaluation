<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\ProductApiController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductApiController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductApiController::class, 'show'])
    ->whereNumber('product')
    ->name('api.products.show');

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register'])->name('api.register');
    Route::post('/login', [AuthApiController::class, 'login'])->name('api.login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');
    Route::get('/cart', [CartApiController::class, 'show'])->name('api.cart.show');
    Route::post('/cart/items', [CartApiController::class, 'store'])->name('api.cart.items.store');
    Route::patch('/cart/items/{item}', [CartApiController::class, 'update'])->whereNumber('item')->name('api.cart.items.update');
    Route::delete('/cart/items/{item}', [CartApiController::class, 'destroy'])->whereNumber('item')->name('api.cart.items.destroy');
    Route::get('/orders', [OrderApiController::class, 'index'])->name('api.orders.index');
    Route::post('/orders', [OrderApiController::class, 'store'])->name('api.orders.store');
    Route::get('/orders/{orderNumber}', [OrderApiController::class, 'show'])->name('api.orders.show');
});