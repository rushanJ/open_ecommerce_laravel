@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.reviews')" :subtitle="__('admin.reviews_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />

            <x-admin.select name="status" :label="__('admin.status')">
                <option value="">{{ __('admin.all') }}</option>
                @foreach(['pending','approved','rejected','spam'] as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="rating" :label="__('admin.rating')">
                <option value="">{{ __('admin.all') }}</option>
                @for($i=5; $i>=1; $i--)
                    <option value="{{ $i }}" @selected((string) ($filters['rating'] ?? '') === (string) $i)>{{ $i }}</option>
                @endfor
            </x-admin.select>

            <x-admin.select name="verified" :label="__('admin.verified')">
                <option value="">{{ __('admin.all') }}</option>
                <option value="1" @selected(($filters['verified'] ?? '') === '1')>{{ __('admin.verified') }}</option>
                <option value="0" @selected(($filters['verified'] ?? '') === '0')>{{ __('admin.not_verified') }}</option>
            </x-admin.select>

            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.reviews.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.product') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.customer') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.rating') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.title') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.verified') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.created') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($reviews as $review)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $review->product?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $review->customer?->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $review->rating }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $review->title ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$review->is_verified_purchase ? 'success' : 'muted'">
                            {{ $review->is_verified_purchase ? __('admin.verified') : __('admin.not_verified') }}
                        </x-admin.badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="match($review->status){'approved'=>'success','pending'=>'warning','rejected'=>'danger','spam'=>'muted',default=>'muted'}">
                            {{ ucfirst($review->status) }}
                        </x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ optional($review->created_at)->format('Y-m-d') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.reviews.show', $review) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">
                            {{ __('admin.view') }}
                        </a>
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
            {{ $reviews->links() }}
        </div>
    </x-admin.card>
@endsection

