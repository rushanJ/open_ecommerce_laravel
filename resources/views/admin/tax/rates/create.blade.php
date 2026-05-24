@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.create_tax_rate')" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.tax.rates.store') }}" class="space-y-6">
            @csrf
            @include('admin.tax.rates.partials.form', ['taxRate' => $taxRate, 'taxClasses' => $taxClasses])

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.tax.rates.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection

