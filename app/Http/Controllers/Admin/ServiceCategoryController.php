<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    /**
     * Display listing of service categories.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = ServiceCategory::query()->withCount('services')->orderBy('sort_order')->orderBy('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->paginate(15)->withQueryString();
        $totalCount = ServiceCategory::count();
        $activeCount = ServiceCategory::where('is_active', true)->count();

        return view('admin.cms.categories.index', compact(
            'categories',
            'search',
            'totalCount',
            'activeCount'
        ));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $category = new ServiceCategory([
            'icon' => '📁',
            'is_active' => true,
            'sort_order' => ServiceCategory::max('sort_order') + 1,
        ]);

        return view('admin.cms.categories.form', compact('category'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:service_categories,slug'],
            'icon' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 1);

        ServiceCategory::create($validated);

        return redirect()->route('admin.cms.categories.index')
            ->with('success', 'Nayi service category kamiyabi se add ho gayi.');
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(ServiceCategory $category)
    {
        return view('admin.cms.categories.form', compact('category'));
    }

    /**
     * Update the category.
     */
    public function update(Request $request, ServiceCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:service_categories,slug,' . $category->id],
            'icon' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 1);

        $category->update($validated);

        return redirect()->route('admin.cms.categories.index')
            ->with('success', "Category '{$category->name}' kamiyabi se update ho gayi.");
    }

    /**
     * Remove the category.
     */
    public function destroy(ServiceCategory $category)
    {
        if ($category->services()->count() > 0) {
            return back()->with('error', "Is category mein services maujood hain. Pehle un services ki category tabdeel karein.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.cms.categories.index')
            ->with('success', "Category '{$name}' delete ho gayi.");
    }

    /**
     * Toggle category active status.
     */
    public function toggle(ServiceCategory $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        $status = $category->is_active ? 'Active' : 'Disabled';
        return back()->with('success', "Category '{$category->name}' ab {$status} hai.");
    }
}
