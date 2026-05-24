<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::query()
            ->with(['order', 'method'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('provider')) {
            $query->whereHas('method', fn ($q) => $q->where('provider', $request->input('provider')));
        }
        if ($request->filled('q')) {
            $q = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($q): void {
                $w->where('payment_reference', 'like', $q)
                    ->orWhere('provider_transaction_id', 'like', $q);
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['order', 'method', 'transactions' => fn ($q) => $q->orderByDesc('id')]);

        return view('admin.payments.show', compact('payment'));
    }
}

