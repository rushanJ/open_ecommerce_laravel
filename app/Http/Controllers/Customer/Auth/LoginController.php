<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\LoginCustomerRequest;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('customer.auth.login');
    }

    public function login(LoginCustomerRequest $request, CartService $cartService): RedirectResponse
    {
        $credentials = $request->validated();

        $customer = Customer::query()
            ->where('email', $credentials['email'])
            ->first();

        if ($customer === null || $customer->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! auth('customer')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'active',
        ])) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        /** @var Customer $authed */
        $authed = auth('customer')->user();
        $authed->forceFill(['last_login_at' => now()])->save();

        $cartService->mergeGuestCartToCustomerCart($authed);

        return redirect()->intended(route('customer.account.dashboard'))
            ->with('success', __('customer.login_success'));
    }
}

