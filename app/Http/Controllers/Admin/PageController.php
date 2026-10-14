<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of CMS Pages.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Page::query()->orderBy('sort_order')->orderBy('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $pages = $query->paginate(20)->withQueryString();
        $totalCount = Page::count();
        $activeCount = Page::where('is_active', true)->count();

        return view('admin.cms.pages.index', compact('pages', 'search', 'totalCount', 'activeCount'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        $page = new Page([
            'is_active' => true,
            'show_in_header' => false,
            'show_in_footer' => true,
            'sort_order' => Page::max('sort_order') + 1,
        ]);

        return view('admin.cms.pages.form', compact('page'));
    }

    /**
     * Store a newly created page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:pages,slug'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'show_in_header' => ['nullable', 'boolean'],
            'show_in_footer' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (Page::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        Page::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'meta_title' => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? null,
            'show_in_header' => $request->boolean('show_in_header'),
            'show_in_footer' => $request->boolean('show_in_footer'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? (Page::max('sort_order') + 1),
        ]);

        return redirect()->route('admin.cms.pages.index')->with('status', "Page '{$validated['title']}' created successfully!");
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        return view('admin.cms.pages.form', compact('page'));
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug,' . $page->id],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'show_in_header' => ['nullable', 'boolean'],
            'show_in_footer' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $page->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'content' => $validated['content'],
            'meta_title' => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? null,
            'show_in_header' => $request->boolean('show_in_header'),
            'show_in_footer' => $request->boolean('show_in_footer'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? $page->sort_order,
        ]);

        return redirect()->route('admin.cms.pages.index')->with('status', "Page '{$page->title}' updated successfully!");
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Page $page)
    {
        $title = $page->title;
        $page->delete();

        return redirect()->route('admin.cms.pages.index')->with('status', "Page '{$title}' deleted successfully!");
    }

    /**
     * Quick toggle status.
     */
    public function toggle(Page $page)
    {
        $page->update(['is_active' => !$page->is_active]);

        return back()->with('status', "Page '{$page->title}' status updated!");
    }
}
