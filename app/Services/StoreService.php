<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StoreService
{
    public function currentStore(): Store
    {
        $store = Store::query()->orderBy('id')->first();
        if ($store === null) {
            throw (new ModelNotFoundException)->setModel(Store::class);
        }

        return $store;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateStore(array $data): Store
    {
        $store = $this->currentStore();
        $store->fill($data);
        $store->save();

        return $store->fresh();
    }

    public function defaultCurrencyCode(): string
    {
        try {
            $code = (string) $this->currentStore()->currency_code;
            if ($code !== '') {
                return $code;
            }
        } catch (\Throwable) {
            // ignore
        }

        $fromSettings = app(SettingService::class)->get('store.currency');
        if (is_string($fromSettings) && $fromSettings !== '') {
            return $fromSettings;
        }

        return (string) config('open_ecommerce_laravel.store.currency_code', 'LKR');
    }
}
