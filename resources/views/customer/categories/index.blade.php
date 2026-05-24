@extends('customer.layouts.app')

@section('title', __('customer.categories'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.categories') }}</h1>
        <div class="mt-10 space-y-12">
            @foreach ($categories as $parent)
                <div>
                    <x-customer.category-card :category="$parent" />
                    @if ($parent->activeChildren->isNotEmpty())
                        <div class="mt-6 grid gap-4 border-l-2 border-emerald-100 pl-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($parent->activeChildren as $child)
                                <a href="{{ route('customer.categories.show', $child) }}" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-800 shadow-sm transition hover:border-emerald-200 hover:text-emerald-800">
                                    {{ $child->name }}
                                    <span class="mt-1 block text-xs font-normal text-slate-500">{{ __('customer.products_count', ['count' => $child->storefront_products_count ?? 0]) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
