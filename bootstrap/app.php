<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware('api')
                ->prefix('api/v1')
                ->name('api.v1.')
                ->group(base_path('routes/api_v1.php'));

            Route::middleware('api')
                ->prefix('api/admin/v1')
                ->name('api.admin.v1.')
                ->group(base_path('routes/admin_api_v1.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\EnsureAdminCreatePin::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'payments/payhere/notify',
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin', 'admin/*') && ! $request->routeIs('admin.login', 'admin.login.submit')) {
                return route('admin.login');
            }

            if ($request->is('account', 'account/*')) {
                return route('customer.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->routeIs('admin.login', 'admin.login.submit') || $request->is('admin/login')) {
                if (Auth::guard('admin')->check()) {
                    return route('admin.dashboard');
                }
            }

            return route('dashboard');
        });

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'admin.api' => \App\Http\Middleware\AdminApiTokenMiddleware::class,
            'admin.permission' => \App\Http\Middleware\AdminPermissionMiddleware::class,
            'customer' => \App\Http\Middleware\EnsureCustomer::class,
            'store.maintenance' => \App\Http\Middleware\StoreMaintenanceMiddleware::class,
            'seo.redirect' => \App\Http\Middleware\SeoRedirectMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The uploaded files are larger than the server allows.',
                    'errors' => [
                        'upload' => ['Increase PHP post_max_size/upload_max_filesize or upload smaller files.'],
                    ],
                ], 413);
            }

            if ($request->is('admin/*')) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'upload' => 'The uploaded files are larger than the server allows. Increase PHP post_max_size/upload_max_filesize or upload smaller files.',
                    ]);
            }

            return null;
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/v1*') || $request->is('api/admin/v1*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'errors' => (object) [],
                ], 401);
            }

            return null;
        });

        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/v1*') || $request->is('api/admin/v1*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('validation.failed'),
                    'errors' => $e->errors(),
                ], $e->status);
            }

            return null;
        });
    })->create();
