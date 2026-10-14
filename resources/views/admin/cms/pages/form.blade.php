<x-layouts.app>
    <x-slot:header>
        CMS &bull; {{ $page->exists ? 'Edit Page' : 'Create New Page' }}
    </x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <a href="{{ route('admin.cms.pages.index') }}" class="hover:text-blue-600 font-semibold transition">&larr; Back to Pages</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-bold">{{ $page->exists ? 'Edit: ' . $page->title : 'Create New Page' }}</span>
            </div>
            @if($page->exists)
                <a
                    href="{{ route('pages.show', $page->slug) }}"
                    target="_blank"
                    class="text-xs text-blue-600 hover:underline flex items-center space-x-1"
                >
                    <span>Preview Page</span>
                    <span class="text-[10px] font-mono">↗</span>
                </a>
            @endif
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-1">
                <strong class="font-bold block">Please fix the following errors:</strong>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $page->exists ? route('admin.cms.pages.update', $page) : route('admin.cms.pages.store') }}"
            class="space-y-6"
        >
            @csrf
            @if($page->exists)
                @method('PUT')
            @endif

            <!-- Main Content Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Page Content & URL</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Page title, custom URL slug, and HTML/text body content.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Page Title *</label>
                        <input
                            type="text"
                            name="title"
                            value="{{ old('title', $page->title) }}"
                            required
                            placeholder="e.g. About Our Firm, Terms of Service"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-semibold"
                        >
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">URL Slug</label>
                        <input
                            type="text"
                            name="slug"
                            value="{{ old('slug', $page->slug) }}"
                            placeholder="e.g. about-us, terms, privacy-policy"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-mono"
                        >
                        <span class="text-[10px] text-slate-400 mt-1 block">Leave blank for automatic generation from title.</span>
                    </div>
                </div>

                <!-- Content Area -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Page Content (HTML or Markdown) *</label>
                    <textarea
                        name="content"
                        rows="12"
                        required
                        placeholder="Write the full page content here. HTML tags (<h2>, <p>, <ul>, <strong>) are supported."
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-mono"
                    >{{ old('content', $page->content) }}</textarea>
                </div>
            </div>

            <!-- Placements & SEO Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Navigation Placements & SEO</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Where this page appears in menus, sort order, and search engine metadata.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Sort Order</label>
                        <input
                            type="number"
                            name="sort_order"
                            value="{{ old('sort_order', $page->sort_order ?: 1) }}"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 tabular-nums"
                        >
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input
                                type="checkbox"
                                name="show_in_header"
                                value="1"
                                {{ old('show_in_header', $page->show_in_header) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500"
                            >
                            <span>Show in Header Navigation</span>
                        </label>
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input
                                type="checkbox"
                                name="show_in_footer"
                                value="1"
                                {{ old('show_in_footer', $page->show_in_footer ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 text-purple-600 rounded-sm border-slate-300 focus:ring-purple-500"
                            >
                            <span>Show in Footer Navigation</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 text-emerald-600 rounded-sm border-slate-300 focus:ring-emerald-500"
                        >
                        <span>✅ Published & Active</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SEO Meta Title</label>
                        <input
                            type="text"
                            name="meta_title"
                            value="{{ old('meta_title', $page->meta_title) }}"
                            placeholder="Optional custom title for search engines"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SEO Meta Description</label>
                        <input
                            type="text"
                            name="meta_description"
                            value="{{ old('meta_description', $page->meta_description) }}"
                            placeholder="Brief summary for Google search snippet"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <a
                    href="{{ route('admin.cms.pages.index') }}"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                >
                    {{ $page->exists ? 'Save Changes' : 'Publish Page' }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
