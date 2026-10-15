<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.guest'" header="Application Tracker" title="Track Application & Return Status - TaxFiler.pk">
    <div class="max-w-4xl mx-auto space-y-8 py-4">
        @auth
            <div class="flex items-center justify-between text-xs font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1">
                    <span>&larr; Back to Dashboard</span>
                </a>
                <a href="{{ route('client.applications') }}" class="text-blue-600 font-semibold hover:underline">
                    View in My Applications Portal &rarr;
                </a>
            </div>
        @endauth

        @if(auth()->check() && isset($myRequests) && $myRequests->isNotEmpty())
            <!-- Logged-in User's Active Applications -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">My Applications &amp; Orders</h2>
                        <p class="text-xs text-slate-500">Live status of your submitted corporate and NTN files</p>
                    </div>
                    <a href="{{ route('services.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 transition">
                        + New Application
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($myRequests as $req)
                        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-900">{{ $req->service_name }}</span>
                                    <span class="font-mono text-xs text-slate-500">#{{ $req->tracking_number }}</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Applied: {{ $req->created_at->format('d M Y, h:i A') }}
                                    @if($req->admin_notes)
                                        &middot; <span class="text-blue-700 font-medium">Note: {{ $req->admin_notes }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <x-ui.status-badge :status="$req->payment_status" />
                                <x-ui.status-badge :status="$req->status" />
                                @if($req->delivered_file_path)
                                    <a href="{{ route('services.download', $req) }}" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                        Download File 📥
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Header & Search Box -->
        <div class="text-center space-y-3">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                <span>📍</span>
                <span>Secure Application Tracker</span>
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Track Your Tax Return & Corporate Application
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto">
                Apna <strong>Application Tracking #</strong> aur registered mobile number ke <strong>aakhri 4 digits</strong> darj karein.
            </p>
        </div>

        <!-- Search Form Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-3">
            <form method="GET" action="{{ route('track') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                    <div class="sm:col-span-7">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Application Tracking #</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ $query }}"
                            required
                            placeholder="e.g. 9BK7M2Q8X"
                            autocomplete="off"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-mono uppercase outline-none transition"
                        />
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone (Last 4 Digits)</label>
                        <input
                            type="text"
                            name="verify"
                            value="{{ $verify ?? '' }}"
                            required
                            maxlength="4"
                            pattern="[0-9]{4}"
                            placeholder="e.g. 5678"
                            autocomplete="off"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-mono text-center outline-none transition"
                        />
                    </div>
                    <div class="sm:col-span-2 flex items-end">
                        <button
                            type="submit"
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium rounded-xl text-sm transition shadow-sm flex items-center justify-center space-x-1 cursor-pointer"
                        >
                            <span>Track</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-400 gap-2 pt-2 border-t border-slate-100">
                    <span class="flex items-center space-x-1">
                        <span>🔒</span>
                        <span>Privacy Protected: Phone verification required</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <span>⚡</span>
                        <span>Real-time status updates</span>
                    </span>
                </div>
            </form>
        </div>

        <!-- Server-Rendered Result (if loaded with ?q= and ?verify=) -->
        <div>
            @if($searched && $result && $type === 'service_request')
                @php $req = $result; @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-3">
                        <div>
                            <span class="text-xs font-mono text-blue-600 font-bold uppercase tracking-wider">Application Tracking # {{ $req->tracking_number }}</span>
                            <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $req->service_name }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Applicant: <strong class="text-slate-700">{{ $req->applicant_name }}</strong></p>
                        </div>
                        <div>
                            @if($req->status === 'submitted')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Submitted</span>
                            @elseif($req->status === 'in_review')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">In Review</span>
                            @elseif($req->status === 'completed')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Completed</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($req->status) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Stepper -->
                    <div class="py-4">
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold mb-1">✓</div>
                                <span class="font-bold text-slate-800">Submitted</span>
                                <span class="text-[10px] text-slate-400">{{ $req->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full {{ in_array($req->status, ['in_review', 'completed']) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-bold mb-1">
                                    {{ in_array($req->status, ['in_review', 'completed']) ? '✓' : '2' }}
                                </div>
                                <span class="font-bold text-slate-800">In Review</span>
                                <span class="text-[10px] text-slate-400">Verifying</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full {{ $req->status === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-bold mb-1">
                                    {{ $req->status === 'completed' ? '✓' : '3' }}
                                </div>
                                <span class="font-bold text-slate-800">Completed</span>
                                <span class="text-[10px] text-slate-400">Certificate</span>
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block">Service Fee</span>
                            <span class="font-bold text-slate-800 font-mono tabular-nums">PKR {{ number_format($req->fee_amount, 0) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Payment</span>
                            <span class="font-bold uppercase text-[11px] {{ $req->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $req->payment_status === 'paid' ? 'Paid ✓' : 'Unpaid' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Application Date</span>
                            <span class="text-slate-700">{{ $req->created_at->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Reference No</span>
                            <span class="font-mono text-slate-700">{{ $req->delivered_reference_no ?? 'Under Process' }}</span>
                        </div>
                    </div>
                </div>
            @elseif($searched)
                <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-6 text-center text-sm font-semibold space-y-1">
                    <p class="text-base font-bold text-rose-900">Record nahi mila ya verification ghalat hai.</p>
                    <p class="text-xs text-rose-600 font-normal">Barah-e-karam apna Application Tracking # aur registered mobile number ke aakhri 4 digits dobara check karein.</p>
                </div>
            @endif
        </div>

        <!-- Helpful Assistant / WhatsApp Helpline Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-[#25D366] flex items-center justify-center text-xl flex-shrink-0">
                    💬
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white">Need help finding your application?</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Our certified tax advocate helpline is available via WhatsApp for immediate lookup.</p>
                </div>
            </div>
            <a
                href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20apna%20tax%20return%20ya%20service%20status%20track%20karne%20mein%20madad%20chahiye."
                target="_blank"
                class="px-4 py-2.5 bg-[#25D366] hover:bg-[#1ebd59] text-slate-950 font-bold rounded-xl text-xs transition flex items-center space-x-1.5 flex-shrink-0 cursor-pointer"
            >
                <span>Chat on WhatsApp</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</x-dynamic-component>
