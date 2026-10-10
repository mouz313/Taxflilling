<x-layouts.app header="My Applications">
    <div class="space-y-6">

        {{-- 1. Top Header & Action Row --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Corporate &amp; NTN Applications</h2>
                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800">
                        {{ $services->count() }} {{ Str::plural('Application', $services->count()) }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    NTN Registration, Sales Tax (STRN), Company Incorporation aur USA LLC files ki live tracking aur deliverables.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('services.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-xs transition">
                    <span>+ Nayi Application</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        {{-- 2. Highlights Metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Applications</div>
                <div class="mt-1 text-base sm:text-lg font-bold text-slate-900">{{ $services->count() }}</div>
                <div class="mt-1 text-[11px] text-slate-500">Corporate &amp; Tax filings</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Under Review</div>
                <div class="mt-1 text-base sm:text-lg font-bold text-amber-700">
                    {{ $services->whereIn('status', ['submitted', 'in_review'])->count() }}
                </div>
                <div class="mt-1 text-[11px] text-amber-600 font-medium">Processing at FBR/SECP</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Completed &amp; Delivered</div>
                <div class="mt-1 text-base sm:text-lg font-bold text-emerald-700">
                    {{ $services->where('status', 'completed')->count() }}
                </div>
                <div class="mt-1 text-[11px] text-emerald-600 font-medium">Official Certificates Issued</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pending Payments</div>
                <div class="mt-1 text-base sm:text-lg font-bold text-slate-900">
                    {{ $services->where('payment_status', 'pending')->count() }}
                </div>
                <div class="mt-1 text-[11px] text-slate-500">Awaiting verification</div>
            </div>
        </div>

        {{-- 3. Applications List --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Submitted Applications &amp; Live Tracking</h3>
                    <p class="text-xs text-slate-500">Track processing status, consultant notes, and download official certificates</p>
                </div>
            </div>

            @if($services->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($services as $app)
                        <div class="p-5 hover:bg-slate-50/50 transition space-y-4">
                            {{-- Top Card Details --}}
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-base font-bold text-slate-900">{{ $app->service_name }}</h4>
                                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100">
                                            #{{ $app->tracking_number }}
                                        </span>
                                        <x-ui.status-badge :status="$app->status" />
                                        <x-ui.status-badge :status="$app->payment_status" />
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-1">
                                        <span>Applied: <strong class="text-slate-700">{{ $app->created_at->format('d M Y, h:i A') }}</strong></span>
                                        <span>Fee: <strong class="text-slate-900 font-mono">PKR {{ number_format($app->fee_amount, 0) }}</strong></span>
                                        @if($app->applicant_cnic)
                                            <span>CNIC: <strong class="text-slate-700 font-mono">{{ $app->applicant_cnic }}</strong></span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action Deliverable Download --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($app->delivered_file_path)
                                        <a href="{{ route('services.download', $app) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition">
                                            <span>Download Official Certificate</span>
                                            <span>📥</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Live Tracking Stepper Pipeline --}}
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Live Filing Timeline</div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    {{-- Step 1 --}}
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold shrink-0">✓</div>
                                        <div>
                                            <div class="font-bold text-slate-900">1. Applied</div>
                                            <div class="text-[10px] text-slate-500">{{ $app->created_at->format('d M') }}</div>
                                        </div>
                                    </div>

                                    {{-- Step 2 --}}
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full {{ $app->payment_status === 'paid' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }} flex items-center justify-center text-[10px] font-bold shrink-0">
                                            {{ $app->payment_status === 'paid' ? '✓' : '2' }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">2. Fee Status</div>
                                            <div class="text-[10px] {{ $app->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }} font-medium">
                                                {{ ucfirst($app->payment_status) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 3 --}}
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full {{ in_array($app->status, ['in_review', 'completed']) ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center text-[10px] font-bold shrink-0">
                                            {{ $app->status === 'completed' ? '✓' : '3' }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">3. Official Review</div>
                                            <div class="text-[10px] text-slate-500">
                                                {{ in_array($app->status, ['in_review', 'completed']) ? 'Govt Processing' : 'Pending' }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 4 --}}
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full {{ $app->status === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center text-[10px] font-bold shrink-0">
                                            {{ $app->status === 'completed' ? '✓' : '4' }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">4. Delivery</div>
                                            <div class="text-[10px] {{ $app->status === 'completed' ? 'text-emerald-700 font-bold' : 'text-slate-500' }}">
                                                {{ $app->status === 'completed' ? 'Delivered' : 'Pending Issuance' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Consultant Notes if any --}}
                            @if($app->admin_notes)
                                <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-3 text-xs text-blue-900 flex items-start gap-2">
                                    <span class="text-base">💬</span>
                                    <div>
                                        <strong>Consultant Remarks:</strong> {{ $app->admin_notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="mx-auto w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-3">
                        🏢
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Abhi tak koi application jama nahi karwayi gayi</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        FBR NTN registration, Sales Tax ya USA LLC incorporation ki darhwast dein aur humari team aap ka case process karegi.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('services.index') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-xs transition">
                            <span>Browse Corporate Services &rarr;</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
