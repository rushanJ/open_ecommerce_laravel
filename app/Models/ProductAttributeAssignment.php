<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeAssignment extends Model
{
    protected $table = 'product_attribute_assignments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
        'custom_value',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ProductAttribute, $this>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * @return BelongsTo<ProductAttributeValue, $this>
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeValue::class, 'attribute_value_id');
    }
}
