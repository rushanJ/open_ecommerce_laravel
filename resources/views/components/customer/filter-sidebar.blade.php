@props([
    'action',
    'categories',
    'brands',
    'lockCategory' => false,
    'lockBrand' => false,
])

<form method="GET" action="{{ $action }}" class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
    <div>
        <label for="f_q" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.search') }}</label>
        <input type="search" id="f_q" name="q" value="{{ request('q', '') }}" placeholder="{{ __('customer.search_placeholder') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" />
    </div>

    @unless ($lockCategory)
        <div>
            <label for="f_cat" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.category') }}</label>
            <select id="f_cat" name="category" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500">
                <option value="">{{ __('customer.filter_all_categories') }}</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected((string) request('category') === (string) $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    @endunless

    @unless ($lockBrand)
        <div>
            <label for="f_brand" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.brand') }}</label>
            <select id="f_brand" name="brand" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500">
                <option value="">{{ __('customer.filter_all_brands') }}</option>
                @foreach ($brands as $b)
                    <option value="{{ $b->id }}" @selected((string) request('brand') === (string) $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
    @endunless

    <div class="grid grid-cols-2 gap-2">
        <div>
            <label for="f_min" class="block text-xs font-semibold text-slate-500">{{ __('customer.min_price') }}</label>
            <input type="number" step="0.01" min="0" id="f_min" name="min_price" value="{{ request('min_price', '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-2 py-2 text-sm" />
        </div>
        <div>
            <label for="f_max" class="block text-xs font-semibold text-slate-500">{{ __('customer.max_price') }}</label>
            <input type="number" step="0.01" min="0" id="f_max" name="max_price" value="{{ request('max_price', '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-2 py-2 text-sm" />
        </div>
    </div>

    <div>
        <label for="f_stock" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.stock_status_filter') }}</label>
        <select id="f_stock" name="stock_status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="">{{ __('customer.filter') }} — </option>
            <option value="in_stock" @selected(request('stock_status') === 'in_stock')>{{ __('customer.in_stock') }}</option>
            <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>{{ __('customer.out_of_stock') }}</option>
            <option value="on_backorder" @selected(request('stock_status') === 'on_backorder')>{{ __('customer.on_backorder') }}</option>
        </select>
    </div>

    <div>
        <label for="f_sort" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.sort') }}</label>
        <select id="f_sort" name="sort" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="" @selected(request('sort') === null || request('sort') === '')>{{ __('customer.newest') }}</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('customer.price_low_to_high') }}</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('customer.price_high_to_low') }}</option>
            <option value="name" @selected(request('sort') === 'name')>{{ __('customer.name_a_to_z') }}</option>
        </select>
    </div>

    <div class="flex flex-wrap gap-2 pt-1">
        <button type="submit" class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('customer.filter') }}</button>
        <a href="{{ $action }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('customer.reset') }}</a>
    </div>
</form>
