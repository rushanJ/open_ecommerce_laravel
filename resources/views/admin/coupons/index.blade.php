@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.coupons')" :subtitle="__('admin.coupons_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />

            <x-admin.select name="type" :label="__('admin.discount_type')">
                <option value="">{{ __('admin.all') }}</option>
                @foreach(['percentage','fixed_cart','fixed_product','free_shipping'] as $t)
                    <option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ __('admin.'.$t) }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="status" :label="__('admin.status')">
                <option value="">{{ __('admin.all') }}</option>
                @foreach(['active','inactive','expired'] as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </x-admin.select>

            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.coupons.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>

            <div class="flex items-end justify-end">
                <a href="{{ route('admin.coupons.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_coupon') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.coupon_code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.discount_type') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.value') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.used_count') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.date_range') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($coupons as $coupon)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $coupon->code }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $coupon->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ __('admin.'.$coupon->type) }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ number_format((float) $coupon->value, 2) }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $coupon->used_count }}</td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $coupon->starts_at?->format('Y-m-d') ?? '—' }} → {{ $coupon->ends_at?->format('Y-m-d') ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$coupon->status === 'active' ? 'success' : 'muted'">{{ ucfirst($coupon->status) }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="8" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4">
            {{ $coupons->links() }}
        </div>
    </x-admin.card>
@endsection

