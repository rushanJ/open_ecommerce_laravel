<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var AdminUser $admin */
        $admin = $request->user('admin');

        $notifications = Notification::query()
            ->where('recipient_type', 'admin')
            ->where(function ($q) use ($admin): void {
                $q->whereNull('recipient_id')
                    ->orWhere('recipient_id', $admin->getKey());
            })
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        /** @var AdminUser $admin */
        $admin = $request->user('admin');

        if ($notification->recipient_type !== 'admin') {
            abort(404);
        }

        if ($notification->recipient_id !== null && (int) $notification->recipient_id !== (int) $admin->getKey()) {
            abort(403);
        }

        $notification->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        /** @var AdminUser $admin */
        $admin = $request->user('admin');

        Notification::query()
            ->where('recipient_type', 'admin')
            ->where(function ($q) use ($admin): void {
                $q->whereNull('recipient_id')
                    ->orWhere('recipient_id', $admin->getKey());
            })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', __('admin.notifications_marked_read'));
    }
}
