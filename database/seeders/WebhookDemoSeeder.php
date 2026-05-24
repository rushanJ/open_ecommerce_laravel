<?php

namespace Database\Seeders;

use App\Models\WebhookEndpoint;
use Illuminate\Database\Seeder;

/**
 * Manual-only seeder (not registered in DatabaseSeeder).
 * Run: php artisan db:seed --class=WebhookDemoSeeder
 */
class WebhookDemoSeeder extends Seeder
{
    public function run(): void
    {
        WebhookEndpoint::query()->firstOrCreate(
            ['name' => 'Demo Local Webhook'],
            [
                'url' => 'https://webhook.site/test-placeholder',
                'events' => ['order.created', 'payment.paid'],
                'secret' => null,
                'status' => 'inactive',
            ],
        );
    }
}
