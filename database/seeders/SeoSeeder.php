<?php

namespace Database\Seeders;

use App\Models\SeoRedirect;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    public function run(): void
    {
        SeoRedirect::query()->updateOrCreate(
            ['from_url' => '/home'],
            [
                'to_url' => '/',
                'status_code' => 301,
                'status' => 'active',
            ],
        );

        SeoRedirect::query()->updateOrCreate(
            ['from_url' => '/products'],
            [
                'to_url' => '/shop',
                'status_code' => 301,
                'status' => 'active',
            ],
        );
    }
}
