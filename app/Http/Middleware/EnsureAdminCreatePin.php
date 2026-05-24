<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminCreatePin
{
    private const FIELD = 'admin_create_pin';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldRequirePin($request)) {
            return $next($request);
        }

        $expectedPin = config('open_ecommerce_laravel.admin.create_pin');

        if (! filled($expectedPin)) {
            return $next($request);
        }

        $providedPin = (string) $request->input(self::FIELD, '');

        if (! hash_equals((string) $expectedPin, $providedPin)) {
            return $this->reject($request, __('admin.create_pin_invalid'));
        }

        $request->request->remove(self::FIELD);

        return $next($request);
    }

    private function shouldRequirePin(Request $request): bool
    {
        if (! $request->is('admin/*')) {
            return false;
        }

        if (! $request->isMethod('post')) {
            return false;
        }

        return ! $request->routeIs('admin.login.submit', 'admin.logout');
    }

    private function reject(Request $request, string $message): RedirectResponse
    {
        return redirect()
            ->back()
            ->withErrors([self::FIELD => $message])
            ->withInput($request->except(self::FIELD));
    }
}
