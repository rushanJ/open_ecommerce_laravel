<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddToCartRequest;
use App\Http\Requests\Customer\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(): View
    {
        $cart = $this->cartService->getCurrentCart();
        $cart->load(['items.product.primaryImage', 'items.variant']);

        return view('customer.cart.index', [
            'cart' => $cart,
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $variant = $request->filled('variant_id')
            ? ProductVariant::query()->findOrFail($request->integer('variant_id'))
            : null;

        $this->cartService->addItem($product, $variant, (float) $request->input('quantity'));

        return redirect()
            ->back()
            ->with('success', __('customer.item_added_to_cart'));
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->cartService->getCurrentCart();

        if ((int) $cartItem->cart_id !== (int) $cart->id) {
            return redirect()
                ->route('customer.cart.index')
                ->withErrors(['cart' => __('customer.invalid_cart_item')]);
        }

        $this->cartService->updateItem($cartItem, (float) $request->input('quantity'));

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('customer.cart_item_updated'));
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $cart = $this->cartService->getCurrentCart();

        if ((int) $cartItem->cart_id !== (int) $cart->id) {
            return redirect()
                ->route('customer.cart.index')
                ->withErrors(['cart' => __('customer.invalid_cart_item')]);
        }

        $this->cartService->removeItem($cartItem);

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('customer.cart_item_removed'));
    }

    public function clear(): RedirectResponse
    {
        $this->cartService->clearCart($this->cartService->getCurrentCart());

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('customer.cart_cleared'));
    }

    public function applyCoupon(Request $request, CouponService $coupons): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
        ]);

        $cart = $this->cartService->getCurrentCart();
        $customer = auth('customer')->user();

        try {
            $coupons->applyCouponToCart($cart, $data['code'], $customer);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('customer.cart.index')
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('customer.coupon_applied'));
    }

    public function removeCoupon(CouponService $coupons): RedirectResponse
    {
        $cart = $this->cartService->getCurrentCart();
        $coupons->removeCouponFromCart($cart, $this->cartService);

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('customer.coupon_removed'));
    }
}
