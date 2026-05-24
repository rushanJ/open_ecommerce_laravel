<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'store.name', 'value' => 'open_ecommerce_laravel', 'type' => 'string', 'group' => 'store', 'is_public' => true],
            ['key' => 'store.domain', 'value' => 'open-ecommerce-laravel.test', 'type' => 'string', 'group' => 'store', 'is_public' => false],
            ['key' => 'store.email', 'value' => 'admin@open-ecommerce-laravel.test', 'type' => 'string', 'group' => 'store', 'is_public' => false],
            ['key' => 'store.currency', 'value' => 'LKR', 'type' => 'string', 'group' => 'store', 'is_public' => true],
            ['key' => 'store.timezone', 'value' => 'Asia/Colombo', 'type' => 'string', 'group' => 'store', 'is_public' => false],
            ['key' => 'general.maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'general', 'is_public' => false],

            ['key' => 'seo.default_title', 'value' => 'open_ecommerce_laravel', 'type' => 'string', 'group' => 'seo', 'is_public' => true],
            ['key' => 'seo.default_description', 'value' => 'Open-source Laravel e-commerce platform', 'type' => 'string', 'group' => 'seo', 'is_public' => true],
            ['key' => 'seo.default_keywords', 'value' => 'ecommerce, laravel, open_ecommerce_laravel', 'type' => 'string', 'group' => 'seo', 'is_public' => true],

            ['key' => 'mail.from_name', 'value' => 'open_ecommerce_laravel', 'type' => 'string', 'group' => 'mail', 'is_public' => false],
            ['key' => 'mail.from_address', 'value' => 'admin@open-ecommerce-laravel.test', 'type' => 'string', 'group' => 'mail', 'is_public' => false],

            ['key' => 'payhere.enabled', 'value' => filter_var(env('PAYHERE_ENABLED', false), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'type' => 'boolean', 'group' => 'payhere', 'is_public' => false],
            ['key' => 'payhere.mode', 'value' => env('PAYHERE_MODE', 'sandbox'), 'type' => 'string', 'group' => 'payhere', 'is_public' => false],
        ];

        foreach ($defaults as $row) {
            SystemSetting::query()->firstOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'type' => $row['type'],
                    'group' => $row['group'],
                    'is_public' => $row['is_public'],
                ],
            );
        }

        SystemSetting::query()->firstOrCreate(
            ['key' => 'payhere.merchant_id'],
            [
                'value' => env('PAYHERE_MERCHANT_ID') ?: '',
                'type' => 'string',
                'group' => 'payhere',
                'is_public' => false,
            ],
        );

        SystemSetting::query()->firstOrCreate(
            ['key' => 'payhere.merchant_secret'],
            [
                'value' => env('PAYHERE_MERCHANT_SECRET') ?: '',
                'type' => 'string',
                'group' => 'payhere',
                'is_public' => false,
            ],
        );

        app(SettingService::class)->forgetCache();
    }
}
