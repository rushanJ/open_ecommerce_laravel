<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ApproveRefundRequest;
use App\Http\Requests\Admin\RejectRefundRequest;
use App\Http\Requests\Admin\RequestRefundRequest;
use App\Models\Order;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;

class RefundController extends BaseAdminController
{
    public function __construct(
        protected RefundService $refundService
    ) {}

    public function store(RequestRefundRequest $request, Order $order): RedirectResponse
    {
        $refund = $this->refundService->requestRefund($order, $request->validated(), auth('admin')->id());
        $this->logAdminActivity('refunds', 'request', $refund, [], [
            'order_id' => $order->getKey(),
            'amount' => $refund->amount ?? null,
        ]);

        return back()->with('success', __('admin.refund_requested'));
    }

    public function approve(ApproveRefundRequest $request, Refund $refund): RedirectResponse
    {
        $before = $refund->only(['status']);
        $approved = $this->refundService->approveRefund($refund, auth('admin')->id());
        $this->logAdminActivity('refunds', 'approve', $approved, $before, $approved->only(['status']));

        return back()->with('success', __('admin.refund_approved'));
    }

    public function reject(RejectRefundRequest $request, Refund $refund): RedirectResponse
    {
        $before = $refund->only(['status']);
        $rejected = $this->refundService->rejectRefund($refund, (string) $request->input('reason'), auth('admin')->id());
        $this->logAdminActivity('refunds', 'reject', $rejected, $before, $rejected->only(['status']));

        return back()->with('success', __('admin.refund_rejected'));
    }
}

