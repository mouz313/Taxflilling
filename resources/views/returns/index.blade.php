<x-layouts.app header="Tax Returns Pipeline">
    <div class="space-y-4 w-full">
        <!-- ========================================================================= -->
        <!-- 1. ADMINLTE KPI SMALL-BOXES (INFO-BOXES)                                  -->
        <!-- ========================================================================= -->
        <!-- ========================================================================= -->
        <!-- 1. AUTHENTIC ADMINLTE KPI SMALL-BOXES                                     -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 w-full">
            <!-- Box 1: Total -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalReturns ?? 0 }}</h3>
                    <p>Total Returns</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <a href="{{ route('returns.index') }}" class="small-box-footer">
                    <span>Pipeline Volume</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 2: In Review -->
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $inReviewReturns ?? 0 }}</h3>
                    <p>In Review</p>
                </div>
                <div class="icon">
                    <i class="fas fa-search"></i>
                </div>
                <a href="{{ route('returns.index', ['status' => 'in_review']) }}" class="small-box-footer">
                    <span>Audit Pending</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 3: Ready to File -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $readyToFile ?? 0 }}</h3>
                    <p>Ready to File</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <a href="{{ route('returns.index', ['status' => 'ready_to_file']) }}" class="small-box-footer">
                    <span>IRIS Entry</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 4: Filed on IRIS -->
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $filedReturns ?? 0 }}</h3>
                    <p>Filed on IRIS</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <a href="{{ route('returns.index', ['status' => 'filed']) }}" class="small-box-footer">
                    <span>Ack Issued ✓</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 5: Tax Volume -->
            <div class="small-box bg-warning col-span-2 sm:col-span-1">
                <div class="inner">
                    <h3 class="text-[#1f2d3d] text-lg font-mono">Rs {{ number_format($totalTaxVolume ?? 0, 0) }}</h3>
                    <p class="text-[#1f2d3d]">Taxable Volume</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="small-box-footer text-[#1f2d3d]">
                    <span>Assessed Income</span>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. ADMINLTE CARD: FULL-WIDTH TABLE & CONTROLS                             -->
        <!-- ========================================================================= -->
        <div class="card card-primary card-outline w-full">
            <!-- Card Header -->
            <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="card-title text-sm font-bold text-[#212529] flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-[#007bff]"></i>
                    <span>Tax Returns Master Dossier ({{ $returns->total() }} records)</span>
                </h3>
                <!-- Card Tools -->
                <div class="card-tools flex items-center gap-2">
                    <a
                        href="{{ route('returns.export') }}"
                        class="btn btn-xs btn-success font-bold inline-flex items-center gap-1"
                        title="Export Returns to Excel"
                    >
                        <i class="fas fa-file-excel"></i>
                        <span>Export Excel</span>
                    </a>
                    <a
                        href="{{ route('returns.create') }}"
                        class="btn btn-xs btn-primary font-bold inline-flex items-center gap-1"
                    >
                        <i class="fas fa-plus"></i>
                        <span>New Return</span>
                    </a>
                </div>
            </div>

            <!-- Filter Bar Inside Card -->
            <div class="p-4 border-b border-slate-100 bg-white">
                <form method="GET" action="{{ route('returns.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <!-- Search Input -->
                    <div class="sm:col-span-6 relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search by client name, CNIC, or IRIS number..."
                            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 transition"
                        />
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 pointer-events-none text-xs">
                            🔍
                        </span>
                    </div>

                    <!-- Pipeline Stage Filter -->
                    <div class="sm:col-span-3">
                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500 cursor-pointer"
                        >
                            <option value="">All Pipeline Stages</option>
                            <option value="draft" {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="docs_pending" {{ ($status ?? '') === 'docs_pending' ? 'selected' : '' }}>Docs Pending</option>
                            <option value="in_review" {{ ($status ?? '') === 'in_review' ? 'selected' : '' }}>In Review</option>
                            <option value="query" {{ ($status ?? '') === 'query' ? 'selected' : '' }}>Query Sent</option>
                            <option value="ready_to_file" {{ ($status ?? '') === 'ready_to_file' ? 'selected' : '' }}>Ready to File</option>
                            <option value="filed" {{ ($status ?? '') === 'filed' ? 'selected' : '' }}>Filed on IRIS</option>
                            <option value="closed" {{ ($status ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <!-- Tax Year Filter -->
                    <div class="sm:col-span-3 flex space-x-2">
                        <select
                            name="tax_year_id"
                            onchange="this.form.submit()"
                            class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500 cursor-pointer"
                        >
                            <option value="">All Tax Years</option>
                            @foreach($taxYears as $ty)
                                <option value="{{ $ty->id }}" {{ ($taxYearId ?? null) == $ty->id ? 'selected' : '' }}>Tax Year {{ $ty->name }}</option>
                            @endforeach
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
                            <th class="py-2.5 px-4">Client Details</th>
                            <th class="py-2.5 px-3">CNIC</th>
                            <th class="py-2.5 px-3">Tax Year</th>
                            <th class="py-2.5 px-3">Total Income</th>
                            <th class="py-2.5 px-3">Tax Liability</th>
                            <th class="py-2.5 px-3">Pipeline Status</th>
                            <th class="py-2.5 px-3">Lock</th>
                            <th class="py-2.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($returns as $ret)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <div class="font-bold text-slate-900">{{ $ret->client->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-normal">{{ $ret->client->phone }}</div>
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-700 text-xs">
                                    {{ $ret->client->cnic }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-semibold text-slate-800">TY {{ $ret->taxYear->name }}</span>
                                    @if($ret->filing_type === 'amendment')
                                        <span class="block text-[9px] text-amber-600 font-bold uppercase">Amendment #{{ $ret->amendment_no }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-900 font-mono">
                                    Rs {{ number_format($ret->total_income, 0) }}
                                </td>
                                <td class="py-3 px-3">
                                    @if($ret->net_tax_payable_or_refundable > 0)
                                        <span class="text-xs font-bold text-rose-600 font-mono">
                                            Payable: Rs {{ number_format($ret->net_tax_payable_or_refundable, 0) }}
                                        </span>
                                    @elseif($ret->net_tax_payable_or_refundable < 0)
                                        <span class="text-xs font-bold text-teal-600 font-mono">
                                            Refund: Rs {{ number_format(abs($ret->net_tax_payable_or_refundable), 0) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Rs 0</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <x-ui.badge :status="$ret->status" />
                                </td>
                                <td class="py-3 px-3">
                                    @if($ret->is_locked)
                                        <span class="inline-flex items-center text-[11px] text-slate-500 font-medium">
                                            🔒 Locked
                                        </span>
                                    @else
                                        <span class="text-[11px] text-emerald-600 font-medium">Editable</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center space-x-1">
                                        <a href="{{ route('returns.pdf', $ret) }}" class="p-1 px-2 text-blue-700 bg-blue-50 hover:bg-blue-100 rounded text-xs transition" title="Download FBR 114(1) PDF">
                                            📄 PDF
                                        </a>
                                        <a href="{{ route('returns.wizard', $ret) }}" class="p-1 px-2 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded text-xs transition font-semibold" title="Filing Wizard">
                                            🧮 Wizard
                                        </a>
                                        <a href="{{ route('returns.show', $ret) }}" class="p-1 px-2 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded text-xs transition font-semibold" title="Console">
                                            📋 View
                                        </a>
                                        <form method="POST" action="{{ route('returns.destroy', $ret) }}" onsubmit="return confirm('Tax Return #{{ $ret->id }} ko delete karna chahte hain?');" class="inline">
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
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    Is filter ke mutabiq koi tax return nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Card Footer Pagination -->
            @if($returns->hasPages())
                <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Showing {{ $returns->firstItem() }} to {{ $returns->lastItem() }} of {{ $returns->total() }} returns
                    </p>
                    <div>
                        {{ $returns->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
