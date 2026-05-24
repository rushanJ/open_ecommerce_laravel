<?php

use App\Http\Controllers\Customer\BrandController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CategoryController;
use App\Http\Controllers\Customer\GuestOrderLookupController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\PageController;
use App\Http\Controllers\Customer\RobotsController;
use App\Http\Controllers\Customer\SitemapController;
use App\Http\Controllers\Customer\ProductReviewController;
use App\Http\Controllers\Customer\Auth\LoginController as CustomerLoginController;
use App\Http\Controllers\Customer\Auth\LogoutController as CustomerLogoutController;
use App\Http\Controllers\Customer\Auth\RegisterController as CustomerRegisterController;
use App\Http\Controllers\Customer\Account\AddressController as CustomerAccountAddressController;
use App\Http\Controllers\Customer\Account\DashboardController as CustomerAccountDashboardController;
use App\Http\Controllers\Customer\Account\NotificationController as CustomerAccountNotificationController;
use App\Http\Controllers\Customer\Account\OrderController as CustomerAccountOrderController;
use App\Http\Controllers\Customer\Account\ProfileController as CustomerAccountProfileController;
use App\Http\Controllers\Customer\PayHerePaymentController;
use App\Http\Controllers\Customer\ProductController as StorefrontProductController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('customer.sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('customer.robots');

Route::middleware(['seo.redirect', 'store.maintenance', 'customer'])->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('customer.home');

    Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('customer.pages.show');

    Route::get('/shop', [StorefrontProductController::class, 'index'])->name('customer.products.index');
    Route::get('/products/{product:slug}', [StorefrontProductController::class, 'show'])->name('customer.products.show');
    Route::post('/products/{product:slug}/reviews', [ProductReviewController::class, 'store'])
        ->middleware('auth:customer')
        ->name('customer.products.reviews.store');

    Route::get('/categories', [CategoryController::class, 'index'])->name('customer.categories.index');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('customer.categories.show');

    Route::get('/brands', [BrandController::class, 'index'])->name('customer.brands.index');
    Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('customer.brands.show');

    Route::get('/cart', [CartController::class, 'index'])->name('customer.cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('customer.cart.items.store');
    Route::put('/cart/items/{cartItem}', [CartController::class, 'update'])->name('customer.cart.items.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('customer.cart.items.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('customer.cart.clear');
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('customer.cart.coupon.apply');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('customer.cart.coupon.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('customer.checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('customer.checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('customer.checkout.success');

    Route::get('/orders/{order}/pay/payhere', [PayHerePaymentController::class, 'start'])->name('customer.payments.payhere.start');
    Route::get('/payments/payhere/return/{payment}', [PayHerePaymentController::class, 'return'])->name('customer.payments.payhere.return');
    Route::get('/payments/payhere/cancel/{payment}', [PayHerePaymentController::class, 'cancel'])->name('customer.payments.payhere.cancel');

    Route::get('/register', [CustomerRegisterController::class, 'show'])->name('customer.register');
    Route::post('/register', [CustomerRegisterController::class, 'store'])->name('customer.register.store');

    Route::get('/login', [CustomerLoginController::class, 'show'])->name('customer.login');
    Route::post('/login', [CustomerLoginController::class, 'login'])
        ->middleware('throttle:customer-login')
        ->name('customer.login.submit');
    Route::post('/logout', [CustomerLogoutController::class, 'logout'])->name('customer.logout');

    Route::get('/order-lookup', [GuestOrderLookupController::class, 'showForm'])->name('customer.order.lookup');
    Route::post('/order-lookup', [GuestOrderLookupController::class, 'lookup'])->name('customer.order.lookup.submit');

    Route::middleware('auth:customer')->group(function (): void {
        Route::get('/account', [CustomerAccountDashboardController::class, 'index'])->name('customer.account.dashboard');

        Route::get('/account/profile', [CustomerAccountProfileController::class, 'edit'])->name('customer.account.profile.edit');
        Route::put('/account/profile', [CustomerAccountProfileController::class, 'update'])->name('customer.account.profile.update');

        Route::get('/account/addresses', [CustomerAccountAddressController::class, 'index'])->name('customer.account.addresses.index');
        Route::get('/account/addresses/create', [CustomerAccountAddressController::class, 'create'])->name('customer.account.addresses.create');
        Route::post('/account/addresses', [CustomerAccountAddressController::class, 'store'])->name('customer.account.addresses.store');
        Route::get('/account/addresses/{address}/edit', [CustomerAccountAddressController::class, 'edit'])->name('customer.account.addresses.edit');
        Route::put('/account/addresses/{address}', [CustomerAccountAddressController::class, 'update'])->name('customer.account.addresses.update');
        Route::delete('/account/addresses/{address}', [CustomerAccountAddressController::class, 'destroy'])->name('customer.account.addresses.destroy');
        Route::patch('/account/addresses/{address}/default', [CustomerAccountAddressController::class, 'setDefault'])->name('customer.account.addresses.default');

        Route::get('/account/orders', [CustomerAccountOrderController::class, 'index'])->name('customer.account.orders.index');
        Route::get('/account/orders/{order}', [CustomerAccountOrderController::class, 'show'])->name('customer.account.orders.show');

        Route::get('/account/notifications', [CustomerAccountNotificationController::class, 'index'])->name('customer.account.notifications.index');
        Route::patch('/account/notifications/{notification}/read', [CustomerAccountNotificationController::class, 'markRead'])->name('customer.account.notifications.read');
    });
});
