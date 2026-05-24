<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $classes = [
                [
                    'slug' => 'standard',
                    'name' => 'Standard',
                    'description' => 'Default tax class.',
                ],
                [
                    'slug' => 'zero-rated',
                    'name' => 'Zero Rated',
                    'description' => 'Tax class for 0% taxable goods.',
                ],
                [
                    'slug' => 'tax-exempt',
                    'name' => 'Tax Exempt',
                    'description' => 'No tax applied.',
                ],
            ];

            foreach ($classes as $c) {
                $payload = [
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'updated_at' => $now,
                ];

                if (DB::table('tax_classes')->where('slug', $c['slug'])->exists()) {
                    DB::table('tax_classes')->where('slug', $c['slug'])->update($payload);
                } else {
                    DB::table('tax_classes')->insert(array_merge($payload, [
                        'slug' => $c['slug'],
                        'created_at' => $now,
                    ]));
                }
            }

            $standardId = (int) DB::table('tax_classes')->where('slug', 'standard')->value('id');
            $zeroId = (int) DB::table('tax_classes')->where('slug', 'zero-rated')->value('id');
            $exemptId = (int) DB::table('tax_classes')->where('slug', 'tax-exempt')->value('id');

            $rates = [
                [
                    'tax_class_id' => $standardId,
                    'name' => 'Sri Lanka Standard Tax',
                    'country_code' => 'LK',
                    'province' => null,
                    'district' => null,
                    'rate' => '0.0000',
                    'priority' => 0,
                    'is_compound' => false,
                    'status' => 'active',
                ],
                [
                    'tax_class_id' => $standardId,
                    'name' => 'Sri Lanka VAT Demo',
                    'country_code' => 'LK',
                    'province' => null,
                    'district' => null,
                    'rate' => '18.0000',
                    'priority' => 0,
                    'is_compound' => false,
                    'status' => 'inactive',
                ],
                [
                    'tax_class_id' => $zeroId,
                    'name' => 'Zero Rated',
                    'country_code' => 'LK',
                    'province' => null,
                    'district' => null,
                    'rate' => '0.0000',
                    'priority' => 0,
                    'is_compound' => false,
                    'status' => 'active',
                ],
                [
                    'tax_class_id' => $exemptId,
                    'name' => 'Tax Exempt',
                    'country_code' => 'LK',
                    'province' => null,
                    'district' => null,
                    'rate' => '0.0000',
                    'priority' => 0,
                    'is_compound' => false,
                    'status' => 'active',
                ],
            ];

            foreach ($rates as $r) {
                $key = [
                    'tax_class_id' => $r['tax_class_id'],
                    'name' => $r['name'],
                    'country_code' => $r['country_code'],
                ];

                $payload = array_merge($r, [
                    'updated_at' => $now,
                ]);

                $exists = DB::table('tax_rates')
                    ->where($key)
                    ->exists();

                if ($exists) {
                    DB::table('tax_rates')->where($key)->update($payload);
                } else {
                    DB::table('tax_rates')->insert(array_merge($payload, [
                        'created_at' => $now,
                    ]));
                }
            }
        });
    }
}
