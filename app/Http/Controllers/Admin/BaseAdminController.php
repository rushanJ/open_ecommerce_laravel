<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\AdminActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base controller for admin HTTP layer.
 *
 * Admin Blade view convention (per module):
 * - resources/views/admin/{module}/index.blade.php
 * - resources/views/admin/{module}/create.blade.php
 * - resources/views/admin/{module}/edit.blade.php
 * - resources/views/admin/{module}/show.blade.php
 * - resources/views/admin/{module}/partials/form.blade.php
 *
 * Shared UI partials: resources/views/admin/shared/*
 *
 * @see docs/admin-crud-pattern.md
 */
abstract class BaseAdminController extends Controller
{
    /**
     * Redirect with a success flash message. Uses back() when no route name is given.
     */
    protected function success(string $message, ?string $routeName = null): RedirectResponse
    {
        if ($routeName !== null) {
            return redirect()->route($routeName)->with('success', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Redirect with an error flash message. Uses back() when no route name is given.
     */
    protected function error(string $message, ?string $routeName = null): RedirectResponse
    {
        if ($routeName !== null) {
            return redirect()->route($routeName)->with('error', $message);
        }

        return redirect()->back()->with('error', $message)->withInput();
    }

    protected function backWithSuccess(string $message): RedirectResponse
    {
        return redirect()->back()->with('success', $message);
    }

    protected function backWithError(string $message): RedirectResponse
    {
        return redirect()->back()->with('error', $message)->withInput();
    }

    /**
     * Fine-grained gate inside a controller action when middleware alone is not enough.
     */
    protected function abortUnlessCan(string $permission, int $status = Response::HTTP_FORBIDDEN): void
    {
        $admin = auth('admin')->user();

        abort_unless(
            $admin instanceof AdminUser && $admin->hasPermission($permission),
            $status
        );
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    protected function logAdminActivity(string $module, string $action, ?Model $subject = null, array $old = [], array $new = []): void
    {
        app(AdminActivityLogger::class)->log($module, $action, $subject, $old, $new);
    }
}
