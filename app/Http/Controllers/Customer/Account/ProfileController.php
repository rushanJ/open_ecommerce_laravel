<?php

namespace App\Http\Controllers\Customer\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('customer.account.profile.edit', [
            'customer' => auth('customer')->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $customer = auth('customer')->user();
        $customer?->fill($request->validated());
        $customer?->save();

        return redirect()
            ->route('customer.account.profile.edit')
            ->with('success', __('customer.profile_updated'));
    }
}

