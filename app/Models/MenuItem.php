<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'reference_id',
        'url',
        'target',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function resolveUrl(): string
    {
        if ($this->type === 'custom') {
            return $this->url ?? '#';
        }

        if ($this->type === 'page') {
            $page = Page::query()->whereKey($this->reference_id)->first();

            return $page ? route('customer.pages.show', $page) : '#';
        }

        if ($this->type === 'category') {
            $cat = Category::query()->whereKey($this->reference_id)->first();

            return $cat ? route('customer.categories.show', $cat) : '#';
        }

        if ($this->type === 'product') {
            $product = Product::query()->whereKey($this->reference_id)->first();

            return $product ? route('customer.products.show', $product) : '#';
        }

        return '#';
    }
}

