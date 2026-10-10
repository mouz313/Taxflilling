<x-layouts.app header="Executive Practice Console & Performance Board">
    <div class="space-y-6">
        <!-- 1. Executive Identity Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 text-white shadow-md flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                        ⚡ Tax Year 2026–2027 Active
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono bg-slate-800 text-slate-300 border border-slate-700">
                        /dashboard &bull; Executive Command Center
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                    {{ $consultancy->firm_name ?? 'TaxFiler Practice Portal' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Executive Operations &amp; Performance Overview: IRIS 2.0 filings, corporate service deliverables, call inquiries desk, aur client compliance ko ek markaz se monitor karein.
                </p>
            </div>

            <!-- Quick Action Command Launchpad -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('returns.create') }}">
                    <button class="px-3.5 py-2 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs transition flex items-center space-x-1.5">
                        <span>+ New Tax Return</span>
                    </button>
                </a>
                <a href="{{ route('clients.create') }}">
                    <button class="px-3.5 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white rounded-xl border border-slate-700 shadow-xs transition flex items-center space-x-1.5">
                        <span>+ Register Client</span>
                    </button>
                </a>
            </div>
        </div>

        <!-- 2. High-Level Practice Financial & Volume Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Revenue -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Fee Revenue</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">💰</span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 font-mono tabular-nums">
                    PKR {{ number_format($totalRevenue, 0) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Tax returns + corporate orders paid</div>
            </div>

            <!-- Taxable Volume Assessed -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Assessed Tax Volume</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">📈</span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 font-mono tabular-nums">
                    PKR {{ number_format($totalTaxableVolume, 0) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Filing dossiers declared value</div>
            </div>

            <!-- Total Tax Returns Pipeline -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Returns In Pipeline</span>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">📑</span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 font-mono tabular-nums">
                    {{ $totalReturns }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1 flex items-center space-x-2">
                    <span class="text-indigo-600 font-semibold">{{ $readyToFile }} ready to file</span>
                    <span>&bull;</span>
                    <span class="text-emerald-600 font-semibold">{{ $filedReturns }} filed</span>
                </div>
            </div>

            <!-- Total Clients Base -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Registered Taxpayers</span>
                    <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">👥</span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 font-mono tabular-nums">
                    {{ $totalClients }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Encrypted CNIC client profiles</div>
            </div>
        </div>

        <!-- 3. Dedicated Performance Boards Launcher (4 Master Operational Desks) -->
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Dedicated Practice Performance Desks</h2>
                <span class="text-xs text-slate-500">Har desk ka apna alag URL aur live dashboard hai</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Desk 1: NTN & Corporate Orders -->
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:border-blue-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">🏢</span>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-mono">
                                {{ $serviceRequests->count() }} Total
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition">NTN & Corporate Orders Desk</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            SECP Pvt Ltd, Individual/Company NTN, STRN Sales Tax, aur USA LLC registration requests.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-1.5">
                            @if($pendingRequestsCount > 0)
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span class="text-xs font-bold text-amber-700">{{ $pendingRequestsCount }} New Orders</span>
                            @else
                                <span class="text-xs text-emerald-600 font-medium">All orders reviewed ✓</span>
                            @endif
                        </div>
                        <span class="text-xs font-semibold text-blue-600 group-hover:translate-x-1 transition">Open Desk &rarr;</span>
                    </div>
                </a>

                <!-- Desk 2: Tax Returns Pipeline -->
                <a
                    href="{{ route('returns.index') }}"
                    class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:border-blue-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">📑</span>
                            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full font-mono">
                                {{ $totalReturns }} Returns
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition">Tax Returns Filing Pipeline</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            12-category calculations, IRIS copy sheet dossier, and FBR IRIS 2.0 electronic submission desk.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-1.5">
                            <span class="text-xs font-bold text-blue-700">{{ $inReviewReturns }} in Review</span>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 group-hover:translate-x-1 transition">Open Desk &rarr;</span>
                    </div>
                </a>

                <!-- Desk 3: Call Requests & Leads Desk -->
                <a
                    href="{{ route('admin.callbacks.index') }}"
                    class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:border-rose-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">📞</span>
                            <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full font-mono">
                                {{ $callbacks->count() }} Leads
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-rose-600 transition">Call Requests & Inquiries</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Website leads aur call back requests ko attend karein, 1-click WhatsApp karein aur conversion dekhein.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-1.5">
                            @if($pendingCallbacksCount > 0)
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <span class="text-xs font-bold text-rose-700">{{ $pendingCallbacksCount }} Calls Waiting</span>
                            @else
                                <span class="text-xs text-emerald-600 font-medium">All calls attended ✓</span>
                            @endif
                        </div>
                        <span class="text-xs font-semibold text-rose-600 group-hover:translate-x-1 transition">Open Desk &rarr;</span>
                    </div>
                </a>

                <!-- Desk 4: Clients Master Directory -->
                <a
                    href="{{ route('clients.index') }}"
                    class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:border-emerald-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">👥</span>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-mono">
                                {{ $totalClients }} Clients
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition">Clients Master Directory</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Taxpayer profiles, CNIC directory, NTN compliance status, and historical filing records.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Active Taxpayer Roster</span>
                        <span class="text-xs font-semibold text-emerald-600 group-hover:translate-x-1 transition">Open Desk &rarr;</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- 4. High-Priority Action Feeds (Two Column Layout) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Recent NTN & Corporate Applications Needing Attention -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Latest Corporate Service Applications</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Orders needing IRIS processing ya certificate delivery</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                        View All ({{ $serviceRequests->count() }}) &rarr;
                    </a>
                </div>

                @if($serviceRequests->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Abhi koi corporate service request nahi aayi.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($serviceRequests->take(5) as $order)
                            <div class="p-4 hover:bg-slate-50/70 transition flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">
                                            {{ $order->tracking_number }}
                                        </span>
                                        <span class="text-xs font-bold text-slate-800">{{ $order->service_name }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-1 flex items-center space-x-2">
                                        <span>{{ $order->applicant_name }}</span>
                                        <span>&bull;</span>
                                        <span class="font-mono">{{ $order->applicant_phone }}</span>
                                        <span>&bull;</span>
                                        <span class="font-bold text-slate-700">PKR {{ number_format($order->fee_amount, 0) }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 shrink-0">
                                    @if($order->status === 'submitted')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Submitted</span>
                                    @elseif($order->status === 'in_review')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">In Review</span>
                                    @elseif($order->status === 'completed')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Completed ✓</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">{{ ucfirst($order->status) }}</span>
                                    @endif

                                    <a href="{{ route('admin.orders.index', ['search' => $order->tracking_number]) }}">
                                        <button class="px-2.5 py-1 text-xs font-semibold bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 rounded-lg transition">
                                            Manage
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right: Urgent Pending Call Requests -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Urgent Call Requests & Inquiries</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Leads awaiting consultant call or WhatsApp</p>
                    </div>
                    <a href="{{ route('admin.callbacks.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                        View All ({{ $callbacks->count() }}) &rarr;
                    </a>
                </div>

                @if($callbacks->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Koi pending call request nahi hai.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($callbacks->take(5) as $cb)
                            <div class="p-4 hover:bg-slate-50/70 transition flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold text-slate-900">{{ $cb->name }}</span>
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">
                                            {{ $cb->service_name ?? 'Consultation' }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-1">
                                        Time: <span class="font-medium text-slate-700">{{ $cb->preferred_time ?? 'Anytime' }}</span>
                                        @if($cb->notes)
                                            &bull; <span class="italic text-slate-400 truncate max-w-xs inline-block align-bottom">{{ $cb->notes }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 shrink-0">
                                    <a
                                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cb->phone) }}?text=Assalam%20o%20Alaikum%20{{ urlencode($cb->name) }},%20TaxFiler%20consultancy%20ki%20taraf%20se%20rabta%20kar%20rahe%20hain."
                                        target="_blank"
                                        class="px-2 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-lg transition font-mono tabular-nums"
                                    >
                                        💬 {{ $cb->phone }}
                                    </a>

                                    <a href="{{ route('admin.callbacks.index', ['search' => $cb->phone]) }}">
                                        <button class="px-2 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                            Log
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
