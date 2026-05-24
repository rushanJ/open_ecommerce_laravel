<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SettingService;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

class SettingController extends ApiController
{
    public function __construct(
        protected StoreService $stores,
        protected SettingService $settings
    ) {}

    public function public(): JsonResponse
    {
        $storeBlock = [
            'name' => (string) config('open_ecommerce_laravel.store.name', config('app.name')),
            'currency_code' => (string) config('open_ecommerce_laravel.store.currency_code', 'LKR'),
            'logo_url' => null,
            'favicon_url' => null,
            'timezone' => null,
        ];

        try {
            $store = $this->stores->currentStore();
            $storeBlock = [
                'name' => $store->name,
                'currency_code' => $store->currency_code ?: $this->stores->defaultCurrencyCode(),
                'logo_url' => media_url($store->logo_path),
                'favicon_url' => media_url($store->favicon_path),
                'timezone' => $store->timezone,
            ];
        } catch (\Throwable) {
            // use defaults + settings below
        }

        $storeNameSetting = (string) $this->settings->get('store.name', '');
        if ($storeNameSetting !== '') {
            $storeBlock['name'] = $storeNameSetting;
        }

        return $this->success([
            'store' => $storeBlock,
            'seo' => [
                'default_title' => (string) $this->settings->get('seo.default_title', ''),
                'default_description' => (string) $this->settings->get('seo.default_description', ''),
                'default_keywords' => (string) $this->settings->get('seo.default_keywords', ''),
            ],
        ]);
    }
}
