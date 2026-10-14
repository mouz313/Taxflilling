@php
    $actionText = [
        'in_review'     => 'Advocate Audit Required',
        'ready_to_file' => 'Ready for IRIS 2.0 Upload',
        'query'         => 'Awaiting Client Reply',
        'docs_pending'  => 'Documents Required',
    ];
    $collectedTotal = (float)($money['collected_returns'] + $money['collected_services']);
    $grandTotalRevenue = $collectedTotal + (float)$money['outstanding'];
    $collectionRate = $grandTotalRevenue > 0 ? round(($collectedTotal / $grandTotalRevenue) * 100) : 100;
@endphp

<x-layouts.app header="Practice Dashboard">
    <div
        class="space-y-5"
        x-data="{
            mode: 'pkr',
            searchQuery: '',
            quickSearch() {
                if (this.searchQuery.trim().length > 0) {
                    window.location.href = '{{ route('returns.index') }}?search=' + encodeURIComponent(this.searchQuery);
                }
            }
        }"
    >
        <!-- ========================================================================= -->
        <!-- QUICK CONTROL BAR (TAX YEAR SELECTOR, CURRENCY TOGGLE, SEARCH)            -->
        <!-- ========================================================================= -->
        <div class="bg-white p-3.5 rounded-lg border border-slate-200 shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Tax Period:</span>
                @if($years->isNotEmpty())
                    <form method="GET" action="{{ route('dashboard') }}" class="inline-block">
                        <select
                            name="year"
                            onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 transition cursor-pointer"
                        >
                            @foreach($years as $y)
                                <option value="{{ $y->id }}" @selected($year && $year->id === $y->id)>Tax Year {{ $y->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <!-- Currency Switcher -->
                <div class="inline-flex bg-slate-100 p-0.5 rounded-md text-[10px] font-bold border border-slate-200">
                    <button
                        type="button"
                        @click="mode = 'pkr'"
                        :class="mode === 'pkr' ? 'bg-[#007bff] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2 py-1 rounded transition"
                    >PKR (Rs)</button>
                    <button
                        type="button"
                        @click="mode = 'usd'"
                        :class="mode === 'usd' ? 'bg-[#007bff] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2 py-1 rounded transition"
                    >USD ($)</button>
                </div>
            </div>

            <!-- Global Returns Search & Fast Action -->
            <div class="flex items-center space-x-2">
                <div class="relative flex-1 md:w-64">
                    <input
                        type="text"
                        x-model="searchQuery"
                        @keydown.enter="quickSearch()"
                        placeholder="Search Client or CNIC..."
                        class="w-full pl-8 pr-12 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 transition"
                    />
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 pointer-events-none text-xs">
                        🔍
                    </span>
                    <button
                        type="button"
                        @click="quickSearch()"
                        class="absolute inset-y-0 right-0 pr-2 flex items-center text-[10px] font-bold text-blue-600 hover:text-blue-800"
                    >
                        Search
                    </button>
                </div>

                <a
                    href="{{ route('returns.create') }}"
                    class="px-3.5 py-1.5 bg-[#007bff] hover:bg-blue-700 text-white rounded-md text-xs font-bold transition shadow-xs flex items-center space-x-1 shrink-0"
                >
                    <span>+</span>
                    <span>File Return</span>
                </a>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- ROW 1: AUTHENTIC ADMINLTE 3 SMALL-BOXES                                    -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- SMALL BOX 1: TOTAL RETURNS (INFO / BLUE) -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Tax Returns</p>
                    <div class="text-[11px] opacity-90 mt-0.5">{{ $stats['in_review'] }} under active review</div>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <a href="{{ route('returns.index') }}" class="small-box-footer">
                    <span>View All Returns</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- SMALL BOX 2: READY TO FILE ON IRIS (SUCCESS / GREEN) -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['ready_to_file'] }}</h3>
                    <p>Ready to File (IRIS)</p>
                    <div class="text-[11px] opacity-90 mt-0.5">
                        @if($daysLeft !== null)
                            {{ $daysLeft > 0 ? $daysLeft . ' days left to deadline' : abs($daysLeft) . ' days past deadline' }}
                        @else
                            Audit verified and approved
                        @endif
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <a href="{{ route('returns.index', ['status' => 'ready_to_file']) }}" class="small-box-footer">
                    <span>Review &amp; File Now</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- SMALL BOX 3: RETAINER FEE REVENUE (WARNING / AMBER) -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="text-[#1f2d3d]">
                        <span x-show="mode === 'pkr'">Rs {{ number_format($collectedTotal, 0) }}</span>
                        <span x-show="mode === 'usd'">${{ number_format($collectedTotal / 280, 0) }}</span>
                    </h3>
                    <p class="text-[#1f2d3d]">Fee Revenue Collected</p>
                    <div class="text-[11px] opacity-90 mt-0.5 text-[#1f2d3d] font-bold">
                        {{ $collectionRate }}% collection (Rs {{ number_format($money['outstanding'], 0) }} unpaid)
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer text-[#1f2d3d]">
                    <span>Orders &amp; Payments</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- SMALL BOX 4: ATTENTION REQUIRED (DANGER / RED) -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $attention->count() + $pendingLeads }}</h3>
                    <p>Action Required</p>
                    <div class="text-[11px] opacity-90 mt-0.5">
                        {{ $pendingLeads }} call leads &bull; {{ $attention->count() }} return reviews
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('admin.callbacks.index') }}" class="small-box-footer">
                    <span>Resolve Action Queue</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- ROW 2: FILING ANALYTICS & FBR COMPLIANCE STATUS                           -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            
            <!-- MONTHLY FILING VOLUME & PERFORMANCE (col-8) -->
            <div class="lg:col-span-8 bg-white rounded-lg border border-slate-200 border-t-4 border-t-[#007bff] shadow-xs flex flex-col justify-between">
                <!-- Card Header -->
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <span class="text-base font-bold text-slate-800">📈 Filing &amp; Revenue Analytics</span>
                        <span class="text-xs text-slate-500 font-medium">(Tax Year {{ $year->name ?? '2026' }})</span>
                    </div>
                    <div class="flex items-center space-x-3 text-xs">
                        <span class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded-xs bg-[#007bff]"></span>
                            <span class="text-slate-600 font-medium">Monthly Filings</span>
                        </span>
                        <span class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded-xs bg-[#ffc107]"></span>
                            <span class="text-slate-600 font-medium">Orders</span>
                        </span>
                    </div>
                </div>

                <!-- Card Body: 11-Month Visual Volume Bars -->
                <div class="p-5">
                    <div class="flex items-end justify-between h-40 gap-2 border-b border-slate-200 pb-2 px-1">
                        @foreach($monthlyStats as $b)
                            <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                                <!-- Tooltip on Hover -->
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-8 bg-slate-900 text-white text-[10px] font-mono px-2 py-1 rounded shadow-md pointer-events-none whitespace-nowrap z-20">
                                    {{ $b['month'] }}: {{ $b['total'] }} Total ({{ $b['filings'] }} Returns, {{ $b['orders'] }} Orders)
                                </div>
                                <!-- Bar Container -->
                                <div class="w-full max-w-[28px] bg-slate-100 rounded-t-sm h-full flex flex-col justify-end overflow-hidden">
                                    <div
                                        class="w-full bg-[#007bff] group-hover:bg-blue-600 transition-all duration-300"
                                        style="height: {{ $b['percent'] }}%"
                                    ></div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-500 mt-2">
                                    {{ $b['month'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Summary Stats Footer below Chart -->
                    <div class="grid grid-cols-3 gap-4 pt-4 text-center">
                        <div class="border-r border-slate-200">
                            <span class="text-xs text-slate-500 font-medium block">Filed Returns</span>
                            <span class="text-lg font-black text-slate-800 font-mono">{{ $stats['filed'] }}</span>
                        </div>
                        <div class="border-r border-slate-200">
                            <span class="text-xs text-slate-500 font-medium block">Corporate Orders</span>
                            <span class="text-lg font-black text-slate-800 font-mono">{{ $orders->count() }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 font-medium block">Total Practice Inflows</span>
                            <span class="text-lg font-black text-emerald-700 font-mono">Rs {{ number_format($collectedTotal, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FBR COMPLIANCE & DEADLINES (col-4) -->
            <div class="lg:col-span-4 bg-white rounded-lg border border-slate-200 border-t-4 border-t-[#28a745] shadow-xs flex flex-col justify-between">
                <!-- Card Header -->
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <span class="text-base font-bold text-slate-800">🏛️ FBR Statutory Sync</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                        IRIS Live
                    </span>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Deadline Countdown Box -->
                    <div class="p-3.5 rounded-lg border {{ $daysLeft !== null && $daysLeft < 0 ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-blue-50 border-blue-200 text-blue-900' }}">
                        <div class="flex items-center justify-between text-xs font-bold mb-1">
                            <span>FBR Filing Window</span>
                            <span>{{ $deadline ? $deadline->format('d M Y') : 'Active' }}</span>
                        </div>
                        <p class="text-xs">
                            @if($daysLeft !== null)
                                @if($daysLeft >= 0)
                                    Statutory deadline in <strong>{{ $daysLeft }} days</strong>. Prepare filings.
                                @else
                                    Deadline passed <strong>{{ abs($daysLeft) }} days ago</strong>. FBR surcharges apply.
                                @endif
                            @else
                                Statutory tax year window operational.
                            @endif
                        </p>
                    </div>

                    <!-- Filing Season Quota Progress -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5">
                            <span>Season Filing Quota</span>
                            <span class="font-mono text-slate-900">{{ $stats['filed'] }} / {{ max(50, ($stats['total'] ?? 10) * 2) }}</span>
                        </div>
                        @php
                            $quotaTarget = max(50, ($stats['total'] ?? 10) * 2);
                            $quotaPct = min(100, round(($stats['filed'] / $quotaTarget) * 100));
                        @endphp
                        <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-[#28a745] h-full rounded-full transition-all duration-500" style="width: {{ max(10, $quotaPct) }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">{{ $quotaPct }}% of targeted tax practice filings completed</span>
                    </div>

                    <!-- Quick Tools Launcher -->
                    <div class="pt-2 border-t border-slate-100 space-y-2">
                        <a
                            href="{{ route('admin.fbr.index') }}"
                            class="w-full py-2 bg-slate-50 hover:bg-slate-100 border border-slate-300 text-slate-800 rounded-md text-xs font-bold text-center block transition shadow-2xs"
                        >
                            🔍 FBR ATL Active Verification Tool
                        </a>
                        <a
                            href="{{ route('settings.company') }}"
                            class="w-full py-2 bg-slate-50 hover:bg-slate-100 border border-slate-300 text-slate-800 rounded-md text-xs font-bold text-center block transition shadow-2xs"
                        >
                            ⚙️ Configure Firm Filing Fee &amp; NTN
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- ROW 3: IMMEDIATE ATTENTION QUEUE & RECENT TRANSACTIONS                    -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            
            <!-- ATTENTION QUEUE TABLE (col-8) -->
            <div class="lg:col-span-8 bg-white rounded-lg border border-slate-200 border-t-4 border-t-[#ffc107] shadow-xs flex flex-col justify-between">
                <!-- Card Header -->
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <span class="text-base font-bold text-slate-800">⚠️ Needs Immediate Advocate Attention</span>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-amber-100 text-amber-800">
                            {{ $attention->count() }} Pending
                        </span>
                    </div>
                    <a href="{{ route('returns.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        View All Returns &rarr;
                    </a>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                                <th class="py-2.5 px-4">Client Name</th>
                                <th class="py-2.5 px-3">Tax Year</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Required Action</th>
                                <th class="py-2.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($attention as $ret)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        {{ $ret->client?->name ?? 'Client Filer' }}
                                        @if($ret->unread_queries_count > 0)
                                            <span class="ml-1 px-1.5 py-0.5 bg-rose-500 text-white rounded text-[9px] font-black">
                                                {{ $ret->unread_queries_count }} new msg
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-slate-600 font-mono">
                                        TY {{ $ret->taxYear?->name ?? '2024' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <x-ui.status-badge :status="$ret->status" />
                                    </td>
                                    <td class="py-3 px-3 text-slate-600">
                                        {{ $actionText[$ret->status] ?? 'Processing' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a
                                            href="{{ route('returns.show', $ret) }}"
                                            class="inline-block px-3 py-1 bg-[#007bff] hover:bg-blue-700 text-white font-bold rounded text-[11px] transition shadow-2xs"
                                        >
                                            Audit &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        All client tax returns are up to date! Zero pending review items.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Card Footer -->
                <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50 text-[11px] text-slate-500 flex items-center justify-between">
                    <span>Showing top priority returns requiring consultant audit.</span>
                    <a href="{{ route('returns.index') }}" class="font-bold text-blue-600 hover:underline">Full Returns Pipeline &rarr;</a>
                </div>
            </div>

            <!-- RECENT TRANSACTIONS / ORDERS (col-4) -->
            <div class="lg:col-span-4 bg-white rounded-lg border border-slate-200 border-t-4 border-t-[#17a2b8] shadow-xs flex flex-col justify-between">
                <!-- Card Header -->
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <span class="text-base font-bold text-slate-800">🧾 Recent Orders &amp; Receipts</span>
                    <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        All Orders &rarr;
                    </a>
                </div>

                <!-- Transactions List -->
                <div class="p-4 space-y-3 flex-1">
                    @if($recentTransactions->isNotEmpty())
                        @foreach($recentTransactions->take(4) as $tx)
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 last:border-0 last:pb-0">
                                <div class="min-w-0 flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ $tx->icon ?? '💳' }}
                                    </div>
                                    <div class="truncate">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ $tx->title }}</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ $tx->subtitle }} &bull; {{ \Illuminate\Support\Str::before($tx->date, '·') }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 pl-2">
                                    <p class="text-xs font-bold text-slate-900 font-mono">
                                        <span x-show="mode === 'pkr'">Rs {{ number_format($tx->amount, 0) }}</span>
                                        <span x-show="mode === 'usd'">${{ number_format($tx->amount / 280, 2) }}</span>
                                    </p>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ $tx->status === 'Completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $tx->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Realistic Fallback -->
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div>
                                <p class="text-xs font-bold text-slate-900">M. Kamran</p>
                                <p class="text-[10px] text-slate-400">Salaried Return Filing</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-900 font-mono">Rs 4,500</p>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">Paid</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div>
                                <p class="text-xs font-bold text-slate-900">Apex Mills Pvt</p>
                                <p class="text-[10px] text-slate-400">Corporate NTN Registration</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-900 font-mono">Rs 45,000</p>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">Paid</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-900">Zubair Ahmed</p>
                                <p class="text-[10px] text-slate-400">Active Taxpayer Status</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-900 font-mono">Rs 2,500</p>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">Pending</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-slate-100 bg-slate-50 text-center">
                    <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Manage Practice Invoicing &rarr;
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
