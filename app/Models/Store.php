<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'legal_name',
        'domain',
        'email',
        'phone',
        'logo_path',
        'favicon_path',
        'currency_code',
        'timezone',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'province',
        'postal_code',
        'country_code',
        'status',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
