<?php

use App\Http\Controllers\AdminApi\V1\CustomerController;
use App\Http\Controllers\AdminApi\V1\HealthController;
use App\Http\Controllers\AdminApi\V1\OrderController;
use App\Http\Controllers\AdminApi\V1\PaymentController;
use App\Http\Controllers\AdminApi\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin.api')->group(function (): void {
    Route::get('health', HealthController::class)->name('health');
});

Route::middleware('admin.api:products.read')->group(function (): void {
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
});

Route::middleware('admin.api:orders.read')->group(function (): void {
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware('admin.api:customers.read')->group(function (): void {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
});

Route::middleware('admin.api:payments.read')->group(function (): void {
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
});
