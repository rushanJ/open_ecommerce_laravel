@php
    /** @var \App\Models\AdminUser|null $admin */
    $admin = auth('admin')->user();
    $can = fn (string $permission): bool => $admin instanceof \App\Models\AdminUser && $admin->hasPermission($permission);

    $icon = function (string $name): string {
        return match ($name) {
            'dashboard' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 13.5 12 5l8 8.5M6.5 12v7h11v-7"/></svg>',
            'catalog' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 8 8-4 8 4-8 4-8-4Zm0 4 8 4 8-4M4 16l8 4 8-4"/></svg>',
            'inventory' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7.5h16M6 7.5V19h12V7.5M9 11h6"/></svg>',
            'orders' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h10M7 12h10M7 17h6M5 4h14v16H5z"/></svg>',
            'payments' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16v10H4zM4 10h16M8 15h3"/></svg>',
            'customers' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 19a4 4 0 0 0-8 0M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm6 8a3.5 3.5 0 0 0-3-3.45M18 8.5a2.5 2.5 0 0 1-2 2.45"/></svg>',
            'marketing' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 15V9l10-4v14L5 15Zm0 0 2 5h3l-2-4"/></svg>',
            'content' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5h14v14H5zM8 9h8M8 13h5"/></svg>',
            'reports' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19V5m0 14h14M9 16v-5m4 5V8m4 8v-3"/></svg>',
            'settings' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7-3.5a7.1 7.1 0 0 0-.1-1l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.8-1L14.4 3h-4.8L9.3 6a8 8 0 0 0-1.8 1l-2.4-1-2 3.5 2 1.5a7.1 7.1 0 0 0 0 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.8 1l.3 3h4.8l.3-3a8 8 0 0 0 1.8-1l2.4 1 2-3.5-2-1.5c.1-.3.1-.7.1-1Z"/></svg>',
            default => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>',
        };
    };

    $navItem = function (string $label, string $permission, ?string $routeName, array $routePatterns, string $iconName, ?string $fallbackHref = '#') use ($can, $icon): ?array {
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

        return [
            'label' => $label,
            'href' => $href,
            'isActive' => $isActive,
            'icon' => $icon($iconName),
        ];
    };

    $sections = [
        [
            'heading' => __('admin.overview'),
            'items' => array_filter([
                $navItem(__('admin.dashboard'), 'dashboard.view', 'admin.dashboard', ['admin.dashboard'], 'dashboard'),
            ]),
        ],
        [
            'heading' => __('admin.catalog'),
            'items' => array_filter([
                $navItem(__('admin.products'), 'products.view', 'admin.products.index', ['admin.products.*'], 'catalog'),
                $navItem(__('admin.categories'), 'categories.view', 'admin.categories.index', ['admin.categories.*'], 'catalog'),
                $navItem(__('admin.brands'), 'brands.view', 'admin.brands.index', ['admin.brands.*'], 'catalog'),
                $navItem(__('admin.attributes'), 'attributes.view', 'admin.attributes.index', ['admin.attributes.*'], 'catalog'),
                $navItem(__('admin.inventory'), 'inventory.view', 'admin.inventory.index', ['admin.inventory.index', 'admin.inventory.movements', 'admin.inventory.adjust', 'admin.inventory.adjust.form'], 'inventory'),
                $navItem(__('admin.warehouses'), 'inventory.view', 'admin.warehouses.index', ['admin.warehouses.*'], 'inventory'),
            ]),
        ],
        [
            'heading' => __('admin.sales'),
            'items' => array_filter([
                $navItem(__('admin.orders'), 'orders.view', 'admin.orders.index', ['admin.orders.*'], 'orders'),
                $navItem(__('admin.payments'), 'payments.view', 'admin.payments.index', ['admin.payments.*'], 'payments'),
                $navItem(__('admin.refunds'), 'orders.refund', null, ['admin.refunds.*'], 'payments'),
                $navItem(__('admin.shipments'), 'shipping.view', null, ['admin.shipments.*'], 'orders'),
            ]),
        ],
        [
            'heading' => __('admin.customers'),
            'items' => array_filter([
                $navItem(__('admin.customers'), 'customers.view', null, ['admin.customers.*', 'admin.customer-*'], 'customers'),
                $navItem(__('admin.customer_groups'), 'customers.view', null, ['admin.customer-groups.*'], 'customers'),
                $navItem(__('admin.reviews'), 'reviews.view', 'admin.reviews.index', ['admin.reviews.*'], 'customers'),
            ]),
        ],
        [
            'heading' => __('admin.marketing'),
            'items' => array_filter([
                $navItem(__('admin.coupons'), 'coupons.view', 'admin.coupons.index', ['admin.coupons.*'], 'marketing'),
                $navItem(__('admin.banners'), 'content.view', 'admin.banners.index', ['admin.banners.*'], 'marketing'),
                $navItem(__('admin.subscribers'), 'content.view', null, ['admin.subscribers.*'], 'marketing'),
            ]),
        ],
        [
            'heading' => __('admin.content'),
            'items' => array_filter([
                $navItem(__('admin.pages'), 'content.view', 'admin.pages.index', ['admin.pages.*'], 'content'),
                $navItem(__('admin.menus'), 'content.view', 'admin.menus.index', ['admin.menus.*'], 'content'),
                $navItem(__('admin.banners'), 'content.view', 'admin.banners.index', ['admin.banners.*'], 'content'),
                $navItem(__('admin.media_library'), 'content.view', 'admin.media.index', ['admin.media.*'], 'content'),
            ]),
        ],
        [
            'heading' => __('admin.reports'),
            'items' => array_filter([
                $navItem(__('admin.sales_report'), 'reports.view', 'admin.reports.sales', ['admin.reports.sales*'], 'reports'),
                $navItem(__('admin.orders_report'), 'reports.view', 'admin.reports.orders', ['admin.reports.orders*'], 'reports'),
                $navItem(__('admin.products_report'), 'reports.view', 'admin.reports.products', ['admin.reports.products*'], 'reports'),
                $navItem(__('admin.customers_report'), 'reports.view', 'admin.reports.customers', ['admin.reports.customers*'], 'reports'),
                $navItem(__('admin.stock_report'), 'reports.view', 'admin.reports.stock', ['admin.reports.stock*'], 'reports'),
                $navItem(__('admin.payments_report'), 'reports.view', 'admin.reports.payments', ['admin.reports.payments*'], 'reports'),
                $navItem(__('admin.coupons_report'), 'reports.view', 'admin.reports.coupons', ['admin.reports.coupons*'], 'reports'),
            ]),
        ],
        [
            'heading' => __('admin.settings'),
            'items' => array_filter([
                $navItem(__('admin.store_settings'), 'settings.view', 'admin.settings.store', ['admin.settings.store*'], 'settings'),
                $navItem(__('admin.general_settings'), 'settings.view', 'admin.settings.general', ['admin.settings.general*'], 'settings'),
                $navItem(__('admin.payment_settings'), 'settings.view', 'admin.settings.payments', ['admin.settings.payments*'], 'settings'),
                $navItem(__('admin.mail_settings'), 'settings.view', 'admin.settings.mail', ['admin.settings.mail*'], 'settings'),
                $navItem(__('admin.seo_settings'), 'settings.view', 'admin.settings.seo', ['admin.settings.seo*'], 'settings'),
                $navItem(__('admin.seo_redirects'), 'settings.view', 'admin.seo.redirects.index', ['admin.seo.redirects.*'], 'settings'),
                $navItem(__('admin.api_tokens'), 'settings.view', 'admin.api-tokens.index', ['admin.api-tokens.*'], 'settings'),
                $navItem(__('admin.webhooks'), 'settings.view', 'admin.webhooks.index', ['admin.webhooks.*', 'admin.webhook-deliveries.*'], 'settings'),
                $navItem(__('admin.shipping_settings'), 'settings.view', null, ['admin.settings.shipping*'], 'settings'),
                $navItem(__('admin.tax_settings'), 'taxes.view', 'admin.tax.rates.index', ['admin.tax.*', 'admin.taxes.*', 'admin.settings.tax*'], 'settings'),
                $navItem(__('admin.admin_users'), 'admins.view', null, ['admin.admins.*'], 'settings'),
                $navItem(__('admin.roles_permissions'), 'roles.view', null, ['admin.roles.*', 'admin.permissions.*'], 'settings'),
                $navItem(__('admin.system_logs'), 'activity_logs.view', null, ['admin.activity-logs.*', 'admin.logs.*'], 'settings'),
            ]),
        ],
    ];

    $brandName = config('open_ecommerce_laravel.admin.theme.brand_name', config('app.name'));
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 w-72 shrink-0 -translate-x-full p-3 text-slate-300 transition-all duration-300 ease-out lg:translate-x-0"
    :class="{
        '-translate-x-full': !sidebarOpen,
        'translate-x-0': sidebarOpen,
        'lg:w-24': sidebarCollapsed,
        'lg:w-80': !sidebarCollapsed
    }"
    aria-label="{{ __('admin.brand_admin_title') }}"
