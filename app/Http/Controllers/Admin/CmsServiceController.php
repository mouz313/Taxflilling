<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsServiceController extends Controller
{
    /**
     * Display a listing of all business services for CMS management.
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search');

        $query = BusinessServiceItem::query()->orderBy('sort_order')->orderBy('id');

        if ($category !== 'all' && !empty($category)) {
            $query->where('category', $category);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('heading', 'like', "%{$search}%")
                  ->orWhere('urdu', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        $services = $query->paginate(20)->withQueryString();
        $categories = BusinessServiceItem::getCategories();
        $totalCount = BusinessServiceItem::count();
        $activeCount = BusinessServiceItem::where('is_active', true)->count();

        return view('admin.cms.services.index', compact(
            'services',
            'categories',
            'category',
            'search',
            'totalCount',
            'activeCount'
        ));
    }

    /**
     * Show the form for creating a new business service.
     */
    public function create()
    {
        $categories = BusinessServiceItem::getCategories();
        $serviceCategories = \App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();
        $service = new BusinessServiceItem([
            'currency' => 'Rs.',
            'turnaround' => '1 - 2 Working Days',
            'icon' => '💼',
            'is_active' => true,
            'popular' => false,
            'requirements' => [],
            'form_fields' => [],
        ]);

        return view('admin.cms.services.form', compact('service', 'categories', 'serviceCategories'));
    }

    /**
     * Store a newly created business service in storage.
     */
    public function store(Request $request)
    {
        $categories = BusinessServiceItem::getCategories();

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:business_services,code'],
            'category' => ['required', 'string'],
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'heading' => ['required', 'string', 'max:255'],
            'urdu' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:10'],
            'fee' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'turnaround' => ['nullable', 'string', 'max:100'],
            'popular' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'desc' => ['nullable', 'string'],
            'requirements' => ['nullable', 'array'],
            'requirements_text' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $code = !empty($validated['code'])
            ? Str::slug($validated['code'], '_')
            : Str::slug($validated['heading'], '_');

        // Ensure unique code
        $originalCode = $code;
        $counter = 1;
        while (BusinessServiceItem::where('code', $code)->exists()) {
            $code = $originalCode . '_' . $counter++;
        }

        // Process requirements: prefer structured array, fallback to text lines
        $requirements = [];
        if (!empty($request->requirements) && is_array($request->requirements)) {
            foreach ($request->requirements as $item) {
                if (!empty($item['label'])) {
                    $requirements[] = [
                        'label' => trim($item['label']),
                        'type' => $item['type'] ?? 'file', // file, text, textarea, number
                        'file_types' => !empty($item['file_types']) ? (array) $item['file_types'] : ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => !empty($item['required']),
                    ];
                }
            }
        } elseif (!empty($request->requirements_text)) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $request->requirements_text))));
            foreach ($lines as $line) {
                $isText = preg_match('/phone|email|name|number|address|id/i', $line);
                $requirements[] = [
                    'label' => $line,
                    'type' => $isText ? 'text' : 'file',
                    'file_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
                    'required' => true,
                ];
            }
        }

        $categoryModel = !empty($validated['service_category_id']) 
            ? \App\Models\ServiceCategory::find($validated['service_category_id'])
            : \App\Models\ServiceCategory::where('slug', $validated['category'])->first();

        $categoryKey = $categoryModel ? $categoryModel->slug : $validated['category'];
        $categoryName = $categoryModel ? $categoryModel->name : ($categories[$validated['category']]['name'] ?? ucfirst($validated['category']));

        BusinessServiceItem::create([
            'code' => $code,
            'service_category_id' => $categoryModel?->id,
            'category' => $categoryKey,
            'category_name' => $categoryName,
            'heading' => $validated['heading'],
            'urdu' => $validated['urdu'] ?? null,
            'icon' => $validated['icon'] ?: ($categoryModel?->icon ?: '💼'),
            'fee' => $validated['fee'],
            'currency' => $validated['currency'] ?: 'Rs.',
            'turnaround' => $validated['turnaround'] ?: '1 - 2 Working Days',
            'popular' => $request->boolean('popular'),
            'is_active' => $request->boolean('is_active', true),
            'desc' => $validated['desc'] ?? null,
            'requirements' => $requirements,
            'form_fields' => [],
            'sort_order' => $validated['sort_order'] ?? (BusinessServiceItem::max('sort_order') + 1),
        ]);

        return redirect()->route('admin.cms.services.index')->with('status', "Service '{$validated['heading']}' created successfully!");
    }

    /**
     * Show the form for editing the specified business service.
     */
    public function edit(BusinessServiceItem $service)
    {
        $categories = BusinessServiceItem::getCategories();
        $serviceCategories = \App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.cms.services.form', compact('service', 'categories', 'serviceCategories'));
    }

    /**
     * Update the specified business service in storage.
     */
    public function update(Request $request, BusinessServiceItem $service)
    {
        $categories = BusinessServiceItem::getCategories();

        $validated = $request->validate([
            'heading' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'urdu' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:10'],
            'fee' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'turnaround' => ['nullable', 'string', 'max:100'],
            'popular' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'desc' => ['nullable', 'string'],
            'requirements' => ['nullable', 'array'],
            'requirements_text' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Process requirements
        $requirements = [];
        if (!empty($request->requirements) && is_array($request->requirements)) {
            foreach ($request->requirements as $item) {
                if (!empty($item['label'])) {
                    $requirements[] = [
                        'label' => trim($item['label']),
                        'type' => $item['type'] ?? 'file',
                        'file_types' => !empty($item['file_types']) ? (array) $item['file_types'] : ['jpg', 'jpeg', 'png', 'pdf'],
                        'required' => !empty($item['required']),
                    ];
                }
            }
        } elseif (!empty($request->requirements_text)) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $request->requirements_text))));
            foreach ($lines as $line) {
                $isText = preg_match('/phone|email|name|number|address|id/i', $line);
                $requirements[] = [
                    'label' => $line,
                    'type' => $isText ? 'text' : 'file',
                    'file_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
                    'required' => true,
                ];
            }
        }

        $categoryModel = !empty($validated['service_category_id']) 
            ? \App\Models\ServiceCategory::find($validated['service_category_id'])
            : \App\Models\ServiceCategory::where('slug', $validated['category'])->first();

        $categoryKey = $categoryModel ? $categoryModel->slug : $validated['category'];
        $categoryName = $categoryModel ? $categoryModel->name : ($categories[$validated['category']]['name'] ?? ucfirst($validated['category']));

        $service->update([
            'service_category_id' => $categoryModel?->id ?? $service->service_category_id,
            'category' => $categoryKey,
            'category_name' => $categoryName,
            'heading' => $validated['heading'],
            'urdu' => $validated['urdu'] ?? null,
            'icon' => $validated['icon'] ?: ($categoryModel?->icon ?: '💼'),
            'fee' => $validated['fee'],
            'currency' => $validated['currency'] ?: 'Rs.',
            'turnaround' => $validated['turnaround'] ?: '1 - 2 Working Days',
            'popular' => $request->boolean('popular'),
            'is_active' => $request->boolean('is_active', true),
            'desc' => $validated['desc'] ?? null,
            'requirements' => $requirements,
            'sort_order' => $validated['sort_order'] ?? $service->sort_order,
        ]);

        return redirect()->route('admin.cms.services.index')->with('status', "Service '{$service->heading}' updated successfully!");
    }

    /**
     * Toggle active status.
     */
    public function toggle(BusinessServiceItem $service)
    {
        $service->update([
            'is_active' => !$service->is_active,
        ]);

        $statusStr = $service->is_active ? 'Activated' : 'Deactivated';
        return back()->with('status', "Service '{$service->heading}' has been {$statusStr}!");
    }

    /**
     * Remove the specified business service from storage.
     */
    public function destroy(BusinessServiceItem $service)
    {
        $title = $service->heading;
        $service->delete();

        return redirect()->route('admin.cms.services.index')->with('status', "Service '{$title}' removed successfully!");
    }
}
