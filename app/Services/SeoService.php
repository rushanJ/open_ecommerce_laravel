<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SeoService
{
    public function __construct(
        protected SettingService $settings,
        protected StoreService $stores,
    ) {}

    public function metaTitle(?string $title = null): string
    {
        $t = $title !== null ? trim($title) : '';
        if ($t !== '') {
            return Str::limit($t, 200, '');
        }

        $fromSettings = $this->settings->get('seo.default_title');
        if (is_string($fromSettings) && trim($fromSettings) !== '') {
            return trim($fromSettings);
        }

        try {
            $name = $this->stores->currentStore()->name;
            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        } catch (\Throwable) {
            // ignore
        }

        return (string) config('app.name', 'open_ecommerce_laravel');
    }

    public function metaDescription(?string $description = null): string
    {
        $d = $description !== null ? trim(strip_tags($description)) : '';
        if ($d !== '') {
            return Str::limit(preg_replace('/\s+/', ' ', $d) ?? $d, 320, '');
        }

        $fromSettings = $this->settings->get('seo.default_description');
        if (is_string($fromSettings) && trim(strip_tags($fromSettings)) !== '') {
            return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($fromSettings)) ?? ''), 320, '');
        }

        return '';
    }

    public function canonicalUrl(?string $url = null): string
    {
        if ($url !== null && trim($url) !== '') {
            return trim($url);
        }

        return url()->current();
    }

    public function ogImage(?string $path = null): ?string
    {
        if ($path !== null && trim($path) !== '') {
            return media_url(trim($path));
        }

        try {
            $logo = $this->stores->currentStore()->logo_path;
            if (is_string($logo) && trim($logo) !== '') {
                return media_url($logo);
            }
        } catch (\Throwable) {
            // ignore
        }

        return null;
    }

    public function robotsContent(): string
    {
        $maintenance = (bool) $this->settings->get('general.maintenance_mode', false)
            || (bool) $this->settings->get('store.maintenance_mode', false);

        try {
            if ($this->stores->currentStore()->status === 'maintenance') {
                $maintenance = true;
            }
        } catch (\Throwable) {
            // ignore
        }

        if ($maintenance) {
            return "User-agent: *\nDisallow: /\n";
        }

        $sitemap = url('/sitemap.xml');

        return "User-agent: *\nAllow: /\n\nSitemap: {$sitemap}\n";
    }

    /**
     * @return list<array{loc: string, lastmod: Carbon|null}>
     */
    public function sitemapItems(): array
    {
        $items = [];

        $items[] = [
            'loc' => url('/'),
            'lastmod' => null,
        ];

        $items[] = [
            'loc' => route('customer.products.index', absolute: true),
            'lastmod' => null,
        ];

        $items[] = [
            'loc' => route('customer.categories.index', absolute: true),
            'lastmod' => null,
        ];

        $items[] = [
            'loc' => route('customer.brands.index', absolute: true),
            'lastmod' => null,
        ];

        Product::query()
            ->forStorefront()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->chunkById(200, function ($products) use (&$items): void {
                foreach ($products as $p) {
                    $items[] = [
                        'loc' => route('customer.products.show', $p, absolute: true),
                        'lastmod' => $p->updated_at,
                    ];
                }
            });

        Category::query()
            ->active()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->chunkById(200, function ($categories) use (&$items): void {
                foreach ($categories as $c) {
                    $items[] = [
                        'loc' => route('customer.categories.show', $c, absolute: true),
                        'lastmod' => $c->updated_at,
                    ];
                }
            });

        Brand::query()
            ->active()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->chunkById(200, function ($brands) use (&$items): void {
                foreach ($brands as $b) {
                    $items[] = [
                        'loc' => route('customer.brands.show', $b, absolute: true),
                        'lastmod' => $b->updated_at,
                    ];
                }
            });

        Page::query()
            ->published()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->chunkById(200, function ($pages) use (&$items): void {
                foreach ($pages as $page) {
                    $items[] = [
                        'loc' => route('customer.pages.show', $page, absolute: true),
                        'lastmod' => $page->updated_at,
                    ];
                }
            });

        return $items;
    }
}
