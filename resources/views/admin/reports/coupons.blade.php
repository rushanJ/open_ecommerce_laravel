@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.coupons_report')" />

    @include('admin.reports.partials.filters', ['filters' => $filters ?? [], 'exportType' => 'coupons'])

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.coupon_code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.discount_type') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.used_count') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.discount_total') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rows as $c)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $c->code }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ __('admin.'.$c->type) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ (int) ($c->used_count_calc ?? $c->used_count ?? 0) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) ($c->discount_total ?? 0), 2) }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$c->status === 'active' ? 'success' : 'muted'">{{ ucfirst($c->status) }}</x-admin.badge>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="5" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    </x-admin.card>
@endsection

