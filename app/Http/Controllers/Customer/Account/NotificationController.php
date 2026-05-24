<?php

namespace App\Http\Controllers\Customer\Account;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $notifications = Notification::query()
            ->where('recipient_type', 'customer')
            ->where('recipient_id', $customer->getKey())
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('customer.account.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        if ($notification->recipient_type !== 'customer'
            || (int) $notification->recipient_id !== (int) $customer->getKey()) {
            abort(403);
        }

        $notification->markAsRead();

        return back();
    }
}
