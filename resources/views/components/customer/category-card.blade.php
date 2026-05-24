@props([
    'category',
])

@php
    /** @var \App\Models\Category $category */
    $href = route('customer.categories.show', $category);
    $count = $category->storefront_products_count ?? null;
    $imageAvailable = $category->image_path
        && (\Illuminate\Support\Str::startsWith($category->image_path, ['http://', 'https://']) || media_exists($category->image_path));
@endphp

<a href="{{ $href }}" class="group flex items-center gap-4 rounded-3xl border border-slate-200/70 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.02] transition duration-[180ms] ease-in-out hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100/40 focus:outline-none focus:ring-4 focus:ring-blue-100">
    <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 to-violet-50 text-sm font-black text-blue-700">
        @if ($imageAvailable)
            <img src="{{ media_url($category->image_path) }}" alt="" class="h-full w-full object-cover" loading="lazy" />
        @else
            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 1)) }}
        @endif
    </span>
    <span class="min-w-0">
        <span class="block truncate text-sm font-black text-slate-950 group-hover:text-blue-700">{{ $category->name }}</span>
        @if ($count !== null)
            <span class="mt-1 block text-xs text-slate-500">{{ __('customer.products_count', ['count' => $count]) }}</span>
        @endif
    </span>
</a>
