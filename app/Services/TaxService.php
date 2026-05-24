<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\TaxClass;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class TaxService
{
    /**
     * @param  array<string, mixed>  $address
     */
    public function calculateCartTax(Cart $cart, array $address = []): float
    {
        $cart->loadMissing(['items.product.taxClass', 'items.variant']);

        $tax = 0.0;
        foreach ($cart->items as $item) {
            $tax += $this->calculateItemTax($item, $address);
        }

        return $this->round($tax);
    }

    /**
     * Tax is calculated on item subtotal (before discount) in this stage.
     *
     * @param  array<string, mixed>  $address
     */
    public function calculateItemTax(CartItem $item, array $address = []): float
    {
        $product = $item->product;
        if ($product === null) {
            return 0.0;
        }

        $taxClass = $product->relationLoaded('taxClass') ? $product->taxClass : $product->taxClass()->first();
        if ($taxClass === null) {
            return 0.0;
        }

        $rates = $this->findApplicableRates($taxClass, $address);

        return $this->calculateTaxForAmount((float) $item->subtotal, $rates);
    }

    /**
     * @param  array<string, mixed>  $address
     * @return EloquentCollection<int, TaxRate>
     */
    public function findApplicableRates(?TaxClass $taxClass, array $address = []): EloquentCollection
    {
        if ($taxClass === null) {
            return new EloquentCollection();
        }

        $country = strtoupper((string) ($address['country_code'] ?? 'LK'));
        $province = $this->normalizeOptionalString($address['province'] ?? null);
        $district = $this->normalizeOptionalString($address['district'] ?? null);

        $query = TaxRate::query()
            ->active()
            ->where('tax_class_id', $taxClass->getKey())
            ->where('country_code', $country);

        // Wildcards (null) allowed. If address has a province/district, prefer exact matches but also allow null.
        $query->where(function ($q) use ($province): void {
            if ($province === null) {
                $q->whereNull('province');
            } else {
                $q->whereNull('province')->orWhere('province', $province);
            }
        });

        $query->where(function ($q) use ($district): void {
            if ($district === null) {
                $q->whereNull('district');
            } else {
                $q->whereNull('district')->orWhere('district', $district);
            }
        });

        /** @var EloquentCollection<int, TaxRate> $rates */
        $rates = $query->get();

        // Sort:
        // 1) More specific first: district > province > country
        // 2) Then by priority ascending (0 first)
        // 3) Then compound last within same specificity/priority (so non-compound sums first)
        return $rates
            ->sortBy(function (TaxRate $r) use ($province, $district): array {
                $specificity = 0;
                if ($district !== null && $r->district !== null && $r->district === $district) {
                    $specificity = 2;
                } elseif ($province !== null && $r->province !== null && $r->province === $province) {
                    $specificity = 1;
                } else {
                    $specificity = 0;
                }

                return [
                    -$specificity,
                    (int) $r->priority,
                    $r->is_compound ? 1 : 0,
                    (int) $r->getKey(),
                ];
            })
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, TaxRate>  $rates
     */
    public function calculateTaxForAmount(float $amount, Collection $rates): float
    {
        $amount = max(0.0, $amount);
        if ($amount <= 0 || $rates->isEmpty()) {
            return 0.0;
        }

        $tax = 0.0;
        foreach ($rates as $rate) {
            $percent = (float) $rate->rate;
            if ($percent <= 0) {
                continue;
            }

            $base = $rate->is_compound ? ($amount + $tax) : $amount;
            $tax += ($base * $percent / 100.0);
        }

        return $this->round($tax);
    }

    private function normalizeOptionalString(mixed $v): ?string
    {
        if (! is_string($v)) {
            return null;
        }
        $v = trim($v);

        return $v === '' ? null : $v;
    }

    private function round(float $v): float
    {
        return round($v, 4);
    }
}

