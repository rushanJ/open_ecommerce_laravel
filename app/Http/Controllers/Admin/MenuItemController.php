<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreMenuItemRequest;
use App\Http\Requests\Admin\UpdateMenuItemRequest;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuItemController extends BaseAdminController
{
    public function create(Menu $menu): View
    {
        return view('admin.menus.items.create', [
            'menu' => $menu,
            'menuItem' => new MenuItem(['menu_id' => $menu->getKey(), 'target' => 'self', 'sort_order' => 0]),
            'pages' => Page::query()->orderBy('title')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'products' => Product::query()->forStorefront()->orderBy('name')->limit(200)->get(),
            'parents' => MenuItem::query()->where('menu_id', $menu->getKey())->whereNull('parent_id')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $item = DB::transaction(function () use ($data): MenuItem {
            return MenuItem::query()->create([
                'menu_id' => $data['menu_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'title' => $data['title'],
                'type' => $data['type'],
                'reference_id' => $data['reference_id'] ?? null,
                'url' => $data['url'] ?? null,
                'target' => $data['target'],
                'sort_order' => $data['sort_order'] ?? 0,
            ]);
        });

        $this->logAdminActivity('content', 'create_menu_item', $item, [], $item->only(['title', 'type', 'menu_id']));

        return redirect()->route('admin.menus.show', $item->menu_id)->with('success', __('admin.menu_item_created'));
    }

    public function edit(MenuItem $menuItem): View
    {
        $menu = $menuItem->menu;

        return view('admin.menus.items.edit', [
            'menu' => $menu,
            'menuItem' => $menuItem,
            'pages' => Page::query()->orderBy('title')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'products' => Product::query()->forStorefront()->orderBy('name')->limit(200)->get(),
            'parents' => MenuItem::query()
                ->where('menu_id', $menuItem->menu_id)
                ->whereNull('parent_id')
                ->whereKeyNot($menuItem->getKey())
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->validated();

        $before = $menuItem->only(['title', 'type', 'menu_id']);
        DB::transaction(function () use ($menuItem, $data): void {
            $menuItem->fill([
                'menu_id' => $data['menu_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'title' => $data['title'],
                'type' => $data['type'],
                'reference_id' => $data['reference_id'] ?? null,
                'url' => $data['url'] ?? null,
                'target' => $data['target'],
                'sort_order' => $data['sort_order'] ?? 0,
            ])->save();
        });
        $updated = $menuItem->fresh();
        $this->logAdminActivity('content', 'update_menu_item', $updated, $before, $updated?->only(['title', 'type', 'menu_id']) ?? []);

        return redirect()->route('admin.menus.show', $menuItem->menu_id)->with('success', __('admin.menu_item_updated'));
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->children()->exists()) {
            return back()->withErrors(['menu_item' => __('admin.menu_item_has_children')]);
        }

        $menuId = $menuItem->menu_id;
        $snapshot = $menuItem->only(['id', 'title', 'menu_id']);
        $menuItem->delete();
        $this->logAdminActivity('content', 'delete_menu_item', null, $snapshot, []);

        return redirect()->route('admin.menus.show', $menuId)->with('success', __('admin.menu_item_deleted'));
    }
}

