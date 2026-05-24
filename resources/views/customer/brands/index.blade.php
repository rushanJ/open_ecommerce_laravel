@extends('customer.layouts.app')

@section('title', __('customer.brands'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.browse_brands') }}</h1>
        <p class="mt-2 max-w-2xl text-slate-600">{{ __('customer.hero_subtitle') }}</p>

        @if ($brands->isEmpty())
            <p class="mt-12 rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center text-slate-600">{{ __('customer.no_products_found') }}</p>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($brands as $brand)
                    <x-customer.brand-card :brand="$brand" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
