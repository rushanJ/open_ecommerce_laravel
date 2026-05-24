<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreMenuRequest;
use App\Http\Requests\Admin\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends BaseAdminController
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $location = (string) $request->get('location', '');

        $menus = Menu::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where('name', 'like', $like);
            })
            ->when($location !== '', fn ($query) => $query->where('location', $location))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.menus.index', [
            'menus' => $menus,
            'filters' => compact('q', 'location'),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.create', [
            'menu' => new Menu(),
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $menu = Menu::query()->create($request->validated());
        $this->logAdminActivity('content', 'create_menu', $menu, [], $menu->only(['name', 'location', 'status']));

        return redirect()->route('admin.menus.edit', $menu)->with('success', __('admin.menu_created'));
    }

    public function show(Menu $menu): View
    {
        $menu->load(['activeItems.children']);

        return view('admin.menus.show', [
            'menu' => $menu,
        ]);
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', [
            'menu' => $menu,
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $before = $menu->only(['name', 'location', 'status']);
        $menu->fill($request->validated())->save();
        $updated = $menu->fresh();
        $this->logAdminActivity('content', 'update_menu', $updated, $before, $updated?->only(['name', 'location', 'status']) ?? []);

        return redirect()->route('admin.menus.edit', $menu)->with('success', __('admin.menu_updated'));
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        if ($menu->items()->exists()) {
            return redirect()->route('admin.menus.index')->withErrors(['menu' => __('admin.menu_has_items')]);
        }

        $snapshot = $menu->only(['id', 'name', 'location']);
        $menu->delete();
        $this->logAdminActivity('content', 'delete_menu', null, $snapshot, []);

        return redirect()->route('admin.menus.index')->with('success', __('admin.menu_deleted'));
    }
}

