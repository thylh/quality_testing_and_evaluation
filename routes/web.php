<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TestHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/mycontroller', [StudentController::class, 'show']);
Route::get('/new/{id}', [NewsController::class, 'show']);
Route::get('/test-history', [TestHistoryController::class, 'index'])->name('test-history.index');
Route::get('/test-history/{run}', [TestHistoryController::class, 'show'])
    ->whereIn('run', ['phpunit', 'selenium', 'postman', 'jmeter-readonly', 'jmeter-checkout', 'zap'])
    ->name('test-history.show');
Route::get('/test-history/artifacts/{artifact}', [TestHistoryController::class, 'artifact'])
    ->whereIn('artifact', ['jmeter-readonly', 'jmeter-checkout', 'zap-html'])
    ->name('test-history.artifact');

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

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders/{orderNumber}', [CheckoutController::class, 'showOrder'])->name('checkout.orders.show');
});
