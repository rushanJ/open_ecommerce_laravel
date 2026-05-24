<?php

namespace App\Http\Controllers\AdminApi\V1;

use App\Http\Resources\AdminApi\V1\AdminPaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends AdminApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $paginator = Payment::query()
            ->with(['order', 'method'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('payment_reference', 'like', '%'.$q.'%')
                        ->orWhere('provider_transaction_id', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('id')
            ->paginate((int) $request->query('per_page', 25))
            ->withQueryString();

        return $this->paginated($paginator, AdminPaymentResource::class);
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $payment->load(['order', 'method', 'transactions']);

        return $this->success((new AdminPaymentResource($payment))->resolve($request));
    }
}
