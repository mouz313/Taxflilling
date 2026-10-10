<x-layouts.app>
    <x-slot:header>
        CMS &bull; Services Marketplace Manager
    </x-slot:header>

    <div class="space-y-6">
        <!-- Top Title Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div>
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">
                    <span>⚙️ Content Management System</span>
                    <span>&bull;</span>
                    <span>Public Services</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Business Services Catalog</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Yahan se aap tamam business services ka title, fees, turnaround time, aur requirements dynamically add aur edit kar sakte hain.
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a
                    href="{{ route('services.index') }}"
                    target="_blank"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition"
                >
                    <span>🌐 Live Preview</span>
                    <span class="text-[10px] font-mono text-slate-400">↗</span>
                </a>
                <a
                    href="{{ route('admin.cms.services.create') }}"
                    class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs"
                >
                    <span>➕ Add New Service</span>
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

        <!-- KPI Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Services</p>
                <p class="text-2xl font-black text-slate-900 mt-1 tabular-nums">{{ $totalCount }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">In master catalog</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Live &amp; Active</p>
                <p class="text-2xl font-black text-emerald-700 mt-1 tabular-nums">{{ $activeCount }}</p>
                <p class="text-[10px] text-emerald-600/80 mt-0.5">Visible to public clients</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Inactive / Hidden</p>
                <p class="text-2xl font-black text-amber-700 mt-1 tabular-nums">{{ $totalCount - $activeCount }}</p>
                <p class="text-[10px] text-amber-600/80 mt-0.5">Draft or temporarily disabled</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Categories</p>
                <p class="text-2xl font-black text-blue-700 mt-1 tabular-nums">{{ count($categories) }}</p>
                <p class="text-[10px] text-blue-600/80 mt-0.5">Active business sectors</p>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Category Tabs -->
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-semibold">
                <a
                    href="{{ route('admin.cms.services.index', array_merge(request()->query(), ['category' => 'all'])) }}"
                    class="px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ $category === 'all' ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    All Services ({{ $totalCount }})
                </a>
                @foreach($categories as $catKey => $catData)
                    <a
                        href="{{ route('admin.cms.services.index', array_merge(request()->query(), ['category' => $catKey])) }}"
                        class="px-3 py-1.5 rounded-lg whitespace-nowrap transition flex items-center space-x-1 {{ $category === $catKey ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        <span>{{ $catData['icon'] }}</span>
                        <span>{{ $catData['name'] }}</span>
                    </a>
                @endforeach
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.cms.services.index') }}" class="flex items-center space-x-2 flex-shrink-0">
                @if($category !== 'all')
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search service name, code..."
                        class="w-56 text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                    <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
                @if(!empty($search))
                    <a href="{{ route('admin.cms.services.index', ['category' => $category]) }}" class="px-2 py-1.5 text-xs text-rose-600 hover:underline">Clear</a>
                @endif
            </form>
        </div>

        <!-- Services Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Service Details</th>
                            <th class="py-3.5 px-4">Category</th>
                            <th class="py-3.5 px-4">Fee (PKR)</th>
                            <th class="py-3.5 px-4">Turnaround</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($services as $item)
                            <tr class="hover:bg-slate-50/80 transition {{ !$item->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                                <td class="py-3 px-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-base flex-shrink-0">
                                            {{ $item->icon }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <h4 class="font-bold text-slate-900 truncate">{{ $item->heading }}</h4>
                                                @if($item->popular)
                                                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200 uppercase">
                                                        ★ Popular
                                                    </span>
                                                @endif
                                            </div>
                                            @if($item->urdu)
                                                <p class="text-[11px] text-blue-600 font-medium">{{ $item->urdu }}</p>
                                            @endif
                                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Code: {{ $item->code }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $categories[$item->category]['icon'] ?? '📁' }} {{ $item->category_name }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 font-bold text-slate-900 tabular-nums">
                                    {{ $item->currency }} {{ number_format($item->fee, 0) }}
                                </td>

                                <td class="py-3 px-4 text-slate-600">
                                    ⏱️ {{ $item->turnaround }}
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <form method="POST" action="{{ route('admin.cms.services.toggle', $item) }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            title="Click to toggle active status"
                                            class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold cursor-pointer transition {{ $item->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $item->is_active ? 'Active' : 'Hidden' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a
                                            href="{{ route('admin.cms.services.edit', $item) }}"
                                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 font-semibold transition"
                                            title="Edit Service Details"
                                        >
                                            ✏️ Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.cms.services.destroy', $item) }}"
                                            onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($item->heading) }}\'? This action cannot be undone.');"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition"
                                                title="Delete Service"
                                            >
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <p class="text-3xl mb-2">🔍</p>
                                    <p class="text-sm font-semibold text-slate-700">Koi service nahi mili</p>
                                    <p class="text-xs text-slate-400 mt-1">Nayi service add karne ke liye upar 'Add New Service' button dabayein.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($services->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
