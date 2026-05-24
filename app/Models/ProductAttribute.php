<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    protected $table = 'product_attributes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_filterable',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<ProductAttributeValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }

    /**
     * @return HasMany<ProductAttributeAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ProductAttributeAssignment::class, 'attribute_id');
    }

    /**
     * @return HasMany<ProductVariantValue, $this>
     */
    public function variantValues(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class, 'attribute_id');
    }
}
