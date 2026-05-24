<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\GuestOrderLookupRequest;
use App\Services\GuestOrderLookupService;
use Illuminate\View\View;

class GuestOrderLookupController extends Controller
{
    public function __construct(
        protected GuestOrderLookupService $guestOrderLookup
    ) {}

    public function showForm(): View
    {
        return view('customer.orders.lookup');
    }

    public function lookup(GuestOrderLookupRequest $request): View
    {
        $data = $request->validated();

        $order = $this->guestOrderLookup->findByOrderNumberAndContact(
            $data['order_number'],
            $data['contact']
        );

        if ($order === null) {
            return view('customer.orders.lookup-result', [
                'order' => null,
                'notFound' => true,
            ]);
        }

        return view('customer.orders.lookup-result', [
            'order' => $order,
            'notFound' => false,
        ]);
    }
}

