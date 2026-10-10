<x-layouts.app header="My Tax Returns">
    <div class="space-y-6">

        {{-- 1. Top Header & Action Row --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">My Tax Returns</h2>
                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800">
                        {{ $returns->count() }} {{ Str::plural('Record', $returns->count()) }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Aap ke tamam saalana income tax returns ka mukammal record, status aur official dossier downloads.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <form method="POST" action="{{ route('client.returns.start') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-xs transition cursor-pointer">
                        <span>+ Start New Return</span>
                        <span>&rarr;</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- 2. Highlights Metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Current Tax Year</div>
                <div class="mt-1 text-base sm:text-lg font-bold text-slate-900">
                    {{ $activeYear?->name ?? 'Tax Year 2026-2027' }}
                </div>
                <div class="mt-1 text-[11px] text-emerald-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Filing Window Open
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Filings</div>
                <div class="mt-1 text-base sm:text-lg font-bold text-slate-900">
                    {{ $returns->count() }}
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    {{ $returns->where('status', 'filed')->count() }} Filed &bull; {{ $returns->where('status', 'draft')->count() }} Draft
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Latest Filing Status</div>
                <div class="mt-1">
                    @if($returns->isNotEmpty())
                        <x-ui.status-badge :status="$returns->first()->status" />
                    @else
                        <span class="text-sm font-semibold text-slate-400">No Return Started</span>
                    @endif
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    {{ $returns->first()?->taxYear?->name ?? 'N/A' }}
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Taxpayer Profile</div>
                <div class="mt-1 text-base sm:text-lg font-bold text-slate-900 truncate">
                    {{ $client?->name ?? auth()->user()->name }}
                </div>
                <div class="mt-1 text-[11px] font-mono text-slate-500">
                    {{ $client?->masked_cnic ?? 'CNIC on File' }}
                </div>
            </div>
        </div>

        {{-- 3. Returns Listing --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Tax Return Filings Record</h3>
                    <p class="text-xs text-slate-500">IRIS FBR submission history and annual wealth reconciliations</p>
                </div>
            </div>

            @if($returns->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($returns as $return)
                        <div class="p-5 hover:bg-slate-50/50 transition">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                {{-- Left info --}}
                                <div class="space-y-2 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <h4 class="text-base font-bold text-slate-900">
                                            Tax Year {{ $return->taxYear?->name ?? '2026-2027' }}
                                        </h4>
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 uppercase">
                                            {{ $return->filing_type ?? 'Original' }} Return
                                        </span>
                                        <x-ui.status-badge :status="$return->status" />
                                        <x-ui.status-badge :status="$return->fee_status" />
                                    </div>

                                    <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500">
                                        <span>
                                            ID: <strong class="font-mono text-slate-700">#TR-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</strong>
                                        </span>
                                        @if($return->iris_acknowledgement_no)
                                            <span>
                                                IRIS CPR/Ack: <strong class="font-mono text-emerald-700">{{ $return->iris_acknowledgement_no }}</strong>
                                            </span>
                                        @endif
                                        <span>
                                            Created: <strong class="text-slate-700">{{ $return->created_at->format('d M Y') }}</strong>
                                        </span>
                                        <span>
                                            Updated: <strong class="text-slate-700">{{ $return->updated_at->diffForHumans() }}</strong>
                                        </span>
                                    </div>

                                    {{-- Financial summary snapshot --}}
                                    <div class="inline-flex flex-wrap items-center gap-3 pt-1 text-xs">
                                        <div class="rounded-lg bg-slate-100 px-2.5 py-1 text-slate-700">
                                            Taxable Income: <strong class="font-mono text-slate-900">PKR {{ number_format($return->taxable_income ?? 0, 0) }}</strong>
                                        </div>
                                        <div class="rounded-lg bg-slate-100 px-2.5 py-1 text-slate-700">
                                            Net Tax Payable: <strong class="font-mono {{ ($return->tax_payable ?? 0) > 0 ? 'text-amber-800' : 'text-slate-900' }}">PKR {{ number_format($return->tax_payable ?? 0, 0) }}</strong>
                                        </div>
                                        <div class="rounded-lg bg-slate-100 px-2.5 py-1 text-slate-700">
                                            Consultant Fee: <strong class="font-mono text-slate-900">PKR {{ number_format($return->fee_amount ?? 3000, 0) }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Actions --}}
                                <div class="flex flex-wrap items-center gap-2 shrink-0 pt-2 lg:pt-0">
                                    {{-- Draft actions --}}
                                    @if($return->status === 'draft')
                                        <form method="POST" action="{{ route('client.returns.destroy', $return) }}"
                                              onsubmit="return confirm('Kya aap is draft return ko delete karna chahte hain?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-600 hover:text-white transition shadow-2xs cursor-pointer" title="Delete Draft">
                                                <span>🗑️</span>
                                                <span>Delete Draft</span>
                                            </button>
                                        </form>

                                        <a href="{{ route('wizard.filing', $return) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition shadow-xs">
                                            <span>Resume Wizard</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @else
                                        {{-- Submitted / In Review / Filed actions --}}
                                        <a href="{{ route('returns.wizard', $return) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                            <span>View Details</span>
                                            <span>🔍</span>
                                        </a>

                                        @if($return->status === 'docs_pending')
                                            <a href="{{ route('wizard.filing', $return) }}"
                                               class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-amber-700 transition shadow-xs">
                                                <span>Upload Documents</span>
                                                <span>📄</span>
                                            </a>
                                        @endif

                                        <a href="{{ route('returns.pdf', $return) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-xs">
                                            <span>Download PDF Dossier</span>
                                            <span>📥</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="mx-auto w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-3">
                        📑
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Abhi tak koi return create nahi hua</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Tax Year {{ $activeYear?->name ?? '2026-2027' }} ka income tax return abhi shuru karein aur apna FBR active filer status hasil karein.
                    </p>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('client.returns.start') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-xs transition cursor-pointer">
                                <span>+ Shuru Karein (Tax Year {{ $activeYear?->name ?? '2026-2027' }})</span>
                                <span>&rarr;</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