>
    <div class="flex h-full flex-col overflow-hidden rounded-[2rem] border border-white/10 bg-[#050816]/95 shadow-[0_28px_80px_-30px_rgba(2,6,23,0.95)] ring-1 ring-white/10 backdrop-blur-2xl">
        <div class="border-b border-white/10 p-4">
            <div class="flex items-center gap-3">
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-black text-white shadow-lg shadow-indigo-600/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300">
                    {{ \Illuminate\Support\Str::of($brandName)->substr(0, 2)->upper() }}
                </a>
                <div class="min-w-0 flex-1" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                    <p class="truncate text-sm font-semibold text-white">{{ $brandName }}</p>
                    <p class="truncate text-xs font-medium text-slate-400">{{ __('admin.admin_panel') }}</p>
                </div>
                <button
                    type="button"
                    class="rounded-2xl p-2 text-slate-400 transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 lg:hidden"
                    @click="sidebarOpen = false"
                    aria-label="{{ __('admin.close_menu') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-4 rounded-3xl bg-white/[0.06] p-3 ring-1 ring-white/10" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-slate-500">Workspace</p>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ __('admin.store_admin_label') }}</p>
                        <p class="truncate text-xs text-slate-400">Commerce operations</p>
                    </div>
                    <span class="grid h-8 w-8 place-items-center rounded-2xl bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/20">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    </span>
                </div>
            </div>
        </div>

        <nav class="scrollbar-thin flex-1 space-y-6 overflow-y-auto px-3 py-5 text-sm" aria-label="{{ __('admin.main_menu') }}">
            @foreach ($sections as $section)
                @php $items = array_values(array_filter($section['items'])); @endphp
                @if (count($items) > 0)
                    <div>
                        <p class="px-3 text-[0.65rem] font-bold uppercase tracking-[0.22em] text-slate-500" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $section['heading'] }}</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($items as $item)
                                @php /** @var array{label: string, href: string, isActive: bool, icon: string} $item */ @endphp
                                <li>
                                    <a
                                        href="{{ $item['href'] }}"
                                        title="{{ $item['label'] }}"
                                        @if ($item['isActive']) aria-current="page" @endif
                                        @class([
                                            'group relative flex items-center gap-3 overflow-hidden rounded-2xl px-3 py-2.5 transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300',
                                            'bg-white/12 text-white shadow-lg shadow-indigo-950/40 ring-1 ring-white/10 before:absolute before:inset-y-2 before:left-0 before:w-1 before:rounded-r-full before:bg-indigo-400' => $item['isActive'],
                                            'text-slate-400 hover:bg-white/[0.07] hover:text-white' => ! $item['isActive'],
                                        ])
                                    >
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl transition {{ $item['isActive'] ? 'bg-indigo-500/20 text-indigo-200' : 'bg-white/[0.04] text-slate-400 group-hover:text-white' }}" aria-hidden="true">
                                            {!! $item['icon'] !!}
                                        </span>
                                        <span class="truncate font-semibold" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <button
                type="button"
                class="hidden w-full items-center justify-center gap-2 rounded-2xl bg-white/[0.06] px-3 py-2.5 text-sm font-semibold text-slate-300 ring-1 ring-white/10 transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 lg:flex"
                @click="toggleSidebarMode()"
                :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <svg class="h-4 w-4 transition" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 6l-6 6 6 6" />
                </svg>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Compact mode</span>
            </button>
        </div>
    </div>
</aside>
