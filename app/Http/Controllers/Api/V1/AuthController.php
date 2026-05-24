<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\Customer;
use App\Services\CartService;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function __construct(
        protected CartService $cartService,
        protected WebhookService $webhooks,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var Customer $customer */
        $customer = Customer::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        $cartToken = $request->header('X-Cart-Token');
        if (is_string($cartToken) && preg_match('/^[a-zA-Z0-9._\-]{8,128}$/', $cartToken)) {
            $this->cartService->usingGuestSessionId($cartToken, fn () => $this->cartService->mergeGuestCartToCustomerCart($customer));
        }

        $customerId = $customer->getKey();
        DB::afterCommit(function () use ($customerId): void {
            $this->webhooks->dispatchSafe('customer.created', ['customer_id' => $customerId]);
        });

        $token = $customer->createToken('api')->plainTextToken;

        return $this->success([
            'customer' => (new CustomerResource($customer))->resolve(),
            'token' => $token,
        ], __('customer.register_success'), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var Customer|null $customer */
        $customer = Customer::query()->where('email', $data['email'])->first();

        if ($customer === null || ! Hash::check($data['password'], (string) $customer->password)) {
            return $this->error(__('auth.failed'), 401);
        }

        if ($customer->status !== 'active') {
            return $this->error(__('auth.failed'), 403);
        }

        $customer->forceFill(['last_login_at' => now()])->save();

        $cartToken = $request->header('X-Cart-Token');
        if (is_string($cartToken) && preg_match('/^[a-zA-Z0-9._\-]{8,128}$/', $cartToken)) {
            $this->cartService->usingGuestSessionId($cartToken, fn () => $this->cartService->mergeGuestCartToCustomerCart($customer));
        }

        $token = $customer->createToken('api')->plainTextToken;

        return $this->success([
            'customer' => (new CustomerResource($customer))->resolve(),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(null, __('customer.logout_success'));
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        return $this->success((new CustomerResource($customer))->resolve());
    }
}
