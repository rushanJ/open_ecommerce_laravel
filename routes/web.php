<?php

use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/payments/payhere/notify', [PaymentWebhookController::class, 'payhereNotify'])
    ->name('payments.payhere.notify');

require __DIR__.'/customer.php';
