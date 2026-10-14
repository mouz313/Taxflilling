<x-layouts.app>
    <x-slot:header>
        CMS &bull; {{ $category->exists ? 'Edit Category' : 'Create Category' }}
    </x-slot:header>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <a href="{{ route('admin.cms.categories.index') }}" class="hover:text-blue-600 font-semibold transition">&larr; Back to Categories</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-bold">{{ $category->exists ? 'Edit: ' . $category->name : 'New Category' }}</span>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-1">
                <strong class="font-bold block">Please fix the following:</strong>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $category->exists ? route('admin.cms.categories.update', $category) : route('admin.cms.categories.store') }}"
            class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-2xs space-y-5"
        >
            @csrf
            @if($category->exists)
                @method('PUT')
            @endif

            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    {{ $category->exists ? 'Update Category Details' : 'Add New Service Category' }}
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Define name, emoji icon, slug, and placement order.</p>
            </div>

            <!-- Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Category Name *</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $category->name) }}"
                    required
                    placeholder="e.g. NTN Registration or Sales Tax"
                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Slug -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">URL Slug (Optional)</label>
                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug', $category->slug) }}"
                        placeholder="e.g. ntn-registration (auto-generated)"
                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-mono"
                    >
                </div>

                <!-- Icon -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Emoji Icon *</label>
                    <input
                        type="text"
                        name="icon"
                        value="{{ old('icon', $category->icon ?: '📁') }}"
                        required
                        placeholder="e.g. 🆔, 💼, 🏢, 📜, 🇺🇸"
                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                    >
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Short Description</label>
                <textarea
                    name="description"
                    rows="3"
                    placeholder="Briefly describe what services belong in this category..."
                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                >{{ old('description', $category->description) }}</textarea>
            </div>

            <!-- Sort order & Active toggle -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Display Sort Order</label>
                    <input
                        type="number"
                        name="sort_order"
                        value="{{ old('sort_order', $category->sort_order ?: 1) }}"
                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-mono"
                    >
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 text-emerald-600 rounded-sm border-slate-300 focus:ring-emerald-500"
                        >
                        <span>✅ Visible & Active on Website</span>
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <a
                    href="{{ route('admin.cms.categories.index') }}"
                    class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer"
                >
                    {{ $category->exists ? 'Update Category' : 'Save Category' }} &rarr;
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
