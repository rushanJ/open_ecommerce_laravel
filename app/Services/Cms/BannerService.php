<?php

namespace App\Services\Cms;

use App\Models\Banner;
use Illuminate\Support\Collection;

class BannerService
{
    /**
     * @return Collection<int, Banner>
     */
    public function getActiveBanners(string $position, ?int $limit = null): Collection
    {
        $q = Banner::query()
            ->active()
            ->position($position)
            ->orderByDesc('id');

        if ($limit !== null) {
            $q->limit($limit);
        }

        return $q->get();
    }
}

