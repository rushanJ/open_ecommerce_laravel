<?php

use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\ProductAttributeValueController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImportExportController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\SeoRedirectController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\WebhookEndpointController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function (): void {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [LoginController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('admin.login.submit');
});

Route::middleware('auth:admin')->group(function (): void {
    Route::post('logout', [LogoutController::class, 'logout'])->name('admin.logout');
});

Route::middleware(['auth:admin', 'admin.permission:dashboard.view'])->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
});

Route::middleware(['auth:admin', 'admin.permission:orders.view'])->group(function (): void {
    Route::get('orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
});

Route::middleware(['auth:admin', 'admin.permission:orders.update'])->group(function (): void {
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status.update');
    Route::post('orders/{order}/notes', [OrderController::class, 'addNote'])->name('admin.orders.notes.store');
});

Route::middleware(['auth:admin', 'admin.permission:orders.refund'])->group(function (): void {
    Route::post('orders/{order}/refunds', [RefundController::class, 'store'])->name('admin.orders.refunds.store');
    Route::patch('refunds/{refund}/approve', [RefundController::class, 'approve'])->name('admin.refunds.approve');
    Route::patch('refunds/{refund}/reject', [RefundController::class, 'reject'])->name('admin.refunds.reject');
});

Route::middleware(['auth:admin', 'admin.permission:settings.view'])->group(function (): void {
    Route::get('settings/store', [SettingController::class, 'store'])->name('admin.settings.store');
    Route::get('settings/general', [SettingController::class, 'general'])->name('admin.settings.general');
    Route::get('settings/payments', [SettingController::class, 'payments'])->name('admin.settings.payments');
    Route::get('settings/mail', [SettingController::class, 'mail'])->name('admin.settings.mail');
    Route::get('settings/seo', [SettingController::class, 'seo'])->name('admin.settings.seo');
});

Route::middleware(['auth:admin', 'admin.permission:settings.update'])->group(function (): void {
    Route::put('settings/store', [SettingController::class, 'updateStore'])->name('admin.settings.store.update');
    Route::put('settings/general', [SettingController::class, 'updateGeneral'])->name('admin.settings.general.update');
    Route::put('settings/payments', [SettingController::class, 'updatePayments'])->name('admin.settings.payments.update');
    Route::put('settings/mail', [SettingController::class, 'updateMail'])->name('admin.settings.mail.update');
    Route::put('settings/seo', [SettingController::class, 'updateSeo'])->name('admin.settings.seo.update');
});

Route::middleware(['auth:admin', 'admin.permission:settings.view'])->group(function (): void {
    Route::get('seo/redirects', [SeoRedirectController::class, 'index'])->name('admin.seo.redirects.index');
    Route::get('seo/redirects/create', [SeoRedirectController::class, 'create'])->name('admin.seo.redirects.create');
    Route::get('seo/redirects/{seo_redirect}/edit', [SeoRedirectController::class, 'edit'])->name('admin.seo.redirects.edit');
});

Route::middleware(['auth:admin', 'admin.permission:settings.create'])->group(function (): void {
    Route::post('seo/redirects', [SeoRedirectController::class, 'store'])->name('admin.seo.redirects.store');
});

Route::middleware(['auth:admin', 'admin.permission:settings.update'])->group(function (): void {
    Route::put('seo/redirects/{seo_redirect}', [SeoRedirectController::class, 'update'])->name('admin.seo.redirects.update');
});

Route::middleware(['auth:admin', 'admin.permission:settings.delete'])->group(function (): void {
    Route::delete('seo/redirects/{seo_redirect}', [SeoRedirectController::class, 'destroy'])->name('admin.seo.redirects.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:settings.view'])->group(function (): void {
    Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('admin.email-templates.index');
    Route::get('email-templates/create', [EmailTemplateController::class, 'create'])->name('admin.email-templates.create');
    Route::get('email-templates/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('admin.email-templates.edit');
});

Route::middleware(['auth:admin', 'admin.permission:settings.update'])->group(function (): void {
    Route::post('email-templates', [EmailTemplateController::class, 'store'])->name('admin.email-templates.store');
    Route::put('email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('admin.email-templates.update');
    Route::delete('email-templates/{emailTemplate}', [EmailTemplateController::class, 'destroy'])->name('admin.email-templates.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:dashboard.view'])->group(function (): void {
    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
    Route::patch('notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.read_all');
    Route::patch('notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('admin.notifications.read');
});

Route::middleware(['auth:admin', 'admin.permission:payments.view'])->group(function (): void {
    Route::get('payments', [PaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('admin.payments.show');
});

Route::middleware(['auth:admin', 'admin.permission:coupons.view'])->group(function (): void {
    Route::get('coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
});

Route::middleware(['auth:admin', 'admin.permission:coupons.create'])->group(function (): void {
    Route::get('coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
});

Route::middleware(['auth:admin', 'admin.permission:coupons.update'])->group(function (): void {
    Route::get('coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('admin.coupons.update');
});

Route::middleware(['auth:admin', 'admin.permission:coupons.delete'])->group(function (): void {
    Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:content.view'])->group(function (): void {
    Route::get('pages', [PageController::class, 'index'])->name('admin.pages.index');
    Route::get('menus', [MenuController::class, 'index'])->name('admin.menus.index');
    Route::get('menus/{menu}', [MenuController::class, 'show'])->name('admin.menus.show');
    Route::get('banners', [BannerController::class, 'index'])->name('admin.banners.index');
    Route::get('media', [MediaController::class, 'index'])->name('admin.media.index');
});

Route::middleware(['auth:admin', 'admin.permission:content.create'])->group(function (): void {
    Route::get('pages/create', [PageController::class, 'create'])->name('admin.pages.create');
    Route::post('pages', [PageController::class, 'store'])->name('admin.pages.store');
    Route::get('menus/create', [MenuController::class, 'create'])->name('admin.menus.create');
    Route::post('menus', [MenuController::class, 'store'])->name('admin.menus.store');
    Route::get('menus/{menu}/items/create', [MenuItemController::class, 'create'])->name('admin.menus.items.create');
    Route::post('menu-items', [MenuItemController::class, 'store'])->name('admin.menus.items.store');
    Route::get('banners/create', [BannerController::class, 'create'])->name('admin.banners.create');
    Route::post('banners', [BannerController::class, 'store'])->name('admin.banners.store');
    Route::get('media/create', [MediaController::class, 'create'])->name('admin.media.create');
    Route::post('media', [MediaController::class, 'store'])->name('admin.media.store');
});

Route::middleware(['auth:admin', 'admin.permission:content.update'])->group(function (): void {
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('admin.pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->name('admin.pages.update');
    Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('admin.menus.edit');
    Route::put('menus/{menu}', [MenuController::class, 'update'])->name('admin.menus.update');
    Route::get('menu-items/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('admin.menu-items.edit');
    Route::put('menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('admin.menu-items.update');
    Route::get('banners/{banner}/edit', [BannerController::class, 'edit'])->name('admin.banners.edit');
    Route::put('banners/{banner}', [BannerController::class, 'update'])->name('admin.banners.update');
    Route::get('media/{media}/edit', [MediaController::class, 'edit'])->name('admin.media.edit');
    Route::put('media/{media}', [MediaController::class, 'update'])->name('admin.media.update');
});

Route::middleware(['auth:admin', 'admin.permission:content.delete'])->group(function (): void {
    Route::delete('pages/{page}', [PageController::class, 'destroy'])->name('admin.pages.destroy');
    Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('admin.menus.destroy');
    Route::delete('menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('admin.menu-items.destroy');
    Route::delete('banners/{banner}', [BannerController::class, 'destroy'])->name('admin.banners.destroy');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('admin.media.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:reviews.view'])->group(function (): void {
    Route::get('reviews', [ProductReviewController::class, 'index'])->name('admin.reviews.index');
    Route::get('reviews/{review}', [ProductReviewController::class, 'show'])->name('admin.reviews.show');
});

Route::middleware(['auth:admin', 'admin.permission:reviews.approve'])->group(function (): void {
    Route::patch('reviews/{review}/status', [ProductReviewController::class, 'updateStatus'])->name('admin.reviews.status.update');
});

Route::middleware(['auth:admin', 'admin.permission:reviews.delete'])->group(function (): void {
    Route::delete('reviews/{review}', [ProductReviewController::class, 'destroy'])->name('admin.reviews.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:brands.view'])->group(function (): void {
    Route::get('brands', [BrandController::class, 'index'])->name('admin.brands.index');
});

Route::middleware(['auth:admin', 'admin.permission:brands.create'])->group(function (): void {
    Route::get('brands/create', [BrandController::class, 'create'])->name('admin.brands.create');
    Route::post('brands', [BrandController::class, 'store'])->name('admin.brands.store');
});

Route::middleware(['auth:admin', 'admin.permission:brands.update'])->group(function (): void {
    Route::get('brands/{brand}/edit', [BrandController::class, 'edit'])->name('admin.brands.edit');
    Route::put('brands/{brand}', [BrandController::class, 'update'])->name('admin.brands.update');
});

Route::middleware(['auth:admin', 'admin.permission:brands.delete'])->group(function (): void {
    Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:categories.view'])->group(function (): void {
    Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories.index');
});

Route::middleware(['auth:admin', 'admin.permission:categories.create'])->group(function (): void {
    Route::get('categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('admin.categories.store');
});

Route::middleware(['auth:admin', 'admin.permission:categories.update'])->group(function (): void {
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
});

Route::middleware(['auth:admin', 'admin.permission:categories.delete'])->group(function (): void {
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:products.view'])->group(function (): void {
    Route::get('products', [ProductController::class, 'index'])->name('admin.products.index');
});

Route::middleware(['auth:admin', 'admin.permission:products.create'])->group(function (): void {
    Route::get('products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('products', [ProductController::class, 'store'])->name('admin.products.store');
});

Route::middleware(['auth:admin', 'admin.permission:products.update'])->group(function (): void {
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('products/{product}/images/{productImage}', [ProductController::class, 'destroyImage'])->name('admin.products.images.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:products.delete'])->group(function (): void {
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:products.export'])->group(function (): void {
    Route::get('products/export', [ProductImportExportController::class, 'export'])->name('admin.products.export');
});

Route::middleware(['auth:admin', 'admin.permission:products.create'])->group(function (): void {
    Route::get('products/import', [ProductImportExportController::class, 'importForm'])->name('admin.products.import');
});

Route::middleware(['auth:admin', 'admin.permission:products.create', 'admin.permission:products.update'])->group(function (): void {
    Route::post('products/import', [ProductImportExportController::class, 'upload'])->name('admin.products.import.upload');
});

Route::middleware(['auth:admin', 'admin.permission:products.update'])->group(function (): void {
    Route::get('import-jobs/{importJob}', [ProductImportExportController::class, 'show'])->name('admin.import-jobs.show');
    Route::post('import-jobs/{importJob}/process', [ProductImportExportController::class, 'process'])->name('admin.import-jobs.process');
});

Route::middleware(['auth:admin', 'admin.permission:inventory.view'])->group(function (): void {
    Route::get('inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('admin.inventory.movements');
    Route::get('warehouses', [WarehouseController::class, 'index'])->name('admin.warehouses.index');
});

Route::middleware(['auth:admin', 'admin.permission:inventory.create'])->group(function (): void {
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('admin.warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->name('admin.warehouses.store');
});

Route::middleware(['auth:admin', 'admin.permission:inventory.update'])->group(function (): void {
    Route::get('inventory/adjust', [InventoryController::class, 'adjustForm'])->name('admin.inventory.adjust.form');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('admin.inventory.adjust');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('admin.warehouses.edit');
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('admin.warehouses.update');
});

Route::middleware(['auth:admin', 'admin.permission:inventory.delete'])->group(function (): void {
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('admin.warehouses.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:attributes.view'])->group(function (): void {
    Route::get('attributes', [ProductAttributeController::class, 'index'])->name('admin.attributes.index');
    Route::get('attributes/{attribute}/values', [ProductAttributeValueController::class, 'index'])->name('admin.attributes.values.index');
});

Route::middleware(['auth:admin', 'admin.permission:attributes.create'])->group(function (): void {
    Route::get('attributes/create', [ProductAttributeController::class, 'create'])->name('admin.attributes.create');
    Route::post('attributes', [ProductAttributeController::class, 'store'])->name('admin.attributes.store');
    Route::get('attributes/{attribute}/values/create', [ProductAttributeValueController::class, 'create'])->name('admin.attributes.values.create');
    Route::post('attributes/{attribute}/values', [ProductAttributeValueController::class, 'store'])->name('admin.attributes.values.store');
});

Route::middleware(['auth:admin', 'admin.permission:attributes.update'])->group(function (): void {
    Route::get('attributes/{attribute}/edit', [ProductAttributeController::class, 'edit'])->name('admin.attributes.edit');
    Route::put('attributes/{attribute}', [ProductAttributeController::class, 'update'])->name('admin.attributes.update');
    Route::get('attributes/{attribute}/values/{value}/edit', [ProductAttributeValueController::class, 'edit'])->name('admin.attributes.values.edit');
    Route::put('attributes/{attribute}/values/{value}', [ProductAttributeValueController::class, 'update'])->name('admin.attributes.values.update');
});

Route::middleware(['auth:admin', 'admin.permission:attributes.delete'])->group(function (): void {
    Route::delete('attributes/{attribute}', [ProductAttributeController::class, 'destroy'])->name('admin.attributes.destroy');
    Route::delete('attributes/{attribute}/values/{value}', [ProductAttributeValueController::class, 'destroy'])->name('admin.attributes.values.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:reports.view'])->group(function (): void {
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('admin.reports.sales');
    Route::get('reports/orders', [ReportController::class, 'orders'])->name('admin.reports.orders');
    Route::get('reports/products', [ReportController::class, 'products'])->name('admin.reports.products');
    Route::get('reports/customers', [ReportController::class, 'customers'])->name('admin.reports.customers');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('admin.reports.stock');
    Route::get('reports/payments', [ReportController::class, 'payments'])->name('admin.reports.payments');
    Route::get('reports/coupons', [ReportController::class, 'coupons'])->name('admin.reports.coupons');
});

Route::middleware(['auth:admin', 'admin.permission:reports.export'])->group(function (): void {
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('admin.reports.export');
});

Route::middleware(['auth:admin', 'admin.permission:taxes.view'])->group(function (): void {
    Route::get('tax/classes', [TaxClassController::class, 'index'])->name('admin.tax.classes.index');
    Route::get('tax/rates', [TaxRateController::class, 'index'])->name('admin.tax.rates.index');
});

Route::middleware(['auth:admin', 'admin.permission:taxes.create'])->group(function (): void {
    Route::get('tax/classes/create', [TaxClassController::class, 'create'])->name('admin.tax.classes.create');
    Route::post('tax/classes', [TaxClassController::class, 'store'])->name('admin.tax.classes.store');
    Route::get('tax/rates/create', [TaxRateController::class, 'create'])->name('admin.tax.rates.create');
    Route::post('tax/rates', [TaxRateController::class, 'store'])->name('admin.tax.rates.store');
});

Route::middleware(['auth:admin', 'admin.permission:taxes.update'])->group(function (): void {
    Route::get('tax/classes/{taxClass}/edit', [TaxClassController::class, 'edit'])->name('admin.tax.classes.edit');
    Route::put('tax/classes/{taxClass}', [TaxClassController::class, 'update'])->name('admin.tax.classes.update');
    Route::get('tax/rates/{taxRate}/edit', [TaxRateController::class, 'edit'])->name('admin.tax.rates.edit');
    Route::put('tax/rates/{taxRate}', [TaxRateController::class, 'update'])->name('admin.tax.rates.update');
});

Route::middleware(['auth:admin', 'admin.permission:taxes.delete'])->group(function (): void {
    Route::delete('tax/classes/{taxClass}', [TaxClassController::class, 'destroy'])->name('admin.tax.classes.destroy');
    Route::delete('tax/rates/{taxRate}', [TaxRateController::class, 'destroy'])->name('admin.tax.rates.destroy');
});

Route::middleware(['auth:admin', 'admin.permission:settings.view'])->group(function (): void {
    Route::get('api-tokens', [ApiTokenController::class, 'index'])->name('admin.api-tokens.index');
    Route::get('api-tokens/create', [ApiTokenController::class, 'create'])->name('admin.api-tokens.create');
    Route::get('api-tokens/show-token', [ApiTokenController::class, 'showToken'])->name('admin.api-tokens.show-token');
    Route::get('webhooks', [WebhookEndpointController::class, 'index'])->name('admin.webhooks.index');
    Route::get('webhooks/create', [WebhookEndpointController::class, 'create'])->name('admin.webhooks.create');
    Route::get('webhooks/{webhookEndpoint}/edit', [WebhookEndpointController::class, 'edit'])->name('admin.webhooks.edit');
    Route::get('webhooks/{webhookEndpoint}/deliveries', [WebhookEndpointController::class, 'deliveries'])->name('admin.webhooks.deliveries');
});

Route::middleware(['auth:admin', 'admin.permission:settings.update'])->group(function (): void {
    Route::post('api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
    Route::patch('api-tokens/{apiToken}/revoke', [ApiTokenController::class, 'revoke'])->name('admin.api-tokens.revoke');
    Route::post('webhooks', [WebhookEndpointController::class, 'store'])->name('admin.webhooks.store');
    Route::put('webhooks/{webhookEndpoint}', [WebhookEndpointController::class, 'update'])->name('admin.webhooks.update');
    Route::delete('webhooks/{webhookEndpoint}', [WebhookEndpointController::class, 'destroy'])->name('admin.webhooks.destroy');
    Route::post('webhook-deliveries/{webhookDelivery}/retry', [WebhookEndpointController::class, 'retry'])->name('admin.webhook-deliveries.retry');
});
