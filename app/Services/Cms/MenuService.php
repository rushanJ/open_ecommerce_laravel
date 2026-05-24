<?php

namespace App\Services\Cms;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;

class MenuService
{
    public function getMenuByLocation(string $location): ?Menu
    {
        return Menu::query()
            ->where('location', $location)
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    /**
     * @return Collection<int, MenuItem>
     */
    public function getMenuItems(string $location): Collection
    {
        $menu = $this->getMenuByLocation($location);
        if ($menu === null) {
            return collect();
        }

        $items = $menu->activeItems()
            ->with('children')
            ->get();

        return $items;
    }

    public function resolveMenuItemUrl(MenuItem $item): string
    {
        return $item->resolveUrl();
    }
}

