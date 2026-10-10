<x-layouts.app header="Clients Directory &amp; Performance Board">
    <div class="space-y-6">
        <!-- 1. Executive Performance Bar & Identity -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 text-white shadow-md flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        👥 Clients Directory
                    </span>
                    <span class="text-xs text-slate-400 font-mono">/clients</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Clients Master Directory &amp; Compliance Board</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Firm ke tamam individual aur business clients ka record, CNIC encryption status, NTN assignment, aur unke tax returns ka centralized portfolio.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('clients.export') }}">
                    <button class="px-3.5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition flex items-center space-x-1.5" title="Export Clients Directory to Excel">
                        <span>📊 Export Excel</span>
                    </button>
                </a>
                <a href="{{ route('clients.create') }}">
                    <button class="px-4 py-2.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs transition flex items-center space-x-1.5">
                        <span>+ Register New Client</span>
                    </button>
                </a>
            </div>
        </div>

        <!-- 2. KPI Performance Stat Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <a href="{{ route('clients.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-400 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Clients</div>
                <div class="text-2xl font-bold text-slate-800 mt-1 font-mono tabular-nums">{{ $totalClients ?? 0 }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Enrolled taxpayers</div>
            </a>

            <a href="{{ route('clients.index', ['status' => 'active']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-emerald-400 transition">
                <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Active Clients</div>
                <div class="text-2xl font-bold text-emerald-600 mt-1 font-mono tabular-nums">{{ $activeClients ?? 0 }}</div>
                <div class="text-[11px] text-emerald-600/80 mt-0.5 font-medium">Fully verified</div>
            </a>

            <a href="{{ route('clients.index', ['status' => 'pending_ntn']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-amber-400 transition">
                <div class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Pending NTN</div>
                <div class="text-2xl font-bold text-amber-600 mt-1 font-mono tabular-nums">{{ $pendingNtnClients ?? 0 }}</div>
                <div class="text-[11px] text-amber-600/80 mt-0.5 font-medium">Awaiting FBR registration</div>
            </a>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <div class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">With Tax Returns</div>
                <div class="text-2xl font-bold text-indigo-600 mt-1 font-mono tabular-nums">{{ $withReturnsCount ?? 0 }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Active filing portfolio</div>
            </div>
        </div>

    <!-- Standardized Search and Filter Bar -->
    <x-ui.search-bar
        placeholder="Search clients by name, CNIC, phone, or NTN..."
        :action="route('clients.index')"
        :searchValue="$search"
    >
        <x-slot:filters>
            <select
                name="status"
                onchange="this.form.submit()"
                class="text-xs bg-white border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-100"
            >
                <option value="">All Statuses</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending_ntn" {{ $status === 'pending_ntn' ? 'selected' : '' }}>Pending NTN</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </x-slot:filters>

        <x-slot:actions>
            <a href="{{ route('clients.create') }}">
                <x-ui.button variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Register Client</span>
                </x-ui.button>
            </a>
        </x-slot:actions>
    </x-ui.search-bar>

    <!-- Standard Clients Table -->
    <x-ui.table
        :headers="['Client Name', 'CNIC (Encrypted)', 'Contact', 'NTN', 'Status', 'Tax Returns', 'Actions']"
        :empty="$clients->isEmpty()"
        emptyMessage="Koi client nahi mila. Naya client register karein!"
    >
        @foreach($clients as $client)
            <tr class="hover:bg-gray-50/50 transition">
                <x-ui.td>
                    <div class="font-bold text-gray-900">{{ $client->name }}</div>
                    @if($client->father_name)
                        <div class="text-[11px] text-gray-400">S/o {{ $client->father_name }}</div>
                    @endif
                </x-ui.td>
                <x-ui.td class="font-mono text-xs font-semibold text-slate-800">
                    {{ $client->cnic }}
                </x-ui.td>
                <x-ui.td>
                    <div class="text-xs font-medium text-gray-800">{{ $client->phone }}</div>
                    @if($client->email)
                        <div class="text-[11px] text-gray-400">{{ $client->email }}</div>
                    @endif
                </x-ui.td>
                <x-ui.td class="font-mono text-xs">
                    @if($client->ntn)
                        <span class="text-slate-800 font-semibold">{{ $client->ntn }}</span>
                    @else
                        <span class="text-amber-600 italic text-[11px]">NTN Required</span>
                    @endif
                </x-ui.td>
                <x-ui.td>
                    <x-ui.badge :status="$client->status" />
                </x-ui.td>
                <x-ui.td>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        {{ $client->tax_returns_count }} {{ Str::plural('Return', $client->tax_returns_count) }}
                    </span>
                </x-ui.td>
                <x-ui.td>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('clients.show', $client) }}" class="p-1.5 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg text-sm transition" title="Client Profile & Dossier">
                            👁️
                        </a>
                        <a href="{{ route('returns.create', ['client_id' => $client->id]) }}" class="p-1.5 text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg text-sm transition" title="New Tax Return Wizard">
                            🧮
                        </a>
                        <a href="{{ route('clients.edit', $client) }}" class="p-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition" title="Edit Client Information">
                            ✏️
                        </a>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Kya aap waqai Client \'{{ $client->name }}\' ko delete karna chahte hain? Inke tamam returns bhi delete ho jayenge.');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg text-sm transition cursor-pointer" title="Delete Client Profile">
                                🗑️
                            </button>
                        </form>
                    </div>
                </x-ui.td>
            </tr>
        @endforeach

        @if($clients->hasPages())
            <x-slot:footer>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Showing {{ $clients->firstItem() }} to {{ $clients->lastItem() }} of {{ $clients->total() }} clients
                    </p>
                    <div>
                        {{ $clients->links() }}
                    </div>
                </div>
            </x-slot:footer>
        @endif
    </x-ui.table>
    </div>
</x-layouts.app>
