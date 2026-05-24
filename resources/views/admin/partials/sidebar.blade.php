@php
    /** @var \App\Models\AdminUser|null $admin */
    $admin = auth('admin')->user();
    $can = fn (string $permission): bool => $admin instanceof \App\Models\AdminUser && $admin->hasPermission($permission);
@endphp

@php
    $navItem = function (string $label, string $permission, ?string $routeName, array $routePatterns, ?string $fallbackHref = '#') use ($can): ?array {
        if (! $can($permission)) {
            return null;
        }
        $href = $fallbackHref ?? '#';
        $isActive = false;
        foreach ($routePatterns as $pattern) {
            if (request()->routeIs($pattern)) {
                $isActive = true;
                break;
            }
        }
        if ($routeName !== null && \Illuminate\Support\Facades\Route::has($routeName)) {
            $href = route($routeName);
        }

        return compact('label', 'href', 'isActive');
    };

    $sections = [
        [
            'heading' => __('admin.overview'),
            'items' => array_filter([
                $navItem(__('admin.dashboard'), 'dashboard.view', 'admin.dashboard', ['admin.dashboard']),
            ]),
        ],
        [
            'heading' => __('admin.catalog'),
            'items' => array_filter([
                $navItem(__('admin.products'), 'products.view', 'admin.products.index', ['admin.products.*']),
                $navItem(__('admin.categories'), 'categories.view', 'admin.categories.index', ['admin.categories.*']),
                $navItem(__('admin.brands'), 'brands.view', 'admin.brands.index', ['admin.brands.*']),
                $navItem(__('admin.attributes'), 'attributes.view', 'admin.attributes.index', ['admin.attributes.*']),
                $navItem(__('admin.inventory'), 'inventory.view', 'admin.inventory.index', ['admin.inventory.index', 'admin.inventory.movements', 'admin.inventory.adjust', 'admin.inventory.adjust.form']),
                $navItem(__('admin.warehouses'), 'inventory.view', 'admin.warehouses.index', ['admin.warehouses.*']),
            ]),
        ],
        [
            'heading' => __('admin.sales'),
            'items' => array_filter([
                $navItem(__('admin.orders'), 'orders.view', 'admin.orders.index', ['admin.orders.*']),
                $navItem(__('admin.payments'), 'payments.view', null, ['admin.payments.*']),
                $navItem(__('admin.refunds'), 'orders.refund', null, ['admin.refunds.*']),
                $navItem(__('admin.shipments'), 'shipping.view', null, ['admin.shipments.*']),
            ]),
        ],
        [
            'heading' => __('admin.customers'),
            'items' => array_filter([
                $navItem(__('admin.customers'), 'customers.view', null, ['admin.customers.*', 'admin.customer-*']),
                $navItem(__('admin.customer_groups'), 'customers.view', null, ['admin.customer-groups.*']),
                $navItem(__('admin.reviews'), 'reviews.view', null, ['admin.reviews.*']),
            ]),
        ],
        [
            'heading' => __('admin.marketing'),
            'items' => array_filter([
                $navItem(__('admin.coupons'), 'coupons.view', 'admin.coupons.index', ['admin.coupons.*']),
                $navItem(__('admin.banners'), 'content.view', null, ['admin.banners.*']),
                $navItem(__('admin.subscribers'), 'content.view', null, ['admin.subscribers.*']),
            ]),
        ],
        [
            'heading' => __('admin.content'),
            'items' => array_filter([
                $navItem(__('admin.pages'), 'content.view', 'admin.pages.index', ['admin.pages.*']),
                $navItem(__('admin.menus'), 'content.view', 'admin.menus.index', ['admin.menus.*']),
                $navItem(__('admin.banners'), 'content.view', 'admin.banners.index', ['admin.banners.*']),
                $navItem(__('admin.media_library'), 'content.view', 'admin.media.index', ['admin.media.*']),
            ]),
        ],
        [
            'heading' => __('admin.reports'),
            'items' => array_filter([
                $navItem(__('admin.sales_report'), 'reports.view', 'admin.reports.sales', ['admin.reports.sales*']),
                $navItem(__('admin.orders_report'), 'reports.view', 'admin.reports.orders', ['admin.reports.orders*']),
                $navItem(__('admin.products_report'), 'reports.view', 'admin.reports.products', ['admin.reports.products*']),
                $navItem(__('admin.customers_report'), 'reports.view', 'admin.reports.customers', ['admin.reports.customers*']),
                $navItem(__('admin.stock_report'), 'reports.view', 'admin.reports.stock', ['admin.reports.stock*']),
                $navItem(__('admin.payments_report'), 'reports.view', 'admin.reports.payments', ['admin.reports.payments*']),
                $navItem(__('admin.coupons_report'), 'reports.view', 'admin.reports.coupons', ['admin.reports.coupons*']),
            ]),
        ],
        [
            'heading' => __('admin.settings'),
            'items' => array_filter([
                $navItem(__('admin.store_settings'), 'settings.view', 'admin.settings.store', ['admin.settings.store*']),
                $navItem(__('admin.general_settings'), 'settings.view', 'admin.settings.general', ['admin.settings.general*']),
                $navItem(__('admin.payment_settings'), 'settings.view', 'admin.settings.payments', ['admin.settings.payments*']),
                $navItem(__('admin.mail_settings'), 'settings.view', 'admin.settings.mail', ['admin.settings.mail*']),
                $navItem(__('admin.seo_settings'), 'settings.view', 'admin.settings.seo', ['admin.settings.seo*']),
                $navItem(__('admin.seo_redirects'), 'settings.view', 'admin.seo.redirects.index', ['admin.seo.redirects.*']),
                $navItem(__('admin.api_tokens'), 'settings.view', 'admin.api-tokens.index', ['admin.api-tokens.*']),
                $navItem(__('admin.webhooks'), 'settings.view', 'admin.webhooks.index', ['admin.webhooks.*', 'admin.webhook-deliveries.*']),
                $navItem(__('admin.shipping_settings'), 'settings.view', null, ['admin.settings.shipping*']),
                $navItem(__('admin.tax_settings'), 'taxes.view', 'admin.tax.rates.index', ['admin.tax.*', 'admin.taxes.*', 'admin.settings.tax*']),
                $navItem(__('admin.admin_users'), 'admins.view', null, ['admin.admins.*']),
                $navItem(__('admin.roles_permissions'), 'roles.view', null, ['admin.roles.*', 'admin.permissions.*']),
                $navItem(__('admin.system_logs'), 'activity_logs.view', null, ['admin.activity-logs.*', 'admin.logs.*']),
            ]),
        ],
    ];

    $brandName = config('open_ecommerce_laravel.admin.theme.brand_name', config('app.name'));
