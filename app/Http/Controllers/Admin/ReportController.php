<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reports
    ) {}

    public function sales(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->reports->salesReport($filters);

        return view('admin.reports.sales', compact('rows', 'filters'));
    }

    public function orders(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->reports->orderReport($filters);

        return view('admin.reports.orders', compact('rows', 'filters'));
    }

    public function products(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->reports->productReport($filters);

        return view('admin.reports.products', compact('rows', 'filters'));
    }

    public function customers(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->reports->customerReport($filters);

        return view('admin.reports.customers', compact('rows', 'filters'));
    }

    public function stock(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->reports->stockReport($filters);

        return view('admin.reports.stock', compact('rows', 'filters'));
    }

    public function payments(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->reports->paymentReport($filters);

        return view('admin.reports.payments', compact('rows', 'filters'));
    }

    public function coupons(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->reports->couponReport($filters);

        return view('admin.reports.coupons', compact('rows', 'filters'));
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        $filters = $this->filters($request);

        $filename = 'report-'.$type.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($type, $filters): void {
            $out = fopen('php://output', 'wb');
            if (! is_resource($out)) {
                return;
            }

            $write = fn (array $row) => fputcsv($out, $row);

            switch ($type) {
                case 'sales':
                    $write(['Date', 'Orders Count', 'Gross Sales', 'Discounts', 'Tax', 'Shipping', 'Refunds', 'Net Sales']);
                    foreach ($this->reports->salesReport($filters)->items() as $r) {
                        $gross = (float) ($r->gross_sales ?? 0);
                        $refunds = (float) ($r->refunds ?? 0);
                        $write([
                            (string) $r->day,
                            (int) $r->orders_count,
                            number_format($gross, 4, '.', ''),
                            number_format((float) $r->discounts, 4, '.', ''),
                            number_format((float) $r->tax, 4, '.', ''),
                            number_format((float) $r->shipping, 4, '.', ''),
                            number_format($refunds, 4, '.', ''),
                            number_format(max(0.0, $gross - $refunds), 4, '.', ''),
                        ]);
                    }
                    break;

                case 'orders':
                    $write(['Order Number', 'Customer', 'Status', 'Payment Status', 'Total', 'Date']);
                    foreach ($this->reports->orderReport($filters)->items() as $o) {
                        $write([
                            $o->order_number,
                            $o->customer?->email ?? $o->customer_email ?? '',
                            $o->status,
                            $o->payment_status,
                            number_format((float) $o->grand_total, 4, '.', ''),
                            optional($o->placed_at)->format('Y-m-d H:i:s') ?? '',
                        ]);
                    }
                    break;

                case 'products':
                    $write(['Product', 'SKU', 'Brand', 'Sold Quantity', 'Revenue', 'Stock']);
                    foreach ($this->reports->productReport($filters)->items() as $p) {
                        $write([
                            (string) $p->product_name,
                            (string) ($p->sku ?? ''),
                            (string) ($p->brand_name ?? ''),
                            (string) $p->sold_quantity,
                            number_format((float) $p->revenue, 4, '.', ''),
                            (string) ($p->stock_quantity ?? ''),
                        ]);
                    }
                    break;

                case 'customers':
                    $write(['Customer', 'Email', 'Orders Count', 'Total Spent', 'Last Order']);
                    foreach ($this->reports->customerReport($filters)->items() as $c) {
                        $name = trim(($c->first_name ?? '').' '.($c->last_name ?? ''));
                        $write([
                            $name !== '' ? $name : '—',
                            (string) ($c->email ?? ''),
                            (int) $c->orders_count,
                            number_format((float) $c->total_spent, 4, '.', ''),
                            (string) ($c->last_order_at ?? ''),
                        ]);
                    }
                    break;

                case 'stock':
                    $write(['Product', 'Variant', 'Warehouse', 'Quantity', 'Reserved', 'Available', 'Stock Status']);
                    foreach ($this->reports->stockReport($filters)->items() as $s) {
                        $write([
                            $s->product?->name ?? '',
                            $s->variant?->name ?? '',
                            $s->warehouse?->name ?? '',
                            number_format((float) $s->quantity, 4, '.', ''),
                            number_format((float) $s->reserved_quantity, 4, '.', ''),
                            number_format((float) $s->available_quantity, 4, '.', ''),
                            $s->product?->stock_status ?? '',
                        ]);
                    }
                    break;

                case 'payments':
                    $write(['Payment Reference', 'Order', 'Provider', 'Amount', 'Status', 'Paid At']);
                    foreach ($this->reports->paymentReport($filters)->items() as $p) {
                        $write([
                            $p->payment_reference,
                            $p->order?->order_number ?? '',
                            $p->method?->provider ?? '',
                            number_format((float) $p->amount, 4, '.', ''),
                            $p->status,
                            optional($p->paid_at)->format('Y-m-d H:i:s') ?? '',
                        ]);
                    }
                    break;

                case 'coupons':
                    $write(['Code', 'Type', 'Used Count', 'Discount Total', 'Status']);
                    foreach ($this->reports->couponReport($filters)->items() as $c) {
                        $write([
                            $c->code,
                            $c->type,
                            (int) ($c->used_count_calc ?? $c->used_count ?? 0),
                            number_format((float) ($c->discount_total ?? 0), 4, '.', ''),
                            $c->status,
                        ]);
                    }
                    break;

                default:
                    $write(['Unsupported export type.']);
                    break;
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return $request->only([
            'date_from',
            'date_to',
            'status',
            'payment_status',
            'product_id',
            'category_id',
            'brand_id',
            'customer_id',
        ]);
    }
}

