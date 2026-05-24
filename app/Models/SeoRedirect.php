<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_url',
        'to_url',
        'status_code',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
        ];
    }

    /**
     * @param  Builder<SeoRedirect>  $query
     * @return Builder<SeoRedirect>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
