<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class CmsFaqController extends Controller
{
    /**
     * Display a listing of FAQs.
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search');

        $query = Faq::query()->orderBy('sort_order')->orderBy('id');

        if ($category !== 'all' && !empty($category)) {
            $query->where('category', $category);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('urdu_question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->paginate(20)->withQueryString();
        $categories = Faq::getCategories();
        $totalCount = Faq::count();
        $activeCount = Faq::where('is_active', true)->count();

        return view('admin.cms.faqs.index', compact(
            'faqs',
            'categories',
            'category',
            'search',
            'totalCount',
            'activeCount'
        ));
    }

    /**
     * Show form for creating a new FAQ.
     */
    public function create()
    {
        $categories = Faq::getCategories();
        $faq = new Faq([
            'category' => 'all',
            'is_active' => true,
            'sort_order' => (Faq::max('sort_order') + 1),
        ]);

        return view('admin.cms.faqs.form', compact('faq', 'categories'));
    }

    /**
     * Store newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
            'question' => ['required', 'string', 'max:500'],
            'urdu_question' => ['nullable', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Faq::create([
            'category' => $validated['category'],
            'question' => $validated['question'],
            'urdu_question' => $validated['urdu_question'] ?? null,
            'answer' => $validated['answer'],
            'sort_order' => $validated['sort_order'] ?? (Faq::max('sort_order') + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.cms.faqs.index')->with('status', 'FAQ created successfully!');
    }

    /**
     * Show form for editing FAQ.
     */
    public function edit(Faq $faq)
    {
        $categories = Faq::getCategories();
        return view('admin.cms.faqs.form', compact('faq', 'categories'));
    }

    /**
     * Update specified FAQ.
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
            'question' => ['required', 'string', 'max:500'],
            'urdu_question' => ['nullable', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $faq->update([
            'category' => $validated['category'],
            'question' => $validated['question'],
            'urdu_question' => $validated['urdu_question'] ?? null,
            'answer' => $validated['answer'],
            'sort_order' => $validated['sort_order'] ?? $faq->sort_order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.cms.faqs.index')->with('status', 'FAQ updated successfully!');
    }

    /**
     * Toggle active status.
     */
    public function toggle(Faq $faq)
    {
        $faq->update([
            'is_active' => !$faq->is_active,
        ]);

        $statusStr = $faq->is_active ? 'Activated' : 'Deactivated';
        return back()->with('status', "FAQ has been {$statusStr}!");
    }

    /**
     * Delete FAQ.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('admin.cms.faqs.index')->with('status', 'FAQ removed successfully!');
    }
}
