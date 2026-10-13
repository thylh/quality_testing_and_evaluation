<?php

use App\Http\Controllers\ProductApiController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductApiController::class, 'index'])->name('api.products.index');
Route::get('/products/{product}', [ProductApiController::class, 'show'])
    ->whereNumber('product')
    ->name('api.products.show');