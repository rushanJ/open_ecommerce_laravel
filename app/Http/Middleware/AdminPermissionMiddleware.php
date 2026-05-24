<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin instanceof AdminUser) {
            return redirect()->route('admin.login');
        }

        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        if (! $admin->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
