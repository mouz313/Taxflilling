@php
    $stages = ['draft' => 'Draft', 'docs_pending' => 'Docs', 'in_review' => 'Review', 'ready_to_file' => 'Ready', 'filed' => 'Filed'];
    $order  = ['draft' => 0, 'docs_pending' => 1, 'in_review' => 2, 'query' => 2, 'ready_to_file' => 3, 'filed' => 4, 'closed' => 4];
    $idx    = $current ? ($order[$current->status] ?? 0) : 0;
    $primary = [
        'draft' => 'Return mukammal karein', 'docs_pending' => 'Documents upload karein',
        'query' => 'Sawal dekhein', 'in_review' => 'Return dekhein',
        'ready_to_file' => 'Return dekhein', 'filed' => 'Return dekhein', 'closed' => 'Return dekhein',
    ];
    $tones = [
        'orange' => 'border-orange-200 bg-orange-50',
        'amber'  => 'border-amber-200 bg-amber-50',
        'blue'   => 'border-blue-200 bg-blue-50',
        'slate'  => 'border-slate-200 bg-slate-50',
    ];
@endphp

<x-layouts.app header="Dashboard">
    <div class="space-y-6 max-w-7xl mx-auto">

        {{-- 1. Profile & Status Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center text-base shadow-xs flex-shrink-0">
                    {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Assalam o Alaikum, {{ auth()->user()->name }}</h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            Verified Taxpayer
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500 font-mono">
                        <span>CNIC: <strong>{{ $client?->masked_cnic ?? 'Not Registered' }}</strong></span>
                        @if($client?->ntn)
                            <span>&middot; NTN: <strong>{{ $client->ntn }}</strong></span>
                        @endif
                        <span>&middot; {{ str_starts_with($activeYear?->name ?? '', 'Tax Year') ? $activeYear?->name : 'Tax Year: ' . ($activeYear?->name ?? '2026') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($daysLeft !== null && (! $current || ! in_array($current->status, ['filed', 'closed'])))
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $daysLeft < 0 ? 'bg-rose-100 text-rose-800' : ($daysLeft <= 14 ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                        <span>⏳</span>
                        <span>{{ $daysLeft >= 0 ? "Filing Deadline: {$daysLeft} din baqi" : 'Deadline guzar chuki' }}</span>
                    </span>
                @endif

                <a href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20apne%20tax%20return%20ke%20hawaale%20se%20rabta%20karna%20hai."
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition shadow-2xs">
                    <span>💬</span>
                    <span>Tax Consultant WhatsApp</span>
                </a>
            </div>
        </div>

        {{-- 2. ACTIVE RETURN IN PROGRESS --}}
        @if($current)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs space-y-5">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs uppercase font-bold tracking-wider text-blue-600">Active Tax Return</span>
                            <span class="text-xs text-slate-400">&middot;</span>
                            <span class="text-xs font-mono text-slate-500">{{ str_starts_with($current->taxYear?->name ?? '', 'Tax Year') ? $current->taxYear?->name : 'Tax Year ' . ($current->taxYear?->name ?? '') }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">Income Tax Return 114(1)</h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            @if($current->net_tax_payable_or_refundable > 0)
                                Net Tax Payable: <span class="font-bold tabular-nums text-rose-600">PKR {{ number_format($current->net_tax_payable_or_refundable, 0) }}</span>
                            @elseif($current->net_tax_payable_or_refundable < 0)
                                Tax Refund Receivable: <span class="font-bold tabular-nums text-emerald-600">PKR {{ number_format(abs($current->net_tax_payable_or_refundable), 0) }}</span>
                            @else
                                Automated calculation in progress &middot; Tax: PKR 0
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ui.status-badge :status="$current->status" />
                    </div>
                </div>

                {{-- Progress Stepper --}}
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-2 flex items-center justify-between">
                        <span>Filing Pipeline Stages</span>
                        <span class="text-blue-600 font-bold font-mono">Stage {{ $idx + 1 }} of 5</span>
                    </div>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($stages as $key => $label)
                            @php $i = $loop->index; @endphp
                            <div>
                                <div class="h-2 rounded-full transition-all {{ $i < $idx ? 'bg-emerald-500' : ($i === $idx ? 'bg-blue-600 ring-2 ring-blue-200' : 'bg-slate-200') }}"></div>
                                <div class="mt-1.5 text-xs text-center {{ $i === $idx ? 'font-bold text-blue-700' : ($i < $idx ? 'text-emerald-700 font-medium' : 'text-slate-400') }}">
                                    {{ $label }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-slate-600 flex items-center gap-2">
                        <span>Advocate Processing Fee: <strong class="font-bold text-slate-900">PKR {{ number_format($current->fee_amount, 0) }}</strong></span>
                        <span class="text-slate-300">|</span>
                        <span class="font-semibold {{ $current->fee_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $current->fee_status === 'paid' ? '✓ Paid' : '⏳ Payment Pending' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($current->status === 'draft')
                            <form method="POST" action="{{ route('client.returns.destroy', $current) }}" onsubmit="return confirm('Kya aap is draft return application ko delete karna chahte hain?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex justify-center items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-600 hover:text-white transition shadow-2xs cursor-pointer" title="Delete Draft Application">
                                    <span>🗑️</span>
                                    <span>Delete Draft</span>
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('wizard.filing', $current) }}"
                           class="inline-flex justify-center items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 transition shadow-xs">
                            <span>{{ $primary[$current->status] ?? 'Return dekhein' }}</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- To-Do Queue --}}
            @if(count($todo))
                <div class="space-y-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Pending Actions Required</h3>
                    @foreach($todo as $item)
                        <div class="flex flex-col gap-3 rounded-2xl border p-4.5 sm:flex-row sm:items-center sm:justify-between shadow-2xs {{ $tones[$item['tone']] ?? $tones['slate'] }}">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-900">{{ $item['title'] }}</div>
                                <div class="mt-0.5 text-xs text-slate-600 leading-relaxed">{{ $item['body'] }}</div>
                            </div>
                            <a href="{{ $item['url'] }}"
                               class="inline-flex shrink-0 justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                {{ $item['cta'] }} &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Past Returns History --}}
            @if($returns->count() > 1)
                <div class="space-y-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Past Tax Returns</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($returns->skip(1) as $ret)
                            <a href="{{ route('wizard.filing', $ret) }}"
                               class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 hover:border-blue-400 hover:shadow-xs transition">
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">Tax Year {{ $ret->taxYear?->name }}</div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5">
                                        {{ $ret->iris_acknowledgement_no ? 'CPR #' . $ret->iris_acknowledgement_no : 'Draft' }}
                                    </div>
                                </div>
                                <x-ui.status-badge :status="$ret->status" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        {{-- 3. FRESH CLIENT STATE (NO ACTIVE RETURN YET) --}}
        @else
            {{-- High-Converting Hero Card --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-800 text-white p-7 sm:p-9 shadow-md">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl space-y-4">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-xs font-semibold text-white">
                        <span>⚡</span>
                        <span>FBR Tax Year {{ $activeYear?->name ?? '2026' }}-27 Filing Window is Active</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-snug">
                        Ready to File Your Pakistan Income Tax Return?
                    </h2>

                    <p class="text-xs sm:text-sm text-blue-100 leading-relaxed max-w-xl">
                        FBR Active Filer ban kar bank cash withdrawal, gari aur property par <strong>50% double withholding tax</strong> se bachain. Certified tax advocates aapka return audit-proof tayyar karenge.
                    </p>

                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('client.returns.start') }}">
                            @csrf
                            <button type="submit"
                                    class="px-6 py-3 bg-white text-blue-700 hover:bg-blue-50 font-black rounded-xl text-xs sm:text-sm shadow-md transition transform hover:scale-[1.02] flex items-center space-x-2 cursor-pointer">
                                <span>Start My Tax Return (6 Mins)</span>
                                <span>🚀</span>
                            </button>
                        </form>

                        <a href="{{ route('services.index') }}"
                           class="px-5 py-3 bg-blue-800/80 hover:bg-blue-800 text-white font-semibold rounded-xl text-xs sm:text-sm border border-blue-400/40 transition">
                            Explore Corporate Services &rarr;
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3 Fast-Track Action Cards --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Popular Filing &amp; Compliance Services</h3>
                    <span class="text-xs text-slate-500">100% FBR IRIS 2.0 Compliant</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- 1. Salaried Individual Return -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 hover:border-blue-400 hover:shadow-xs transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl p-2 rounded-xl bg-blue-50">💼</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Most Popular</span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900">Salaried Tax Return</h4>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Salary slip, employer tax deduction certificate aur instant 114(1) FBR receipt.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-slate-900">PKR 3,000</span>
                            <form method="POST" action="{{ route('client.returns.start') }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-2xs cursor-pointer">
                                    Start Now &rarr;
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- 2. Freelancer & Business Return -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 hover:border-blue-400 hover:shadow-xs transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl p-2 rounded-xl bg-indigo-50">💻</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">IT / Business</span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900">Freelancer &amp; Business</h4>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Sole proprietors, IT exporters, Upwork freelancers aur retail shop return.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-slate-900">PKR 5,000</span>
                            <form method="POST" action="{{ route('client.returns.start') }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-2xs cursor-pointer">
                                    Start Now &rarr;
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- 3. Fresh NTN Registration -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 hover:border-blue-400 hover:shadow-xs transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl p-2 rounded-xl bg-emerald-50">🆔</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">New Filer</span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900">Individual NTN Registration</h4>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                FBR IRIS par naya NTN number aur official 181 registration certificate.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-slate-900">PKR 1,500</span>
                            <a href="{{ route('services.index', ['tab' => 'income']) }}"
                               class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs">
                                Apply NTN &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 4. Corporate & NTN Applications Hub --}}
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50/60">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Corporate &amp; NTN Applications</h3>
                    <p class="text-xs text-slate-500">Aap ki tamam corporate darhwastain aur official deliverables</p>
                </div>
                <a href="{{ route('services.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition shadow-2xs">
                    <span>+ Nayi Application</span>
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($services as $app)
                    <div class="flex flex-col gap-2 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between hover:bg-slate-50/50 transition">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="truncate text-sm font-semibold text-slate-900">{{ $app->service_name }}</span>
                                <span class="font-mono text-xs text-slate-400">#{{ $app->tracking_number }}</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Fee: PKR {{ number_format($app->fee_amount, 0) }} &middot; {{ $app->created_at->format('d M Y') }}
                                @if($app->admin_notes)
                                    &middot; <span class="text-blue-700 font-medium">Consultant: {{ $app->admin_notes }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-ui.status-badge :status="$app->payment_status" />
                            <x-ui.status-badge :status="$app->status" />
                            @if($app->delivered_file_path)
                                <a href="{{ route('services.download', $app) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-2xs transition">
                                    <span>Download Certificate</span>
                                    <span>📥</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-xs sm:text-sm text-slate-500">
                        Abhi tak koi application jama nahi karwayi gayi.
                        <div class="mt-2">
                            <a href="{{ route('services.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Explore Services Catalog &rarr;</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-layouts.app>