@endphp

{{-- href="#" for modules not built yet; route() used when the named route exists (e.g. dashboard). --}}
<aside
    class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 transform border-r border-gray-800/80 bg-slate-950 text-slate-300 transition-transform duration-200 ease-out lg:static lg:translate-x-0"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
    aria-label="{{ __('admin.brand_admin_title') }}"
>
    <div class="flex h-full flex-col">
        <div class="flex h-16 items-center justify-between border-b border-gray-800/80 px-4">
            <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="min-w-0 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--mk-admin-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950">
                <span class="block truncate text-sm font-semibold tracking-tight text-white">
                    {{ $brandName }}
                    <span class="font-normal text-slate-400">{{ __('admin.admin_panel') }}</span>
                </span>
            </a>
            <button
                type="button"
                class="rounded-md p-1.5 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden"
                @click="sidebarOpen = false"
                aria-label="{{ __('admin.close_menu') }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="flex-1 space-y-7 overflow-y-auto px-2 py-5 text-sm" aria-label="{{ __('admin.main_menu') }}">
            @foreach ($sections as $section)
                @php $items = array_values(array_filter($section['items'])); @endphp
                @if (count($items) > 0)
                    <div>
                        <p class="px-3 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500">{{ $section['heading'] }}</p>
                        <ul class="mt-2.5 space-y-0.5">
                            @foreach ($items as $item)
                                @php /** @var array{label: string, href: string, isActive: bool} $item */ @endphp
                                <li>
                                    <a
                                        href="{{ $item['href'] }}"
                                        @if ($item['isActive']) aria-current="page" @endif
                                        @class([
                                            'group flex rounded-lg px-3 py-2 transition border-l-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--mk-admin-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950',
                                            'border-[color:var(--mk-admin-primary)] bg-white/10 text-white' => $item['isActive'],
                                            'border-transparent text-slate-300 hover:border-slate-600 hover:bg-white/5 hover:text-white' => ! $item['isActive'],
                                        ])
                                    >
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>
</aside>
