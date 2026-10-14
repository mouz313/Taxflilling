<x-layouts.app>
    <x-slot:header>
        CMS &bull; Website Pages (About Us, Terms, Privacy Policy)
    </x-slot:header>

    <div class="space-y-6">
        <!-- Top Stats & Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Website Pages Management</h2>
                <p class="text-xs text-slate-500 mt-1">Manage static and dynamic pages with header/footer placement and SEO meta tags.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a
                    href="{{ route('admin.cms.pages.create') }}"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>+ Add New Page</span>
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-4 text-xs font-semibold text-slate-500">
                <span>Total Pages: <strong class="text-slate-900 font-bold">{{ $totalCount }}</strong></span>
                <span>&bull;</span>
                <span>Active: <strong class="text-emerald-600 font-bold">{{ $activeCount }}</strong></span>
            </div>

            <form method="GET" action="{{ route('admin.cms.pages.index') }}" class="flex items-center space-x-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by title, slug..."
                    class="text-xs px-3 py-2 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 w-64"
                >
                <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
                @if($search)
                    <a href="{{ route('admin.cms.pages.index') }}" class="text-xs text-rose-500 hover:underline">Clear</a>
                @endif
            </form>
        </div>

        <!-- Pages Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center">Order</th>
                            <th class="py-3.5 px-4">Page Title & Slug</th>
                            <th class="py-3.5 px-4 text-center">Placements</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pages as $page)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-400">
                                    {{ $page->sort_order }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-sm">{{ $page->title }}</div>
                                    <div class="text-[11px] font-mono text-blue-600 mt-0.5 flex items-center space-x-1">
                                        <span>/p/{{ $page->slug }}</span>
                                        <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="hover:underline text-slate-400 hover:text-blue-600">↗</a>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center space-x-1">
                                    @if($page->show_in_header)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            Header
                                        </span>
                                    @endif
                                    @if($page->show_in_footer)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            Footer
                                        </span>
                                    @endif
                                    @if(!$page->show_in_header && !$page->show_in_footer)
                                        <span class="text-[11px] text-slate-400 italic">Direct Link</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('admin.cms.pages.toggle', $page) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $page->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $page->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $page->is_active ? 'Active' : 'Disabled' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center space-x-2">
                                        <a
                                            href="{{ route('admin.cms.pages.edit', $page) }}"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 hover:bg-blue-50 transition border border-transparent hover:border-blue-200"
                                        >
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.cms.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition border border-transparent hover:border-rose-200"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    No pages found. Click "+ Add New Page" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pages->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $pages->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
