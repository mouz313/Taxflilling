<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display menu items management with drag & drop reordering.
     */
    public function index(Request $request)
    {
        $location = $request->get('location', 'header');

        $menuItems = MenuItem::where('location', $location)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $pages = Page::where('is_active', true)->orderBy('title')->get();
        $categories = ServiceCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.cms.menus.index', compact('menuItems', 'location', 'pages', 'categories'));
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => ['required', 'in:header,footer'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'target' => ['required', 'in:_self,_blank'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
        ]);

        $maxSort = MenuItem::where('location', $validated['location'])
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('sort_order') ?? 0;

        MenuItem::create([
            'location' => $validated['location'],
            'title' => $validated['title'],
            'url' => $validated['url'],
            'target' => $validated['target'],
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        return back()->with('status', "Menu item '{$validated['title']}' added successfully!");
    }

    /**
     * Update an existing menu item.
     */
    public function update(Request $request, MenuItem $menu)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'target' => ['required', 'in:_self,_blank'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $menu->update([
            'title' => $validated['title'],
            'url' => $validated['url'],
            'target' => $validated['target'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', "Menu item '{$menu->title}' updated!");
    }

    /**
     * Delete a menu item.
     */
    public function destroy(MenuItem $menu)
    {
        $title = $menu->title;
        // Delete children if any
        $menu->children()->delete();
        $menu->delete();

        return back()->with('status', "Menu item '{$title}' deleted!");
    }

    /**
     * Reorder menu items via drag & drop.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'exists:menu_items,id'],
            'order.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->order as $item) {
            MenuItem::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
