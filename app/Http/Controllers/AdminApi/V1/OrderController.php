<?php

namespace App\Http\Controllers\AdminApi\V1;

use App\Http\Resources\AdminApi\V1\AdminOrderDetailResource;
use App\Http\Resources\AdminApi\V1\AdminOrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends AdminApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $paginator = Order::query()
            ->with(['customer'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('order_number', 'like', '%'.$q.'%')
                        ->orWhere('customer_email', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('placed_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('placed_at', '<=', $dateTo))
            ->orderByDesc('placed_at')
            ->paginate((int) $request->query('per_page', 25))
            ->withQueryString();

        return $this->paginated($paginator, AdminOrderResource::class);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $order->load(['customer', 'items', 'addresses', 'coupon']);

        return $this->success((new AdminOrderDetailResource($order))->resolve($request));
    }
}
