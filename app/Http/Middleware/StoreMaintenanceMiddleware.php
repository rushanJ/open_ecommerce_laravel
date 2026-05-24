<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use App\Services\StoreService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreMaintenanceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        try {
            $settings = app(SettingService::class);
            $flag = (bool) $settings->get('general.maintenance_mode', false)
                || (bool) $settings->get('store.maintenance_mode', false);

            $store = app(StoreService::class)->currentStore();
            $flag = $flag || $store->status === 'maintenance';
        } catch (\Throwable) {
            return $next($request);
        }

        if (! $flag) {
            return $next($request);
        }

        return response()
            ->view('customer.maintenance', [], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
