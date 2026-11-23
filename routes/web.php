<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/sales/create');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::resource('products', ProductController::class)->except('show');
    Route::patch('/products/{product}/restore', [ProductController::class, 'restore'])
        ->withTrashed()->name('products.restore');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store']);
});
