@extends('admin.layouts.app')

@section('title', __('admin.import_job').' — '.config('app.name'))

@section('breadcrumb', __('admin.import_job'))

@section('content')
    <x-admin.page-header :title="__('admin.import_job')" :subtitle="$job->filename ?? ''" />

    <x-admin.card class="mt-6">
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <div>
                <dt class="text-slate-500">{{ __('admin.status') }}</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $job->status }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('admin.total_rows') }}</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $job->total_rows }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('admin.success_rows') }}</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $job->success_rows }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('admin.failed_rows') }}</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $job->failed_rows }}</dd>
            </div>
        </dl>

        @if(!empty($job->errors))
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="text-sm font-semibold text-red-900">{{ __('admin.validation_errors') }}</p>
                <ul class="mt-2 list-inside list-disc text-sm text-red-800">
                    @foreach($job->errors as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-admin.card>

    @if($job->canProcess())
        <x-admin.card class="mt-6">
            <p class="text-sm text-slate-600">{{ __('admin.preview_import_ready') }}</p>
            <form method="POST" action="{{ route('admin.import-jobs.process', $job) }}" class="mt-4">
                @csrf
                <x-admin.button type="submit">{{ __('admin.process_import') }}</x-admin.button>
            </form>
        </x-admin.card>
    @endif

    <x-admin.card class="mt-6">
        <h2 class="text-base font-semibold text-slate-900">{{ __('admin.preview_import') }} ({{ __('admin.first_n_rows', ['n' => 50]) }})</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-xs text-slate-700">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="py-2 pr-3">#</th>
                        <th class="py-2 pr-3">{{ __('admin.status') }}</th>
                        <th class="py-2 pr-3">sku</th>
                        <th class="py-2 pr-3">product_type</th>
                        <th class="py-2 pr-3">name</th>
                        <th class="py-2 pr-3">variant_sku</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($previewRows as $r)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-3">{{ $r->row_number }}</td>
                            <td class="py-2 pr-3">{{ $r->status }}</td>
                            <td class="py-2 pr-3 font-mono">{{ $r->raw_data['sku'] ?? '' }}</td>
                            <td class="py-2 pr-3">{{ $r->raw_data['product_type'] ?? '' }}</td>
                            <td class="py-2 pr-3">@php($nm = $r->raw_data['name'] ?? ''){{ strlen($nm) > 40 ? substr($nm, 0, 40).'…' : $nm }}</td>
                            <td class="py-2 pr-3 font-mono">{{ $r->raw_data['variant_sku'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-slate-500">{{ __('admin.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    @if($failedRows->isNotEmpty())
        <x-admin.card class="mt-6">
            <h2 class="text-base font-semibold text-red-800">{{ __('admin.failed_rows_detail') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach($failedRows as $r)
                    <li class="rounded border border-red-100 bg-red-50/50 p-2">
                        <span class="font-semibold">{{ __('admin.row') }} {{ $r->row_number }}</span>
                        — {{ $r->status }}
                        @if(!empty($r->errors))
                            <ul class="mt-1 list-inside list-disc text-red-800">
                                @foreach($r->errors as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <div class="mt-6">
        <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.back_to_products') }}</a>
    </div>
@endsection
