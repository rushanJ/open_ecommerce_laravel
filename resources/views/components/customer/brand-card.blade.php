@props([
    'brand',
])

@php
    /** @var \App\Models\Brand $brand */
    $href = route('customer.brands.show', $brand);
    $count = $brand->storefront_products_count ?? 0;
    $logoAvailable = $brand->logo_path
        && (\Illuminate\Support\Str::startsWith($brand->logo_path, ['http://', 'https://']) || media_exists($brand->logo_path));
@endphp

<a href="{{ $href }}" class="group flex min-h-32 flex-col items-center justify-center rounded-3xl border border-slate-200/70 bg-white p-5 text-center shadow-sm ring-1 ring-slate-900/[0.02] transition duration-[180ms] ease-in-out hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100/40 focus:outline-none focus:ring-4 focus:ring-blue-100">
    @if ($logoAvailable)
        <span class="mb-3 flex h-14 items-center justify-center">
            <img src="{{ media_url($brand->logo_path) }}" alt="" class="max-h-10 w-auto object-contain" loading="lazy" />
        </span>
    @else
        <span class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-50 to-blue-50 text-xl font-black text-blue-700">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($brand->name, 0, 1)) }}</span>
    @endif
    <h3 class="text-sm font-black text-slate-950 group-hover:text-blue-700">{{ $brand->name }}</h3>
    <p class="mt-1 text-xs text-slate-500">{{ __('customer.products_count', ['count' => $count]) }}</p>
</a>
