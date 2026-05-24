@php
    /** @var \App\Models\Product $product */
    $tabs = [
        'basic' => __('admin.basic_details'),
        'pricing' => __('admin.pricing'),
        'inventory' => __('admin.inventory'),
        'categories' => __('admin.categories'),
        'seo' => __('admin.seo'),
        'images' => __('admin.images'),
        'attributes' => __('admin.product_attributes'),
        'variants' => __('admin.variants'),
    ];
@endphp

<div x-data="{ tab: 'basic' }" class="space-y-6">
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex min-w-max gap-2">
            @foreach ($tabs as $id => $label)
                <button
                    type="button"
                    class="rounded-lg px-3 py-2 text-sm font-semibold transition"
                    :class="tab === '{{ $id }}'
                        ? 'bg-[color:var(--mk-admin-primary)] text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white'"
                    @click="tab = '{{ $id }}'"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div x-show="tab === 'basic'" x-cloak>
        <x-admin.card :title="__('admin.basic_details')">
            @include('admin.products.partials.basic')
        </x-admin.card>
    </div>

    <div x-show="tab === 'pricing'" x-cloak>
        <x-admin.card :title="__('admin.pricing')">
            @include('admin.products.partials.pricing')
        </x-admin.card>
    </div>

    <div x-show="tab === 'inventory'" x-cloak>
        <x-admin.card :title="__('admin.inventory')">
            @include('admin.products.partials.inventory')
        </x-admin.card>
    </div>

    <div x-show="tab === 'categories'" x-cloak>
        <x-admin.card :title="__('admin.categories')">
            @include('admin.products.partials.categories')
        </x-admin.card>
    </div>

    <div x-show="tab === 'seo'" x-cloak>
        <x-admin.card :title="__('admin.seo')">
            @include('admin.products.partials.seo')
        </x-admin.card>
    </div>

    <div x-show="tab === 'images'" x-cloak>
        <x-admin.card :title="__('admin.images')">
            @include('admin.products.partials.images')
        </x-admin.card>
    </div>

    <div x-show="tab === 'attributes'" x-cloak>
        <x-admin.card :title="__('admin.product_attributes')">
            @include('admin.products.partials.attributes')
        </x-admin.card>
    </div>

    <div x-show="tab === 'variants'" x-cloak>
        <x-admin.card :title="__('admin.variants')">
            @include('admin.products.partials.variants')
        </x-admin.card>
    </div>
</div>
