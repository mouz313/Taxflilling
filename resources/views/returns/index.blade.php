<x-layouts.app header="Tax Returns Pipeline &amp; Performance Board">
    <div class="space-y-6">
        <!-- 1. Executive Performance Bar & Identity -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 text-white shadow-md flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                        📑 Returns Dossier &bull; FBR IRIS 2.0
                    </span>
                    <span class="text-xs text-slate-400 font-mono">/returns</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Tax Returns Filing Pipeline &amp; Performance Board</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Client tax returns ki 12-category calculations, advance tax adjustments, IRIS copy sheet dossier, aur FBR submission timeline ko monitor karein.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('returns.export') }}">
                    <button class="px-3.5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition flex items-center space-x-1.5" title="Export Pipeline to Excel">
                        <span>📊 Export Excel</span>
                    </button>
                </a>
                <a href="{{ route('returns.create') }}">
                    <button class="px-4 py-2.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs transition flex items-center space-x-1.5">
                        <span>+ Initiate Tax Return</span>
                    </button>
                </a>
            </div>
        </div>

        <!-- 2. KPI Performance Stat Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <a href="{{ route('returns.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-400 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Returns</div>
                <div class="text-2xl font-bold text-slate-800 mt-1 font-mono tabular-nums">{{ $totalReturns ?? 0 }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Pipeline volume</div>
            </a>

            <a href="{{ route('returns.index', ['status' => 'in_review']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-400 transition">
                <div class="text-xs font-semibold text-blue-700 uppercase tracking-wider">In Review</div>
                <div class="text-2xl font-bold text-blue-600 mt-1 font-mono tabular-nums">{{ $inReviewReturns ?? 0 }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Dossier being checked</div>
            </a>

            <a href="{{ route('returns.index', ['status' => 'ready_to_file']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-indigo-400 transition">
                <div class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">Ready to File</div>
                <div class="text-2xl font-bold text-indigo-600 mt-1 font-mono tabular-nums">{{ $readyToFile ?? 0 }}</div>
                <div class="text-[11px] text-indigo-600/80 mt-0.5 font-medium">Approved for IRIS entry</div>
            </a>

            <a href="{{ route('returns.index', ['status' => 'filed']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-emerald-400 transition">
                <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Filed on IRIS</div>
                <div class="text-2xl font-bold text-emerald-600 mt-1 font-mono tabular-nums">{{ $filedReturns ?? 0 }}</div>
                <div class="text-[11px] text-emerald-600/80 mt-0.5 font-medium">Ack number issued ✓</div>
            </a>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Taxable Income</div>
                <div class="text-xl font-bold text-slate-800 mt-1 font-mono tabular-nums">PKR {{ number_format($totalTaxVolume ?? 0, 0) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Assessed volume</div>
            </div>
        </div>

    <!-- Standardized Search and Filter Bar -->
    <x-ui.search-bar
        placeholder="Search returns by client name, CNIC, or IRIS number..."
        :action="route('returns.index')"
        :searchValue="$search"
    >
        <x-slot:filters>
            <select
                name="status"
                onchange="this.form.submit()"
                class="text-xs bg-white border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-100"
            >
                <option value="">All Pipeline Stages</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="docs_pending" {{ $status === 'docs_pending' ? 'selected' : '' }}>Docs Pending</option>
                <option value="in_review" {{ $status === 'in_review' ? 'selected' : '' }}>In Review</option>
                <option value="query" {{ $status === 'query' ? 'selected' : '' }}>Query Sent</option>
                <option value="ready_to_file" {{ $status === 'ready_to_file' ? 'selected' : '' }}>Ready to File</option>
                <option value="filed" {{ $status === 'filed' ? 'selected' : '' }}>Filed on IRIS</option>
                <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <select
                name="tax_year_id"
                onchange="this.form.submit()"
                class="text-xs bg-white border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-100"
            >
                <option value="">All Tax Years</option>
                @foreach($taxYears as $ty)
                    <option value="{{ $ty->id }}" {{ $taxYearId == $ty->id ? 'selected' : '' }}>{{ $ty->name }}</option>
                @endforeach
            </select>
        </x-slot:filters>

        <x-slot:actions>
            <a href="{{ route('returns.create') }}">
                <x-ui.button variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Return</span>
                </x-ui.button>
            </a>
        </x-slot:actions>
    </x-ui.search-bar>

    <!-- Standard Returns Table -->
    <x-ui.table
        :headers="['Client Details', 'CNIC', 'Tax Year', 'Total Income', 'Tax Liability', 'Pipeline Status', 'Filing Lock', 'Actions']"
        :empty="$returns->isEmpty()"
        emptyMessage="Is filter ke mutabiq koi return nahi mila."
    >
        @foreach($returns as $ret)
            <tr class="hover:bg-gray-50/50 transition">
                <x-ui.td>
                    <div class="font-bold text-gray-900">{{ $ret->client->name }}</div>
                    <div class="text-xs text-gray-400">{{ $ret->client->phone }}</div>
                </x-ui.td>
                <x-ui.td class="font-mono text-xs">
                    {{ $ret->client->cnic }}
                </x-ui.td>
                <x-ui.td>
                    <span class="text-xs font-semibold text-gray-800">{{ $ret->taxYear->name }}</span>
                    @if($ret->filing_type === 'amendment')
                        <span class="block text-[10px] text-amber-600 font-bold uppercase">Amendment #{{ $ret->amendment_no }}</span>
                    @endif
                </x-ui.td>
                <x-ui.td class="font-medium text-gray-900">
                    PKR {{ number_format($ret->total_income, 0) }}
                </x-ui.td>
                <x-ui.td>
                    @if($ret->net_tax_payable_or_refundable > 0)
                        <span class="text-xs font-bold text-rose-600">
                            Payable: PKR {{ number_format($ret->net_tax_payable_or_refundable, 0) }}
                        </span>
                    @elseif($ret->net_tax_payable_or_refundable < 0)
                        <span class="text-xs font-bold text-teal-600">
                            Refund: PKR {{ number_format(abs($ret->net_tax_payable_or_refundable), 0) }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400">PKR 0</span>
                    @endif
                </x-ui.td>
                <x-ui.td>
                    <x-ui.badge :status="$ret->status" />
                </x-ui.td>
                <x-ui.td>
                    @if($ret->is_locked)
                        <span class="inline-flex items-center text-xs text-gray-500 font-medium">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                            Locked
                        </span>
                    @else
                        <span class="text-xs text-emerald-600 font-medium">Editable</span>
                    @endif
                </x-ui.td>
                <x-ui.td>
                    <div class="flex items-center space-x-1.5">
                        <a href="{{ route('returns.pdf', $ret) }}" class="p-1.5 text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg text-sm transition" title="Download Official FBR 114(1) PDF Dossier">
                            📄
                        </a>
                        <a href="{{ route('returns.wizard', $ret) }}" class="p-1.5 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg text-sm transition" title="12-Category Filing Wizard">
                            🧮
                        </a>
                        <a href="{{ route('returns.show', $ret) }}" class="p-1.5 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg text-sm transition" title="FBR IRIS Console & Codes">
                            📋
                        </a>
                        <form method="POST" action="{{ route('returns.destroy', $ret) }}" onsubmit="return confirm('Kya aap waqai Tax Return #{{ $ret->id }} ({{ $ret->client->name }}) ko delete karna chahte hain? Tamam data delete ho jayega.');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg text-sm transition cursor-pointer" title="Delete Tax Return">
                                🗑️
                            </button>
                        </form>
                    </div>
                </x-ui.td>
            </tr>
        @endforeach

        @if($returns->hasPages())
            <x-slot:footer>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Showing {{ $returns->firstItem() }} to {{ $returns->lastItem() }} of {{ $returns->total() }} returns
                    </p>
                    <div>
                        {{ $returns->links() }}
                    </div>
                </div>
            </x-slot:footer>
        @endif
    </x-ui.table>
    </div>
</x-layouts.app>
