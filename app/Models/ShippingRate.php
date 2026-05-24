<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'shipping_zone_id',
        'shipping_method_id',
        'min_order_amount',
        'max_order_amount',
        'min_weight',
        'max_weight',
        'rate',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_order_amount' => 'decimal:4',
            'max_order_amount' => 'decimal:4',
            'min_weight' => 'decimal:4',
            'max_weight' => 'decimal:4',
            'rate' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<ShippingZone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    /**
     * @return BelongsTo<ShippingMethod, $this>
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    /**
     * @param  Builder<ShippingRate>  $query
     * @return Builder<ShippingRate>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
