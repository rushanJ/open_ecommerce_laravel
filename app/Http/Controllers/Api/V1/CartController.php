<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AddCartItemRequest;
use App\Http\Requests\Api\V1\ApplyCouponRequest;
use App\Http\Requests\Api\V1\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends ApiController
{
    public function __construct(
        protected CartService $cartService,
        protected CouponService $couponService
    ) {}

    public function show(Request $request): JsonResponse
    {
        if ($this->cartService->authenticatedCustomer() !== null) {
            $cart = $this->cartService->getCurrentCart()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            return $this->success((new CartResource($cart))->resolve());
        }

        $token = $this->resolveGuestCartToken($request);

        return $this->cartService->usingGuestSessionId($token, function () use ($token) {
            $cart = $this->cartService->getCurrentCart()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            return $this->success((new CartResource($cart))->resolve(), null, 200, ['cart_token' => $token]);
        });
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $data = $request->validated();

        $product = Product::query()->findOrFail($data['product_id']);
        $variant = ! empty($data['variant_id'])
            ? ProductVariant::query()->findOrFail((int) $data['variant_id'])
            : null;

        return $this->withGuestCartContext($request, function (?string $token) use ($product, $variant, $data) {
            try {
                $this->cartService->addItem($product, $variant, (float) $data['quantity']);
            } catch (ValidationException $e) {
                return $this->error(
                    (string) $e->validator->errors()->first(),
                    422,
                    $e->errors()
                );
            }

            $cart = $this->cartService->getCurrentCart()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            $meta = $token !== null ? ['cart_token' => $token] : [];

            return $this->success((new CartResource($cart))->resolve(), null, 200, $meta);
        });
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        return $this->withGuestCartContext($request, function () use ($request, $cartItem) {
            if (! $this->cartItemBelongsToCurrentCart($cartItem)) {
                return $this->error(__('customer.invalid_cart_item'), 403);
            }

            try {
                $this->cartService->updateItem($cartItem, (float) $request->validated('quantity'));
            } catch (ValidationException $e) {
                return $this->error(
                    (string) $e->validator->errors()->first(),
                    422,
                    $e->errors()
                );
            }

            $cart = $this->cartService->getCurrentCart()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            return $this->success((new CartResource($cart))->resolve());
        });
    }

    public function removeItem(Request $request, CartItem $cartItem): JsonResponse
    {
        return $this->withGuestCartContext($request, function () use ($cartItem) {
            if (! $this->cartItemBelongsToCurrentCart($cartItem)) {
                return $this->error(__('customer.invalid_cart_item'), 403);
            }

            $this->cartService->removeItem($cartItem);

            $cart = $this->cartService->getCurrentCart()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            return $this->success((new CartResource($cart))->resolve());
        });
    }

    public function clear(Request $request): JsonResponse
    {
        return $this->withGuestCartContext($request, function () {
            $this->cartService->clearCart($this->cartService->getCurrentCart());

            $cart = $this->cartService->getCurrentCart()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            return $this->success((new CartResource($cart))->resolve());
        });
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $code = $request->validated('code');

        return $this->withGuestCartContext($request, function () use ($code) {
            $cart = $this->cartService->getCurrentCart();
            $customer = $this->cartService->authenticatedCustomer();

            try {
                $this->couponService->applyCouponToCart($cart, $code, $customer);
            } catch (ValidationException $e) {
                return $this->error(
                    (string) $e->validator->errors()->first(),
                    422,
                    $e->errors()
                );
            }

            $cart = $this->cartService->getCurrentCart()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            return $this->success((new CartResource($cart))->resolve(), __('customer.coupon_applied'));
        });
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        return $this->withGuestCartContext($request, function () {
            $cart = $this->cartService->getCurrentCart();
            $this->couponService->removeCouponFromCart($cart, $this->cartService);

            $cart = $cart->fresh()->load(['items.product.brand', 'items.product.categories', 'items.product.primaryImage', 'items.variant']);

            return $this->success((new CartResource($cart))->resolve(), __('customer.coupon_removed'));
        });
    }

    /**
     * @param  callable(?string): JsonResponse  $callback
     */
    private function withGuestCartContext(Request $request, callable $callback): JsonResponse
    {
        if ($this->cartService->authenticatedCustomer() !== null) {
            return $callback(null);
        }

        $token = $this->resolveGuestCartToken($request);

        return $this->cartService->usingGuestSessionId($token, fn () => $callback($token));
    }

    private function resolveGuestCartToken(Request $request): string
    {
        $header = $request->header('X-Cart-Token');
        if (is_string($header) && preg_match('/^[a-zA-Z0-9._\-]{8,128}$/', $header)) {
            return $header;
        }

        return (string) Str::uuid();
    }

    private function cartItemBelongsToCurrentCart(CartItem $cartItem): bool
    {
        $cart = $this->cartService->getCurrentCart();

        return (int) $cartItem->cart_id === (int) $cart->getKey();
    }
}
