<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\GuestOrderLookupRequest;
use App\Http\Resources\Api\V1\OrderDetailResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\GuestOrderLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function __construct(
        protected GuestOrderLookupService $guestOrderLookup
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = $request->user();

        $orders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->orderByDesc('placed_at')
            ->orderByDesc('id')
            ->paginate(15);

        return $this->paginated($orders, OrderResource::class);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = $request->user();

        if ((int) $order->customer_id !== (int) $customer->getKey()) {
            return $this->error(__('customer.order_not_found'), 404);
        }

        $order->load(['items', 'addresses']);

        return $this->success((new OrderDetailResource($order))->resolve());
    }

    public function lookup(GuestOrderLookupRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = $this->guestOrderLookup->findByOrderNumberAndContact(
            $data['order_number'],
            $data['contact']
        );

        if ($order === null) {
            return $this->error(__('customer.order_not_found'), 404);
        }

        return $this->success((new OrderDetailResource($order))->resolve());
    }
}
