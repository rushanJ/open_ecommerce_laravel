<?php

namespace App\Http\Resources\Api\V1\Concerns;

trait ResolvesProductPricing
{
    /**
     * @param  \App\Models\Product|\App\Models\ProductVariant  $model
     * @return array{price: string, regular_price: string, sale_price: ?string}
     */
    protected function variantOrProductPrices(object $model): array
    {
        $regular = (string) $model->regular_price;
        $sale = $model->sale_price !== null ? (string) $model->sale_price : null;
        $price = $sale !== null && $sale !== '' && (float) $sale < (float) $regular ? $sale : $regular;

        return [
            'price' => $price,
            'regular_price' => $regular,
            'sale_price' => $sale !== null && $sale !== '' && (float) $sale < (float) $regular ? $sale : null,
        ];
    }
}
