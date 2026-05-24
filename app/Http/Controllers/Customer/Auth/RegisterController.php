<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\RegisterCustomerRequest;
use App\Models\Customer;
use App\Services\CartService;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('customer.auth.register');
    }

    public function store(RegisterCustomerRequest $request, CartService $cartService): RedirectResponse
    {
        $data = $request->validated();

        /** @var Customer $customer */
        $customer = Customer::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'accepts_marketing' => (bool) ($data['accepts_marketing'] ?? false),
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        auth('customer')->login($customer);
        $request->session()->regenerate();

        $cartService->mergeGuestCartToCustomerCart($customer);

        $customerId = $customer->getKey();
        DB::afterCommit(function () use ($customerId): void {
            app(WebhookService::class)->dispatchSafe('customer.created', ['customer_id' => $customerId]);
        });

        return redirect()->route('customer.account.dashboard')
            ->with('success', __('customer.register_success'));
    }
}

