@extends('admin.layouts.app')

@section('title', __('admin.payments'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('admin.payments') }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('admin.coming_soon') }}</p>
            </div>

            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.search') }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900" />
                <select name="status" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">{{ __('admin.status') }}</option>
                    @foreach (['initiated','pending','authorized','paid','failed','cancelled','refunded'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-gray-100 dark:text-gray-900">{{ __('admin.filter') }}</button>
                <a href="{{ route('admin.payments.index') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ __('admin.reset') }}</a>
            </form>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">{{ __('admin.orders') }}</th>
                        <th class="px-4 py-3">{{ __('admin.payment_reference') }}</th>
                        <th class="px-4 py-3">{{ __('admin.payments') }}</th>
                        <th class="px-4 py-3">{{ __('admin.status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.revenue') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-900">
                    @forelse ($payments as $p)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="px-4 py-3 font-medium">{{ $p->order?->order_number ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->payment_reference ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $p->method?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $p->status }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float) $p->amount, 2) }} {{ $p->currency_code }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.payments.show', $p) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">{{ __('admin.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">{{ __('admin.no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $payments->links() }}</div>
    </div>
@endsection

