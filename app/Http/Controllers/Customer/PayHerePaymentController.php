<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\Payments\PayHereService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayHerePaymentController extends Controller
{
    public function __construct(
        protected PayHereService $payHereService
    ) {}

    public function start(Request $request, Order $order): View|RedirectResponse
    {
        if (! $this->payHereService->isEnabled()) {
            return redirect()
                ->route('customer.products.index')
                ->withErrors(['payment' => __('customer.payment_gateway_disabled')]);
        }

        if (! in_array($order->payment_status, ['unpaid', 'pending'], true)) {
            return redirect()
                ->route('customer.products.index')
                ->withErrors(['payment' => __('customer.payment_not_available')]);
        }

        if (Auth::guard('customer')->check()) {
            if ($order->customer_id === null || (int) $order->customer_id !== (int) Auth::guard('customer')->id()) {
                abort(403);
            }
        } else {
            $guestOrders = (array) $request->session()->get('guest_order_ids', []);
            if (! in_array((int) $order->id, array_map('intval', $guestOrders), true)) {
                abort(403);
            }
        }

        $payment = $this->payHereService->createPaymentForOrder($order);
        $payload = $this->payHereService->buildCheckoutPayload($order->fresh(['billingAddress']), $payment);

        return view('customer.payments.payhere.redirect', [
            'checkoutUrl' => $this->payHereService->getCheckoutUrl(),
            'payload' => $payload,
            'order' => $order,
            'payment' => $payment,
        ]);
    }

    public function return(Request $request, Payment $payment): View
    {
        PaymentTransaction::query()->create([
            'payment_id' => $payment->getKey(),
            'order_id' => $payment->order_id,
            'provider' => 'payhere',
            'transaction_type' => 'return',
            'provider_reference' => $payment->payment_reference,
            'status' => 'returned',
            'request_payload' => $request->all(),
            'response_payload' => null,
            'signature_valid' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return view('customer.payments.payhere.return', [
            'payment' => $payment->fresh(['order']),
        ]);
    }

    public function cancel(Request $request, Payment $payment): View
    {
        PaymentTransaction::query()->create([
            'payment_id' => $payment->getKey(),
            'order_id' => $payment->order_id,
            'provider' => 'payhere',
            'transaction_type' => 'cancel',
            'provider_reference' => $payment->payment_reference,
            'status' => 'cancelled',
            'request_payload' => $request->all(),
            'response_payload' => null,
            'signature_valid' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return view('customer.payments.payhere.cancel', [
            'payment' => $payment->fresh(['order']),
        ]);
    }
}

