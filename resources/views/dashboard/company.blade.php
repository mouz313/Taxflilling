@php
    $actionText = [
        'in_review'     => 'Review karein',
        'ready_to_file' => 'IRIS par file karein',
        'query'         => 'Client ke jawab ka intezar',
        'docs_pending'  => 'Documents ka intezar',
    ];
    $btn = 'inline-flex items-center rounded-lg px-3.5 py-2 text-sm font-medium transition';
@endphp

<x-layouts.app header="Dashboard">
    <div class="space-y-6">

        {{-- Header --}}
        {{-- Header with Search & Quick Actions --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Practice Dashboard</h1>
                <p class="text-xs sm:text-sm text-slate-500">{{ $consultancy->firm_name ?? $consultancy->name ?? config('app.name') }} &middot; Live Practice Hub</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Quick Global Search --}}
                <form method="GET" action="{{ route('returns.index') }}" class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none text-xs">🔍</span>
                    <input type="text" name="search" placeholder="Search Client or CNIC..."
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-200 outline-none bg-white transition shadow-2xs">
                </form>

                @if($years->isNotEmpty())
                    <form method="GET" action="{{ route('dashboard') }}">
                        <select name="year" onchange="this.form.submit()"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-2xs outline-none">
                            @foreach($years as $y)
                                <option value="{{ $y->id }}" @selected($year && $year->id === $y->id)>Tax Year {{ $y->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <a href="{{ route('clients.create') }}" class="{{ $btn }} border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 shadow-2xs text-xs">
                    + Add Client
                </a>
                <a href="{{ route('returns.create') }}" class="{{ $btn }} bg-blue-600 text-white hover:bg-blue-700 shadow-2xs text-xs">
                    + New Return
                </a>
            </div>
        </div>

        {{-- Deadline Alert --}}
        @if($daysLeft !== null && $daysLeft <= 30)
            <div class="rounded-xl border px-4 py-3 text-xs sm:text-sm shadow-2xs flex items-center justify-between {{ $daysLeft < 0 ? 'border-rose-200 bg-rose-50 text-rose-900' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
                <div class="flex items-center space-x-2">
                    <span class="text-base">{{ $daysLeft < 0 ? '⚠️' : '⏳' }}</span>
                    <div>
                        @if($daysLeft >= 0)
                            FBR Filing Deadline: <strong>{{ $deadline->format('d M Y') }}</strong> (<strong>{{ $daysLeft }} din baqi</strong>).
                        @else
                            FBR Filing Deadline ({{ $deadline->format('d M Y') }}) <strong>{{ abs($daysLeft) }} din pehle</strong> guzar chuki hai.
                        @endif
                        <span class="text-xs opacity-75 hidden sm:inline ml-1">(FBR extension ho to tax year settings update karein.)</span>
                    </div>
                </div>
                <a href="{{ route('settings.company') }}" class="text-xs font-semibold underline hover:no-underline shrink-0">Settings &rarr;</a>
            </div>
        @endif

        {{-- Stats Cards with Visual Indicators --}}
        @php
            $collectedTotal = (float)($money['collected_returns'] + $money['collected_services']);
            $grandTotalRevenue = $collectedTotal + (float)$money['outstanding'];
            $collectionRate = $grandTotalRevenue > 0 ? round(($collectedTotal / $grandTotalRevenue) * 100) : 100;
        @endphp

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            {{-- In Review --}}
            <a href="{{ route('returns.index', ['status' => 'in_review']) }}" class="rounded-xl border border-slate-200 bg-white p-4.5 hover:border-blue-400 hover:shadow-xs transition group">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Review ke liye</div>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">🔍</span>
                </div>
                <div class="mt-2 text-3xl font-bold tabular-nums text-slate-900 group-hover:text-blue-600 transition">{{ $stats['in_review'] }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Consultant verification pending</div>
            </a>

            {{-- Ready to File --}}
            <a href="{{ route('returns.index', ['status' => 'ready_to_file']) }}" class="rounded-xl border border-slate-200 bg-white p-4.5 hover:border-indigo-400 hover:shadow-xs transition group">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">File karne ko tayyar</div>
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">⚡</span>
                </div>
                <div class="mt-2 text-3xl font-bold tabular-nums text-indigo-700">{{ $stats['ready_to_file'] }}</div>
                <div class="mt-1 text-[11px] text-indigo-600/80">IRIS submission pending</div>
            </a>

            {{-- Waiting Client --}}
            <a href="{{ route('returns.index') }}" class="rounded-xl border border-slate-200 bg-white p-4.5 hover:border-amber-400 hover:shadow-xs transition group">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Client ka intezar</div>
                    <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">⏳</span>
                </div>
                <div class="mt-2 text-3xl font-bold tabular-nums text-amber-700">{{ $stats['waiting_client'] }}</div>
                <div class="mt-1 text-[11px] text-amber-600/80">Docs / Query reply pending</div>
            </a>

            {{-- Revenue & Collection Gauge --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4.5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Wasool Shuda Fee</div>
                    <span class="text-xs font-bold text-emerald-700 font-mono">{{ $collectionRate }}%</span>
                </div>
                <div class="mt-2 text-2xl font-bold tabular-nums text-slate-900">
                    PKR {{ number_format($collectedTotal, 0) }}
                </div>
                <!-- Progress bar -->
                <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $collectionRate }}%"></div>
                </div>
                <div class="mt-1 text-[11px] text-slate-400 flex justify-between">
                    <span>Baqi: PKR {{ number_format($money['outstanding'], 0) }}</span>
                    <span class="text-emerald-600 font-medium">Paid: {{ $stats['filed'] }} filed</span>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- Needs attention --}}
            <div class="rounded-xl border border-slate-200 bg-white lg:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Needs your attention</h2>
                    <a href="{{ route('returns.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Sab returns</a>
                </div>

                @forelse($attention as $ret)
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="truncate font-medium text-slate-900">{{ $ret->client?->name ?? 'Client' }}</span>
                                <x-ui.status-badge :status="$ret->status" />
                                @if($ret->unread_queries_count > 0)
                                    <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">
                                        {{ $ret->unread_queries_count }} naya message
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                {{ $ret->unread_queries_count > 0 ? 'Client ka naya message dekhein' : ($actionText[$ret->status] ?? '') }}
                                &middot; {{ $ret->taxYear?->name }} &middot; {{ $ret->updated_at->diffForHumans() }}
                            </div>
                        </div>
                        <a href="{{ route('returns.show', $ret) }}"
                           class="{{ $btn }} shrink-0 justify-center border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">Open</a>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-slate-500">
                        Abhi koi return action ka muntazir nahi hai.
                    </div>
                @endforelse
            </div>

            {{-- Right column --}}
            <div class="space-y-6">

                <div class="rounded-xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-slate-900">
                            Naye leads
                            @if($pendingLeads) <span class="ml-1 text-sm font-normal text-slate-500">({{ $pendingLeads }})</span> @endif
                        </h2>
                        <a href="{{ route('admin.callbacks.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Sab</a>
                    </div>
                    @forelse($leads as $cb)
                        @php $wa = preg_replace('/^0/', '92', preg_replace('/\D/', '', (string) $cb->phone)); @endphp
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 last:border-0">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-slate-900">{{ $cb->name }}</div>
                                <div class="truncate text-xs text-slate-500">{{ $cb->service_name ?? 'Consultation' }} &middot; {{ $cb->preferred_time ?? 'Anytime' }}</div>
                            </div>
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
                               class="shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-600 hover:text-white">WhatsApp</a>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-slate-500">Koi naya lead nahi.</div>
                    @endforelse
                </div>

                <div class="rounded-xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-slate-900">
                            Naye orders
                            @if($pendingOrders) <span class="ml-1 text-sm font-normal text-slate-500">({{ $pendingOrders }})</span> @endif
                        </h2>
                        <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Sab</a>
                    </div>
                    @forelse($orders as $order)
                        <a href="{{ route('admin.orders.index', ['search' => $order->tracking_number]) }}"
                           class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-slate-900">{{ $order->service_name }}</div>
                                <div class="truncate text-xs text-slate-500">{{ $order->applicant_name }} &middot; {{ $order->tracking_number }}</div>
                            </div>
                            <span class="shrink-0 text-xs font-medium tabular-nums text-slate-700">PKR {{ number_format($order->fee_amount, 0) }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-slate-500">Koi naya order nahi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
