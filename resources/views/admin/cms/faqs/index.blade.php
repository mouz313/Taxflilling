<x-layouts.app>
    <x-slot:header>
        CMS &bull; Tax Knowledge Base &amp; FAQs
    </x-slot:header>

    <div class="space-y-6">
        <!-- Top Title Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div>
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">
                    <span>⚙️ Content Management System</span>
                    <span>&bull;</span>
                    <span>Knowledge Base</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Tax FAQs &amp; Help Desk Questions</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Yahan se aap website ke FAQs, tax rules, salaried slabs, aur filer benefits ke jawabat dynamically manage kar sakte hain.
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a
                    href="{{ route('faq') }}"
                    target="_blank"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition"
                >
                    <span>🌐 View Public FAQ</span>
                    <span class="text-[10px] font-mono text-slate-400">↗</span>
                </a>
                <a
                    href="{{ route('admin.cms.faqs.create') }}"
                    class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs"
                >
                    <span>➕ Add New FAQ</span>
                </a>
            </div>
        </div>

        <!-- Feedback Alert -->
        @if(session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center space-x-2">
                    <span>✅</span>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        <!-- Filter & Search Controls -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Category Tabs -->
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-semibold">
                <a
                    href="{{ route('admin.cms.faqs.index', array_merge(request()->query(), ['category' => 'all'])) }}"
                    class="px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ $category === 'all' ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    All Questions ({{ $totalCount }})
                </a>
                @foreach($categories as $catKey => $catLabel)
                    @if($catKey !== 'all')
                        <a
                            href="{{ route('admin.cms.faqs.index', array_merge(request()->query(), ['category' => $catKey])) }}"
                            class="px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ $category === $catKey ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                        >
                            {{ $catLabel }}
                        </a>
                    @endif
                @endforeach
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.cms.faqs.index') }}" class="flex items-center space-x-2 flex-shrink-0">
                @if($category !== 'all')
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search question, keyword..."
                        class="w-56 text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-300 focus:outline-hidden focus:border-blue-500"
                    >
                    <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
            </form>
        </div>

        <!-- FAQs Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 w-12 text-center">#</th>
                            <th class="py-3.5 px-4">Question &amp; Answer Preview</th>
                            <th class="py-3.5 px-4">Topic Category</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($faqs as $faq)
                            <tr class="hover:bg-slate-50/80 transition {{ !$faq->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                                <td class="py-3 px-4 text-center text-slate-400 font-bold tabular-nums">
                                    {{ $faq->sort_order }}
                                </td>

                                <td class="py-3 px-4 max-w-lg">
                                    <h4 class="font-bold text-slate-900 leading-snug">{{ $faq->question }}</h4>
                                    @if($faq->urdu_question)
                                        <p class="text-[11px] text-blue-600 font-medium mt-0.5">{{ $faq->urdu_question }}</p>
                                    @endif
                                    <div class="text-[11px] text-slate-500 line-clamp-2 mt-1">
                                        {{ strip_tags($faq->answer) }}
                                    </div>
                                </td>

                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $categories[$faq->category] ?? ucfirst($faq->category) }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <form method="POST" action="{{ route('admin.cms.faqs.toggle', $faq) }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            title="Click to toggle active status"
                                            class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold cursor-pointer transition {{ $faq->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full {{ $faq->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $faq->is_active ? 'Active' : 'Hidden' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a
                                            href="{{ route('admin.cms.faqs.edit', $faq) }}"
                                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 font-semibold transition"
                                            title="Edit FAQ"
                                        >
                                            ✏️ Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.cms.faqs.destroy', $faq) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this FAQ?');"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition"
                                                title="Delete FAQ"
                                            >
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <p class="text-3xl mb-2">❓</p>
                                    <p class="text-sm font-semibold text-slate-700">Koi FAQ nahi mila</p>
                                    <p class="text-xs text-slate-400 mt-1">Naya sawal shamil karne ke liye upar 'Add New FAQ' button dabayein.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($faqs->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $faqs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
