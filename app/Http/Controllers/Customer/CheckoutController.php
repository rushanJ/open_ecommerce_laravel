<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->getCurrentCart();
        $cart->load(['items.product.primaryImage', 'items.variant']);

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('customer.cart.index')
                ->withErrors(['cart' => __('customer.cart_empty')]);
        }

        $data = $this->checkoutService->getCheckoutData($request, $cart);

        $defaults = [];
        if (Auth::guard('customer')->check()) {
            $c = Auth::guard('customer')->user();
            if ($c !== null) {
                $defaults = [
                    'customer_email' => $c->email ?? '',
                    'customer_phone' => $c->phone ?? '',
                    'billing_first_name' => $c->first_name ?? '',
                    'billing_last_name' => $c->last_name ?? '',
                ];
            }
        }

        return view('customer.checkout.index', [
            'cart' => $data['cart'],
            'shippingRates' => $data['shipping_rates'],
            'previewAddress' => $data['preview_address'],
            'defaults' => $defaults,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $cart = $this->cartService->getCurrentCart();

        if ($cart->items()->count() === 0) {
            return redirect()
                ->route('customer.cart.index')
                ->withErrors(['cart' => __('customer.cart_empty')]);
        }

        $order = $this->checkoutService->createDraftOrderFromCart($cart, $request->validated());

        $request->session()->flash('checkout_order_id', $order->id);
        $guestOrders = (array) $request->session()->get('guest_order_ids', []);
        $guestOrders[] = (int) $order->id;
        $request->session()->put('guest_order_ids', array_values(array_unique(array_map('intval', $guestOrders))));

        return redirect()
            ->route('customer.checkout.success', $order)
            ->with('success', __('customer.order_created'));
    }

    public function success(Request $request, Order $order): View
    {
        $flashedId = $request->session()->pull('checkout_order_id');
        $ok = (int) $flashedId === (int) $order->id;

        if (! $ok && Auth::guard('customer')->check()
            && $order->customer_id !== null
            && (int) $order->customer_id === (int) Auth::guard('customer')->id()) {
            $ok = true;
        }

        if (! $ok) {
            abort(403);
        }

        return view('customer.checkout.success', [
            'order' => $order->load(['items']),
        ]);
    }
}
