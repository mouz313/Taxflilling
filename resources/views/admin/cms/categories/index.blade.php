<x-layouts.app>
    <x-slot:header>
        CMS &bull; Service Categories Management
    </x-slot:header>

    <div class="space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Service Categories</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                        {{ $activeCount }} Active / {{ $totalCount }} Total
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Manage service groups (e.g. NTN Registration, Sales Tax, Company Registration, Intellectual Property).
                </p>
            </div>

            <div class="flex items-center space-x-3">
                <a
                    href="{{ route('admin.cms.services.index') }}"
                    class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 transition"
                >
                    &larr; View Services
                </a>
                <a
                    href="{{ route('admin.cms.categories.create') }}"
                    class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition flex items-center space-x-1.5"
                >
                    <span>+ Add New Category</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.cms.categories.index') }}" class="flex-1 max-w-md">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search category by name or slug..."
                        class="w-full text-xs pl-9 pr-4 py-2 rounded-lg border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
                </div>
            </form>
            @if(!empty($search))
                <a href="{{ route('admin.cms.categories.index') }}" class="text-xs text-rose-600 hover:underline">Clear Search</a>
            @endif
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 font-bold">Category</th>
                            <th class="py-3 px-4 font-bold">Slug</th>
                            <th class="py-3 px-4 font-bold">Services Count</th>
                            <th class="py-3 px-4 font-bold">Sort Order</th>
                            <th class="py-3 px-4 font-bold">Status</th>
                            <th class="py-3 px-4 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4 font-semibold text-slate-900">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-xl p-1.5 rounded-lg bg-slate-100">{{ $cat->icon }}</span>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $cat->name }}</div>
                                            @if($cat->description)
                                                <div class="text-[11px] text-slate-400 line-clamp-1">{{ $cat->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                                    {{ $cat->slug }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-700">
                                        {{ $cat->services_count }} {{ Str::plural('service', $cat->services_count) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-700">
                                    {{ $cat->sort_order }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <form method="POST" action="{{ route('admin.cms.categories.toggle', $cat) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold transition cursor-pointer {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                            title="Click to toggle status"
                                        >
                                            <span>{{ $cat->is_active ? '● Active' : '○ Disabled' }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a
                                        href="{{ route('admin.cms.categories.edit', $cat) }}"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-700 font-bold transition"
                                    >
                                        Edit
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.cms.categories.destroy', $cat) }}"
                                        class="inline"
                                        onsubmit="return confirm('Kya aap waqai is category ko delete karna chahte hain?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="px-2 py-1 rounded-lg hover:bg-rose-50 text-rose-600 font-bold transition cursor-pointer"
                                            title="Delete Category"
                                        >
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-slate-400">
                                    Koi category nahi mili. <a href="{{ route('admin.cms.categories.create') }}" class="text-blue-600 font-bold hover:underline">+ Add Category</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
