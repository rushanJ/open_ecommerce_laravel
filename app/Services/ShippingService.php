<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Support\Collection;

class ShippingService
{
    /**
     * Address keys: country_code, province?, district?, city?
     *
     * @param  array<string, string|null>  $shippingAddress
     * @return Collection<int, ShippingZone>
     */
    public function findMatchingZones(array $shippingAddress): Collection
    {
        $country = strtoupper((string) ($shippingAddress['country_code'] ?? 'LK'));

        $candidates = ShippingZone::query()
            ->active()
            ->whereRaw('upper(country_code) = ?', [$country])
            ->get()
            ->filter(fn (ShippingZone $z) => $this->zoneMatches($z, $shippingAddress));

        return $candidates->sortByDesc(fn (ShippingZone $z) => $this->zoneSpecificity($z))->values();
    }

    /**
     * @param  array<string, string|null>  $shippingAddress
     * @return Collection<int, array{rate: ShippingRate, amount: float, label: string}>
     */
    public function getAvailableRates(array $shippingAddress, Cart $cart): Collection
    {
        $zones = $this->findMatchingZones($shippingAddress);
        $results = collect();
        $seenMethodIds = [];

        foreach ($zones as $zone) {
            $rates = ShippingRate::query()
                ->active()
                ->where('shipping_zone_id', $zone->getKey())
                ->with(['method', 'zone'])
                ->get()
                ->filter(fn (ShippingRate $rate) => $rate->method !== null
                    && $rate->method->status === 'active'
                    && $this->rateMatchesCart($rate, $cart));

            foreach ($rates as $rate) {
                $methodId = $rate->shipping_method_id;
                if (isset($seenMethodIds[$methodId])) {
                    continue;
                }
                $seenMethodIds[$methodId] = true;
                $amount = $this->calculateRate($rate, $cart);
                $label = $rate->method->name.' · '.$zone->name;

                $results->push([
                    'rate' => $rate,
                    'amount' => $amount,
                    'label' => $label,
                ]);
            }
        }

        return $results->values();
    }

    public function calculateRate(ShippingRate $rate, Cart $cart): float
    {
        $method = $rate->method;
        if ($method === null) {
            return (float) $rate->rate;
        }

        return match ($method->type) {
            'free_shipping' => 0.0,
            default => (float) $rate->rate,
        };
    }

    protected function zoneMatches(ShippingZone $zone, array $addr): bool
    {
        if (($zone->province ?? '') !== '' && strcasecmp((string) $zone->province, (string) ($addr['province'] ?? '')) !== 0) {
            return false;
        }
        if (($zone->district ?? '') !== '' && strcasecmp((string) $zone->district, (string) ($addr['district'] ?? '')) !== 0) {
            return false;
        }
        if (($zone->city ?? '') !== '' && strcasecmp((string) $zone->city, (string) ($addr['city'] ?? '')) !== 0) {
            return false;
        }

        return true;
    }

    protected function zoneSpecificity(ShippingZone $zone): int
    {
        $s = 1;
        if (($zone->province ?? '') !== '') {
            $s += 2;
        }
        if (($zone->district ?? '') !== '') {
            $s += 4;
        }
        if (($zone->city ?? '') !== '') {
            $s += 8;
        }

        return $s;
    }

    protected function rateMatchesCart(ShippingRate $rate, Cart $cart): bool
    {
        $subtotal = (float) $cart->items()->sum('subtotal');
        $weight = $this->cartTotalWeight($cart);

        if ($rate->min_order_amount !== null && $subtotal < (float) $rate->min_order_amount) {
            return false;
        }
        if ($rate->max_order_amount !== null && $subtotal > (float) $rate->max_order_amount) {
            return false;
        }
        if ($rate->min_weight !== null && $weight < (float) $rate->min_weight) {
            return false;
        }
        if ($rate->max_weight !== null && $weight > (float) $rate->max_weight) {
            return false;
        }

        return true;
    }

    protected function cartTotalWeight(Cart $cart): float
    {
        $cart->loadMissing(['items.product', 'items.variant']);

        $total = 0.0;
        foreach ($cart->items as $item) {
            $product = $item->product;
            if ($product === null) {
                continue;
            }
            $variant = $item->variant;
            $unit = 0.0;
            if ($variant !== null && $variant->weight !== null && (float) $variant->weight > 0) {
                $unit = (float) $variant->weight;
            } else {
                $unit = (float) ($product->weight ?? 0);
            }
            $total += $unit * (float) $item->quantity;
        }

        return $total;
    }
}
