<x-layouts.app header="Clients Directory">
    <div class="space-y-4 w-full">
        <!-- ========================================================================= -->
        <!-- 1. ADMINLTE KPI SMALL-BOXES                                               -->
        <!-- ========================================================================= -->
        <!-- ========================================================================= -->
        <!-- 1. AUTHENTIC ADMINLTE KPI SMALL-BOXES                                     -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 w-full">
            <!-- Box 1: Total -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalClients ?? 0 }}</h3>
                    <p>Total Clients</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('clients.index') }}" class="small-box-footer">
                    <span>Enrolled Taxpayers</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 2: Active -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $activeClients ?? 0 }}</h3>
                    <p>Active Clients</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="small-box-footer">
                    <span>Fully Verified ✓</span>
                </div>
            </div>

            <!-- Box 3: Pending NTN -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="text-[#1f2d3d]">{{ $pendingNtnClients ?? 0 }}</h3>
                    <p class="text-[#1f2d3d]">Pending NTN</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="small-box-footer text-[#1f2d3d]">
                    <span>Awaiting Registration</span>
                </div>
            </div>

            <!-- Box 4: With Returns -->
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $withReturnsCount ?? 0 }}</h3>
                    <p>With Tax Returns</p>
                </div>
                <div class="icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <a href="{{ route('returns.index') }}" class="small-box-footer">
                    <span>Active Filing Portfolio</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. ADMINLTE CARD: CLIENTS TABLE & CONTROLS                                -->
        <!-- ========================================================================= -->
        <div class="card card-primary card-outline w-full">
            <!-- Card Header -->
            <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="card-title text-sm font-bold text-[#212529] flex items-center gap-2">
                    <i class="fas fa-users text-[#007bff]"></i>
                    <span>Clients Master Directory ({{ $clients->total() }} records)</span>
                </h3>
                <div class="card-tools flex items-center gap-2">
                    <a
                        href="{{ route('clients.export') }}"
                        class="btn btn-xs btn-success font-bold inline-flex items-center gap-1"
                    >
                        <i class="fas fa-file-excel"></i>
                        <span>Export Excel</span>
                    </a>
                    <a
                        href="{{ route('clients.create') }}"
                        class="btn btn-xs btn-primary font-bold inline-flex items-center gap-1"
                    >
                        <i class="fas fa-user-plus"></i>
                        <span>New Client</span>
                    </a>
                </div>

            <!-- Filter Bar Inside Card -->
            <div class="p-4 border-b border-slate-100 bg-white">
                <form method="GET" action="{{ route('clients.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <div class="sm:col-span-8 relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search clients by name, CNIC, phone, or NTN..."
                            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 transition"
                        />
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 pointer-events-none text-xs">
                            🔍
                        </span>
                    </div>

                    <div class="sm:col-span-4 flex space-x-2">
                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500 cursor-pointer"
                        >
                            <option value="">All Statuses</option>
                            <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending_ntn" {{ ($status ?? '') === 'pending_ntn' ? 'selected' : '' }}>Pending NTN</option>
                            <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-md text-xs font-bold text-slate-700">
                            Go
                        </button>
                    </div>
                </form>
            </div>

            <!-- Full-Width Table -->
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                            <th class="py-2.5 px-4">Client Name</th>
                            <th class="py-2.5 px-3">CNIC</th>
                            <th class="py-2.5 px-3">Contact</th>
                            <th class="py-2.5 px-3">NTN</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Tax Returns</th>
                            <th class="py-2.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($clients as $client)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <div class="font-bold text-slate-900">{{ $client->name }}</div>
                                    @if($client->father_name)
                                        <div class="text-[10px] text-slate-400 font-normal">S/o {{ $client->father_name }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-700 font-semibold text-xs">
                                    {{ $client->cnic }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-slate-800">{{ $client->phone }}</div>
                                    @if($client->email)
                                        <div class="text-[10px] text-slate-400">{{ $client->email }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono text-xs">
                                    @if($client->ntn)
                                        <span class="text-slate-800 font-semibold">{{ $client->ntn }}</span>
                                    @else
                                        <span class="text-amber-600 italic text-[11px]">NTN Required</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <x-ui.badge :status="$client->status" />
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $client->tax_returns_count }} {{ Str::plural('Return', $client->tax_returns_count) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center space-x-1">
                                        <a href="{{ route('clients.show', $client) }}" class="p-1 px-2 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded text-xs transition font-semibold" title="Profile">
                                            👁️ View
                                        </a>
                                        <a href="{{ route('returns.create', ['client_id' => $client->id]) }}" class="p-1 px-2 text-sky-700 bg-sky-50 hover:bg-sky-100 rounded text-xs transition font-semibold" title="New Return">
                                            + Return
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}" class="p-1 px-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded text-xs transition font-semibold" title="Edit">
                                            ✏️ Edit
                                        </a>
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Client \'{{ $client->name }}\' ko delete karna chahte hain? Inke tamam returns bhi delete ho jayenge.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 px-1.5 text-rose-700 bg-rose-50 hover:bg-rose-100 rounded text-xs transition cursor-pointer" title="Delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Koi client nahi mila. Naya client register karein!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Card Footer Pagination -->
            @if($clients->hasPages())
                <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Showing {{ $clients->firstItem() }} to {{ $clients->lastItem() }} of {{ $clients->total() }} clients
                    </p>
                    <div>
                        {{ $clients->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
