<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/mycontroller', [StudentController::class, 'show'] );
Route::get('/new/{id}', [NewsController::class, 'show'] );

use App\Http\Controllers\ShopController;

Route::get('/home', [ShopController::class, 'index'])->name('index');
Route::get('/shop', [ShopController::class, 'shop'])->name('shop');
Route::get('/product/{id}', [ShopController::class, 'sproduct'])->name('sproduct');
Route::post('/addtocart/{id}', [ShopController::class, 'addtocart'])->name('addtocart');
Route::get('/cart', [ShopController::class, 'cart'])->name('cart');
Route::delete('/cart/remove/{id}', [ShopController::class, 'remove'])->name('remove');
