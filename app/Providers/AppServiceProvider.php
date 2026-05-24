<?php

namespace App\Providers;

use App\Models\AdminUser;
use App\Models\Notification;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Services\CartService;
use App\Services\SettingService;
use App\Services\StoreService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class, fn () => new CartService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        RateLimiter::for('admin-login', function (Request $request): Limit {
            $email = strtolower((string) $request->input('email', ''));

            return Limit::perMinute(5)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('customer-login', function (Request $request): Limit {
            $email = strtolower((string) $request->input('email', ''));

            return Limit::perMinute(5)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('api-public', fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));

        RateLimiter::for('api-auth', function (Request $request): Limit {
            $email = strtolower((string) $request->input('email', ''));

            return Limit::perMinute(10)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('api-cart', function (Request $request): Limit {
            $user = $request->user();
            $by = $user instanceof \App\Models\Customer
                ? 'customer:'.$user->getKey()
                : 'ip:'.$request->ip();

            return Limit::perMinute(60)->by($by);
        });

        require_once app_path('Helpers/media.php');

        Route::bind('value', function (string $value, \Illuminate\Routing\Route $route) {
            $attribute = $route->parameter('attribute');
            if ($attribute instanceof ProductAttribute) {
                return ProductAttributeValue::query()
                    ->where('attribute_id', $attribute->getKey())
                    ->whereKey($value)
                    ->firstOrFail();
            }

            return ProductAttributeValue::query()->whereKey($value)->firstOrFail();
        });

        View::composer('admin.partials.topbar', function ($view): void {
            $admin = auth('admin')->user();
            $adminUnreadNotificationCount = 0;
            if ($admin instanceof AdminUser) {
                $adminUnreadNotificationCount = Notification::query()
                    ->where('recipient_type', 'admin')
                    ->where(function ($q) use ($admin): void {
                        $q->whereNull('recipient_id')
                            ->orWhere('recipient_id', $admin->getKey());
                    })
                    ->whereNull('read_at')
                    ->count();
            }

            $view->with('adminUnreadNotificationCount', $adminUnreadNotificationCount);
        });

        View::composer('customer.*', function ($view): void {
            $count = (int) (app(CartService::class)->getCurrentCart()->items_count ?? 0);

            try {
                $storefrontStore = app(StoreService::class)->currentStore();
            } catch (\Throwable) {
                $storefrontStore = null;
            }

            $storefrontPublicSettings = app(SettingService::class)->publicSettings();

            $storefrontName = $storefrontStore?->name
                ?? ($storefrontPublicSettings['store.name'] ?? null)
                ?? config('open_ecommerce_laravel.store.name', config('app.name'));

            $view->with([
                'storefrontCartItemCount' => $count,
                'storefrontStore' => $storefrontStore,
                'storefrontPublicSettings' => $storefrontPublicSettings,
                'storefrontName' => $storefrontName,
            ]);
        });
    }
}
