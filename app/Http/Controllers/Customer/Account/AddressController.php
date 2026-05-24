<?php

namespace App\Http\Controllers\Customer\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerAddressRequest;
use App\Http\Requests\Customer\UpdateCustomerAddressRequest;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $customer = auth('customer')->user();

        $addresses = $customer?->addresses()
            ->latest('id')
            ->get() ?? collect();

        return view('customer.account.addresses.index', [
            'customer' => $customer,
            'addresses' => $addresses,
        ]);
    }

    public function create(): View
    {
        return view('customer.account.addresses.create', [
            'customer' => auth('customer')->user(),
        ]);
    }

    public function store(StoreCustomerAddressRequest $request): RedirectResponse
    {
        $customer = auth('customer')->user();
        $data = $request->validated();

        DB::transaction(function () use ($customer, $data) {
            if (! empty($data['is_default'])) {
                $customer?->addresses()
                    ->where('type', $data['type'])
                    ->update(['is_default' => false]);
            }

            $customer?->addresses()->create($data);
        });

        return redirect()
            ->route('customer.account.addresses.index')
            ->with('success', __('customer.address_created'));
    }

    public function edit(CustomerAddress $address): View
    {
        $this->assertOwnership($address);

        return view('customer.account.addresses.edit', [
            'customer' => auth('customer')->user(),
            'address' => $address,
        ]);
    }

    public function update(UpdateCustomerAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        $this->assertOwnership($address);

        $customer = auth('customer')->user();
        $data = $request->validated();

        DB::transaction(function () use ($address, $customer, $data) {
            if (! empty($data['is_default'])) {
                $customer?->addresses()
                    ->where('type', $data['type'])
                    ->whereKeyNot($address->getKey())
                    ->update(['is_default' => false]);
            }

            $address->fill($data);
            $address->save();
        });

        return redirect()
            ->route('customer.account.addresses.index')
            ->with('success', __('customer.address_updated'));
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->assertOwnership($address);

        $address->delete();

        return redirect()
            ->route('customer.account.addresses.index')
            ->with('success', __('customer.address_deleted'));
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        $this->assertOwnership($address);

        $customer = auth('customer')->user();

        DB::transaction(function () use ($address, $customer) {
            $customer?->addresses()
                ->where('type', $address->type)
                ->update(['is_default' => false]);

            $address->forceFill(['is_default' => true])->save();
        });

        return redirect()
            ->route('customer.account.addresses.index')
            ->with('success', __('customer.default_address_updated'));
    }

    private function assertOwnership(CustomerAddress $address): void
    {
        $customerId = (int) auth('customer')->id();

        abort_unless((int) $address->customer_id === $customerId, 404);
    }
}

