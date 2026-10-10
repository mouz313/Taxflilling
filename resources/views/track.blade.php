<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.guest'" header="Application Tracker" title="Track Application & Return Status - TaxFiler.pk">
    <div
        class="max-w-4xl mx-auto space-y-8 py-4"
        x-data="{
            searchQuery: '{{ $query }}',
            results: [],
            loading: false,
            hasSearched: false,
            activeResult: null,
            searchAjax() {
                const q = this.searchQuery.trim();
                if (q.length < 2) {
                    this.results = [];
                    this.loading = false;
                    this.hasSearched = false;
                    return;
                }
                this.loading = true;
                fetch('{{ route('track.search') }}?q=' + encodeURIComponent(q))
                    .then(res => res.json())
                    .then(data => {
                        this.results = data.results || [];
                        this.loading = false;
                        this.hasSearched = true;
                        if (this.results.length === 1) {
                            this.activeResult = this.results[0];
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },
            clearSearch() {
                this.searchQuery = '';
                this.results = [];
                this.hasSearched = false;
                this.activeResult = null;
            }
        }"
        x-init="if (searchQuery.trim().length >= 2) { searchAjax(); }"
    >
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
                <span>Live Real-Time Status Tracker</span>
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Track Your Tax Return or Corporate Application
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto">
                Real-time AJAX search by <strong>Application # (9-digit)</strong>, <strong>Name</strong>, <strong>Email</strong>, <strong>Phone</strong>, or <strong>CNIC</strong>.
            </p>
        </div>

        <!-- Search Form Card with Real-time AJAX on-type listener -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-3">
            <form method="GET" action="{{ route('track') }}" @submit.prevent="searchAjax()" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <template x-if="!loading">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                            <template x-if="loading">
                                <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                        </div>
                        <input
                            type="text"
                            name="query"
                            x-model="searchQuery"
                            @input.debounce.250ms="searchAjax()"
                            placeholder="Type Application # (e.g. 9BK7M2Q8X), Name, Phone, or Email..."
                            autocomplete="off"
                            class="w-full pl-11 pr-10 py-3 text-sm rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-mono outline-none transition"
                        />
                        <button
                            type="button"
                            x-show="searchQuery.length > 0"
                            @click="clearSearch()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"
                        >
                            &times;
                        </button>
                    </div>
                    <button
                        type="button"
                        @click="searchAjax()"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium rounded-xl text-sm transition shadow-sm flex items-center justify-center space-x-2 cursor-pointer flex-shrink-0"
                    >
                        <span>Search Live</span>
                        <span>&rarr;</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-400 gap-2">
                    <span class="flex items-center space-x-1">
                        <span>⚡</span>
                        <span>Real-time typing search enabled (Type 2+ characters to search)</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <span>🔒</span>
                        <span>100% Secure FBR &amp; Corporate Verification</span>
                    </span>
                </div>
            </form>
        </div>

        <!-- Real-Time AJAX Results Container -->
        <div x-show="hasSearched" x-cloak class="space-y-4">
            <!-- Results Count Bar -->
            <div class="flex items-center justify-between px-2 text-xs text-slate-600">
                <div>
                    Found <strong class="text-slate-900" x-text="results.length"></strong> matching application(s) for "<span class="font-mono text-blue-600 font-bold" x-text="searchQuery"></span>"
                </div>
                <div x-show="loading" class="text-blue-600 font-semibold flex items-center space-x-1">
                    <span class="animate-pulse">Searching live database...</span>
                </div>
            </div>

            <!-- List of Matching Cards -->
            <template x-for="item in results" :key="item.type + '_' + item.id">
                <div
                    @click="activeResult = item"
                    class="bg-white rounded-2xl border p-5 sm:p-6 shadow-sm hover:shadow-md transition cursor-pointer space-y-4"
                    :class="activeResult?.tracking_number === item.tracking_number ? 'border-blue-500 ring-2 ring-blue-100' : 'border-slate-200'"
                >
                    <!-- Card Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
                                <template x-if="item.type === 'service_request'"><span>💼</span></template>
                                <template x-if="item.type === 'tax_return'"><span>📑</span></template>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-xs bg-slate-100 text-blue-700 px-2 py-0.5 rounded border border-slate-200" x-text="'#' + item.tracking_number"></span>
                                    <h3 class="text-base font-bold text-slate-900" x-text="item.service_name"></h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Applicant: <strong class="text-slate-700" x-text="item.applicant_name"></strong>
                                    &bull; Phone: <span class="font-mono" x-text="item.applicant_phone"></span>
                                    &bull; Email: <span class="text-slate-600" x-text="item.applicant_email"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-bold"
                                :class="{
                                    'bg-amber-100 text-amber-800': item.status === 'submitted' || item.status === 'draft',
                                    'bg-blue-100 text-blue-800': item.status === 'in_review',
                                    'bg-emerald-100 text-emerald-800': item.status === 'completed' || item.status === 'filed',
                                    'bg-rose-100 text-rose-800': item.status === 'cancelled'
                                }"
                                x-text="item.status_label"
                            ></span>
                        </div>
                    </div>

                    <!-- 3-Stage Progress Timeline -->
                    <div class="py-2">
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <!-- Stage 1: Submitted -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center font-bold mb-1 transition"
                                    :class="item.status !== 'cancelled' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500'"
                                >
                                    ✓
                                </div>
                                <span class="font-bold text-slate-800">1. Submitted</span>
                                <span class="text-[10px] text-slate-400" x-text="item.created_at"></span>
                            </div>

                            <!-- Stage 2: In Review -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center font-bold mb-1 transition"
                                    :class="{
                                        'bg-emerald-600 text-white': item.status === 'completed' || item.status === 'filed',
                                        'bg-blue-600 text-white ring-4 ring-blue-100': item.status === 'in_review',
                                        'bg-slate-100 text-slate-400 border border-slate-200': item.status === 'submitted' || item.status === 'draft' || item.status === 'cancelled'
                                    }"
                                >
                                    <template x-if="item.status === 'completed' || item.status === 'filed'"><span>✓</span></template>
                                    <template x-if="item.status !== 'completed' && item.status !== 'filed'"><span>2</span></template>
                                </div>
                                <span class="font-bold text-slate-800">2. In Review</span>
                                <span class="text-[10px] text-slate-400">Consultant verifying</span>
                            </div>

                            <!-- Stage 3: Completed -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center font-bold mb-1 transition"
                                    :class="(item.status === 'completed' || item.status === 'filed') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200'"
                                >
                                    <template x-if="item.status === 'completed' || item.status === 'filed'"><span>✓</span></template>
                                    <template x-if="item.status !== 'completed' && item.status !== 'filed'"><span>3</span></template>
                                </div>
                                <span class="font-bold text-slate-800">3. Completed</span>
                                <span class="text-[10px] text-slate-400">License / Certificate</span>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Fee Amount</span>
                            <span class="font-bold font-mono text-slate-900" x-text="'PKR ' + Number(item.fee_amount).toLocaleString()"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Payment Status</span>
                            <span
                                class="font-bold uppercase text-[10px]"
                                :class="item.payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700'"
                                x-text="item.payment_status === 'paid' ? 'Paid ✓' : 'Unpaid ⏳'"
                            ></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Application Date</span>
                            <span class="text-slate-700" x-text="item.created_at"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Reference / FBR Slip</span>
                            <span class="font-mono text-slate-700" x-text="item.delivered_reference_no || item.iris_acknowledgement_no || 'Under Process'"></span>
                        </div>
                    </div>

                    <!-- Admin Notes if present -->
                    <template x-if="item.admin_notes">
                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-blue-900">
                            <strong>Note from Advocate / Consultant:</strong>
                            <p class="mt-0.5" x-text="item.admin_notes"></p>
                        </div>
                    </template>

                    <!-- Deliverable Document Download Button -->
                    <template x-if="item.has_delivered_file">
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-emerald-700">Official Certificate Issued ✓</span>
                            <a
                                :href="item.download_url"
                                class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition"
                            >
                                <span>📥 Download Certificate</span>
                            </a>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Zero Results Message -->
            <div x-show="results.length === 0 && !loading" class="bg-white rounded-2xl border border-slate-200 p-8 text-center space-y-3">
                <div class="text-3xl">🔍</div>
                <h3 class="text-sm font-bold text-slate-800">No Application Found</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    "<span class="font-mono font-bold text-slate-700" x-text="searchQuery"></span>" se koi record nahi mila. Barah-e-karam apna 9-digit application number, registered phone number ya email check karein.
                </p>
            </div>
        </div>

        <!-- Initial Server-Rendered Result (if loaded with ?query=) -->
        <div x-show="!hasSearched">
            @if($searched && $result && $type === 'service_request')
                @php $req = $result; @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-3">
                        <div>
                            <span class="text-xs font-mono text-blue-600 font-bold uppercase tracking-wider">Application Tracking # {{ $req->tracking_number }}</span>
                            <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $req->service_name }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Applicant: <strong class="text-slate-700">{{ $req->applicant_name }}</strong> | Phone: <span class="font-mono">{{ $req->applicant_phone }}</span></p>
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
