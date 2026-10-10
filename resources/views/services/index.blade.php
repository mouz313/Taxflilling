<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.guest'" header="Business & Corporate Services" title="Business & Corporate Services - TaxFiler.pk">
    <div
        class="max-w-7xl mx-auto space-y-6"
        x-data="{
            activeTab: '{{ $tab }}',
            searchQuery: '',
            activeModal: null, // 'apply' or 'callback'
            selectedService: null,
            callbackService: null,
            services: {{ Js::from($catalog) }},
            cnicInput: '{{ (Auth::check() && Auth::user()->role === 'client' && Auth::user()->client) ? Auth::user()->client->cnic : '' }}',
            applicantNameInput: '{{ (Auth::check() && Auth::user()->role === 'client') ? Auth::user()->name : '' }}',
            isCheckingCnic: false,
            cnicCheckResult: null,
            cnicCheckTimeout: null,

            openApply(serviceCode) {
                this.selectedService = this.services[serviceCode];
                this.activeModal = 'apply';
                if (this.cnicInput && this.cnicInput.replace(/[^0-9]/g, '').length === 13) {
                    this.performCnicCheck(this.cnicInput);
                }
            },

            onCnicInput(val) {
                this.cnicInput = val;
                const clean = val.replace(/[^0-9]/g, '');
                if (clean.length < 13) {
                    this.cnicCheckResult = null;
                    return;
                }
                clearTimeout(this.cnicCheckTimeout);
                this.cnicCheckTimeout = setTimeout(() => {
                    this.performCnicCheck(val);
                }, 350);
            },

            async performCnicCheck(cnicVal) {
                if (!cnicVal) return;
                this.isCheckingCnic = true;
                try {
                    const res = await fetch('{{ route('services.check-cnic') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ cnic: cnicVal })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.cnicCheckResult = data;
                        if (!this.applicantNameInput && data.legal_name) {
                            this.applicantNameInput = data.legal_name;
                        }
                    } else {
                        this.cnicCheckResult = null;
                    }
                } catch (e) {
                    this.cnicCheckResult = null;
                } finally {
                    this.isCheckingCnic = false;
                }
            },

            openCallback(serviceCode) {
                this.callbackService = this.services[serviceCode] || { heading: 'General Corporate Consultation', code: 'general' };
                this.activeModal = 'callback';
            },

            closeModal() {
                this.activeModal = null;
            },

            filterMatches(service) {
                // Tab filter
                if (this.activeTab !== 'all' && service.category !== this.activeTab) {
                    return false;
                }
                // Search query filter
                if (this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase();
                    const titleMatch = service.heading.toLowerCase().includes(q);
                    const descMatch = (service.desc || '').toLowerCase().includes(q);
                    const catMatch = service.category_name.toLowerCase().includes(q);
                    return titleMatch || descMatch || catMatch;
                }
                return true;
            }
        }"
    >
        <!-- Breadcrumb & Page Heading -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 shadow-xs">
            <nav class="flex text-xs font-semibold text-gray-400 mb-3 space-x-2">
                <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="hover:text-blue-600 transition">
                    {{ auth()->check() ? 'Dashboard' : 'Home' }}
                </a>
                <span>/</span>
                <span class="text-blue-600">Business Services</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                        Our Business & Corporate Services
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1.5 max-w-2xl leading-relaxed">
                        Registering a business or handling tax compliance doesn't have to be stressful. Get certified consultant assistance for FBR, SECP, IPO, and USA LLC corporate setups.
                    </p>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0">
                    <button
                        type="button"
                        @click="openCallback('general')"
                        class="px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl text-xs border border-blue-200 transition shadow-xs flex items-center space-x-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>Request a Callback</span>
                    </button>
                    <a
                        href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20corporate%20tax%20service%20ke%20bare%20mein%20maloomat%20chahiye."
                        target="_blank"
                        class="px-4 py-2.5 bg-[#25D366] hover:bg-[#20ba59] text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center space-x-1.5"
                    >
                        <span>💬</span>
                        <span>WhatsApp Helpline</span>
                    </a>
                </div>
            </div>

            <!-- Categories Tabs Bar (Horizontal Scrollable) -->
            <div class="mt-6 pt-5 border-t border-gray-100">
                <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-none">
                    @foreach($categories as $catKey => $catInfo)
                        <button
                            type="button"
                            @click="activeTab = '{{ $catKey }}'"
                            :class="activeTab === '{{ $catKey }}'
                                ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30 ring-2 ring-blue-500/20'
                                : 'bg-gray-100 hover:bg-gray-200/80 text-gray-700 font-semibold'"
                            class="px-4 py-2.5 rounded-full text-xs whitespace-nowrap transition cursor-pointer flex items-center space-x-2 select-none"
                        >
                            <span class="text-sm">{{ $catInfo['icon'] }}</span>
                            <span>{{ $catInfo['name'] }}</span>
                            <span
                                :class="activeTab === '{{ $catKey }}' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'"
                                class="px-2 py-0.5 rounded-full text-[10px] font-black"
                            >
                                {{ $counts[$catKey] ?? 0 }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Grid: Left Services Cards (8 Cols) vs Right Sidebar (4 Cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- LEFT: Services Cards List -->
            <div class="lg:col-span-8 space-y-4">
                @foreach($catalog as $service)
                    <div
                        x-show="filterMatches(services['{{ $service['code'] }}'])"
                        x-transition
                        class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs hover:border-blue-200 hover:shadow-md transition duration-200"
                    >
                        <!-- Card Header -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-gray-100">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xl">{{ $service['icon'] }}</span>
                                    <h3 class="text-base font-bold text-gray-900 tracking-tight">{{ $service['heading'] }}</h3>
                                    <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                        {{ $service['category_name'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $service['desc'] }}</p>
                            </div>

                            <div class="text-left sm:text-right flex-shrink-0">
                                <div class="text-xs text-gray-400 font-medium">Official Fee:</div>
                                <div class="text-lg font-black text-blue-700 font-mono">
                                    {{ $service['currency'] }} {{ number_format($service['fee'], 0) }}
                                </div>
                            </div>
                        </div>

                        <!-- Card Body: Turnaround & Requirements -->
                        <div class="py-4">
                            <!-- Turnaround time -->
                            <div class="flex items-center justify-between text-xs mb-3">
                                <span class="font-semibold text-gray-700">Required Documents & Checklist:</span>
                                <span class="font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[11px] font-bold">
                                    ⏱ {{ $service['turnaround'] }}
                                </span>
                            </div>

                            <!-- Requirements bullet points -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-600">
                                @foreach($service['requirements'] as $req)
                                    <div class="flex items-start space-x-2">
                                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">✓</span>
                                        <span>{{ $req }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Card Footer: Actions (Apply Form vs Call/WhatsApp) -->
                        <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-2.5">
                            <div class="flex items-center space-x-2 w-full sm:w-auto">
                                <!-- Request for Call button -->
                                <button
                                    type="button"
                                    @click="openCallback('{{ $service['code'] }}')"
                                    class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-bold transition flex items-center justify-center space-x-1.5 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span>Request a Call</span>
                                </button>

                                <!-- Direct WhatsApp button -->
                                <a
                                    href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20{{ urlencode($service['heading']) }}%20ke%20bare%20mein%20maloomat%20chahiye."
                                    target="_blank"
                                    class="w-full sm:w-auto px-3 py-2 rounded-xl border border-[#25D366]/40 bg-[#25D366]/5 hover:bg-[#25D366]/10 text-[#128c54] text-xs font-bold transition flex items-center justify-center space-x-1"
                                >
                                    <span>💬</span>
                                    <span>WhatsApp</span>
                                </a>
                            </div>

                            <!-- Primary: Apply / Proceed to Form -->
                            <button
                                type="button"
                                @click="openApply('{{ $service['code'] }}')"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center space-x-1.5 cursor-pointer"
                            >
                                <span>Apply Online (Fill Form)</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>
                @endforeach

                <!-- Empty State if search finds nothing -->
                <div
                    x-show="services && Object.values(services).filter(s => filterMatches(s)).length === 0"
                    x-cloak
                    class="bg-white rounded-2xl border border-gray-100 p-12 text-center"
                >
                    <div class="text-4xl mb-3">🔍</div>
                    <h4 class="text-base font-bold text-gray-800">Koi Service Match Nahi Hui</h4>
                    <p class="text-xs text-gray-400 mt-1">Apna search keyword tabdeel karein ya "All Services" tab muntakhab karein.</p>
                </div>
            </div>

            <!-- RIGHT: Sticky Sidebar (Search & Top Picks) -->
            <div class="lg:col-span-4 space-y-5 sticky top-6">
                <!-- Search Box -->
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Search Services</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="e.g. NTN, SECP, Trademark, USA LLC..."
                            class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs outline-none transition"
                        />
                        <button
                            type="button"
                            x-show="searchQuery.length > 0"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 text-xs"
                        >
                            &times;
                        </button>
                    </div>
                </div>

                <!-- TOP PICKS Card -->
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-xs">
                    <div class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider flex items-center space-x-1.5">
                        <span>🔥</span>
                        <span>TOP PICKS (Most Popular)</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($catalog as $pick)
                            @if(!empty($pick['popular']))
                                <div
                                    @click="openApply('{{ $pick['code'] }}')"
                                    class="p-3.5 hover:bg-gray-50 transition cursor-pointer flex items-center justify-between"
                                >
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-lg">{{ $pick['icon'] }}</span>
                                        <div>
                                            <div class="text-xs font-bold text-gray-900">{{ $pick['heading'] }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $pick['turnaround'] }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-blue-600 font-mono">{{ $pick['currency'] }} {{ number_format($pick['fee'], 0) }}</span>
                                        <span class="block text-[10px] text-blue-600">&rarr;</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Live Consultant Help Banner -->
                <div class="bg-gradient-to-br from-blue-50 via-teal-50 to-slate-50 border border-blue-200/60 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            🎧
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900">Need Personalized Guidance?</h4>
                            <p class="text-[11px] text-gray-500">Call our certified tax lawyers directly</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        If you are unsure whether to form an AOP, SMC, or Pvt Ltd company, our consultants offer free 10-minute strategic consultation.
                    </p>
                    <button
                        type="button"
                        @click="openCallback('general')"
                        class="w-full py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-sm"
                    >
                        Request Instant Callback &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 1: DYNAMIC SERVICE APPLICATION FORM (INTAKE FORM)                   -->
        <!-- ========================================================================= -->
        <div
            x-show="activeModal === 'apply'"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div
                @click.away="closeModal()"
                class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto"
            >
                <div class="flex items-start justify-between pb-4 border-b border-gray-100 mb-5">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl" x-text="selectedService?.icon"></span>
                        <div>
                            <h3 class="text-lg font-black text-gray-900" x-text="selectedService?.heading"></h3>
                            <div class="text-xs text-blue-600 font-bold font-mono">
                                Fee: <span x-text="selectedService?.currency"></span> <span x-text="Number(selectedService?.fee).toLocaleString()"></span>
                                • <span class="text-gray-500" x-text="selectedService?.turnaround"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">
                        &times;
                    </button>
                </div>

                <form method="POST" action="{{ route('services.apply') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="service_code" :value="selectedService?.code">

                    <!-- Applicant Contact Section -->
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Applicant Contact Info</h4>
                            @if(Auth::check() && Auth::user()->role === 'client')
                                <span class="text-[10px] bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full font-semibold border border-blue-200 flex items-center space-x-1">
                                    <span>👤</span>
                                    <span>Logged in as {{ Auth::user()->name }}</span>
                                </span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Full Name *</label>
                                <input
                                    type="text"
                                    name="applicant_name"
                                    x-model="applicantNameInput"
                                    required
                                    placeholder="Muhammad Ali"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">WhatsApp / Phone *</label>
                                <input
                                    type="text"
                                    name="applicant_phone"
                                    value="{{ (Auth::check() && Auth::user()->role === 'client') ? Auth::user()->phone : '' }}"
                                    required
                                    placeholder="03001234567"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Email Address *</label>
                                <input
                                    type="email"
                                    name="applicant_email"
                                    value="{{ (Auth::check() && Auth::user()->role === 'client') ? Auth::user()->email : '' }}"
                                    required
                                    placeholder="ali@example.com"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1 flex items-center justify-between">
                                    <span>CNIC / NTN Number *</span>
                                    <span class="text-[9px] text-blue-600 font-semibold flex items-center space-x-1">
                                        <span x-show="isCheckingCnic" class="animate-spin inline-block">⏳</span>
                                        <span>FBR IRIS Live Check</span>
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="applicant_cnic"
                                    x-model="cnicInput"
                                    @input="onCnicInput($event.target.value)"
                                    required
                                    placeholder="42101-1234567-1"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white font-mono"
                                />
                            </div>
                        </div>

                        <!-- LIVE FBR IRIS VERIFICATION BADGE (SHOWN BEFORE SUBMITTING) -->
                        <div x-show="isCheckingCnic" x-cloak class="p-2.5 rounded-xl bg-blue-50 border border-blue-100 flex items-center space-x-2 text-xs text-blue-700 animate-pulse">
                            <span class="text-sm">🔍</span>
                            <span>FBR IRIS Database se CNIC verify ho raha hai...</span>
                        </div>

                        <div x-show="cnicCheckResult" x-cloak class="p-3.5 rounded-xl border text-xs space-y-2 transition shadow-2xs"
                             :class="cnicCheckResult?.is_active ? 'bg-emerald-50/90 border-emerald-200' : 'bg-amber-50/90 border-amber-200'">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-1.5">
                                    <span class="text-base">🏛️</span>
                                    <span class="font-bold uppercase tracking-wider text-[11px]" :class="cnicCheckResult?.is_active ? 'text-emerald-950' : 'text-amber-950'">
                                        FBR IRIS Live Taxpayer Status
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] shadow-2xs font-mono"
                                      :class="cnicCheckResult?.is_active ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
                                      x-text="cnicCheckResult?.is_active ? '✓ ACTIVE (Filer)' : '✕ INACTIVE (Non-Filer)'">
                                </span>
                            </div>

                            <!-- Verified Data Grid -->
                            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1.5 border-t" :class="cnicCheckResult?.is_active ? 'border-emerald-200/70' : 'border-amber-200/70'">
                                <div>
                                    <span class="text-gray-500 block text-[10px]">FBR Legal Name:</span>
                                    <span class="font-bold text-gray-900" x-text="cnicCheckResult?.legal_name || 'Individual Taxpayer'"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block text-[10px]">NTN Number:</span>
                                    <span class="font-mono font-bold text-blue-700" x-text="cnicCheckResult?.ntn || 'Fresh Registration Required'"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block text-[10px]">Surcharge Status:</span>
                                    <span class="font-bold" :class="cnicCheckResult?.is_active ? 'text-emerald-700' : 'text-rose-700'" x-text="cnicCheckResult?.surcharge_status || (cnicCheckResult?.is_active ? 'Paid & Cleared ✓' : 'Surcharge Unpaid')"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block text-[10px]">Assigned RTO:</span>
                                    <span class="font-medium text-gray-800" x-text="cnicCheckResult?.assigned_rto || 'Regional Tax Office'"></span>
                                </div>
                            </div>

                            <!-- Live Advisory Callout -->
                            <div class="text-[10px] p-2 rounded-lg font-medium leading-relaxed" :class="cnicCheckResult?.is_active ? 'bg-emerald-100/70 text-emerald-900' : 'bg-amber-100/70 text-amber-950'">
                                <template x-if="cnicCheckResult?.is_active">
                                    <span>✓ <strong>Filer Record Verified:</strong> Aapka tax status active hai aur surcharge paid hai. Normal withholding tax rates apply honge.</span>
                                </template>
                                <template x-if="!cnicCheckResult?.is_active">
                                    <span>⚠️ <strong>Non-Filer Alert:</strong> Yeh CNIC FBR par Inactive / Non-Filer show ho raha hai. Banking aur properties par double withholding tax se bachne ke liye application proceed karein.</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Service Specific Questions / Form Fields -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Service Specific Details</h4>

                        <template x-for="(field, index) in (selectedService?.form_fields || [])" :key="index">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    <span x-text="field.label"></span>
                                    <span x-show="field.required" class="text-rose-500">*</span>
                                </label>

                                <!-- Text Input -->
                                <template x-if="field.type === 'text' || field.type === 'number'">
                                    <input
                                        :type="field.type"
                                        :name="'fields[' + field.name + ']'"
                                        :placeholder="field.placeholder || ''"
                                        :required="field.required"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-200 outline-none"
                                    />
                                </template>

                                <!-- Textarea Input -->
                                <template x-if="field.type === 'textarea'">
                                    <textarea
                                        :name="'fields[' + field.name + ']'"
                                        rows="2"
                                        :placeholder="field.placeholder || ''"
                                        :required="field.required"
                                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-200 outline-none"
                                    ></textarea>
                                </template>

                                <!-- Select Dropdown -->
                                <template x-if="field.type === 'select'">
                                    <select
                                        :name="'fields[' + field.name + ']'"
                                        :required="field.required"
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                                    >
                                        <option value="">-- Muntakhab Karein --</option>
                                        <template x-for="opt in field.options" :key="opt">
                                            <option :value="opt" x-text="opt"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="pt-3">
                        <button
                            type="submit"
                            class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-600/20 cursor-pointer flex items-center justify-center space-x-2"
                        >
                            <span>Submit Application & Proceed</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 2: REQUEST FOR CALL (CALLBACK INQUIRY)                              -->
        <!-- ========================================================================= -->
        <div
            x-show="activeModal === 'callback'"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div
                @click.away="closeModal()"
                class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-gray-100"
            >
                <div class="flex items-start justify-between pb-3 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Request a Free Callback</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="'Service: ' + (callbackService?.heading || 'General Advisory')"></p>
                    </div>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">
                        &times;
                    </button>
                </div>

                <form method="POST" action="{{ route('services.callback') }}" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="service_code" :value="callbackService?.code">
                    <input type="hidden" name="service_name" :value="callbackService?.heading">

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Your Full Name *</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ (Auth::check() && Auth::user()->role === 'client') ? Auth::user()->name : '' }}"
                            required
                            placeholder="Muhammad Ali"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Mobile / WhatsApp Number *</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ (Auth::check() && Auth::user()->role === 'client') ? Auth::user()->phone : '' }}"
                            required
                            placeholder="03001234567"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address (Optional)</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ (Auth::check() && Auth::user()->role === 'client') ? Auth::user()->email : '' }}"
                            placeholder="ali@example.com"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Preferred Time for Call *</label>
                        <select
                            name="preferred_time"
                            required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-white"
                        >
                            <option value="anytime">Anytime during business hours</option>
                            <option value="morning">Morning (9:00 AM - 1:00 PM)</option>
                            <option value="afternoon">Afternoon (1:00 PM - 5:00 PM)</option>
                            <option value="evening">Evening (5:00 PM - 8:00 PM)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Brief Question or Requirement</label>
                        <textarea
                            name="notes"
                            rows="2"
                            placeholder="Apna masla ya sawal yahan darj karein..."
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 focus:border-blue-500 outline-none"
                        ></textarea>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-600/20 cursor-pointer"
                        >
                            Confirm Callback Request &rarr;
                        </button>
                    </div>

                    <!-- Direct WhatsApp Alternative -->
                    <div class="text-center pt-2">
                        <a
                            href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20immediate%20consultation%20chahiye."
                            target="_blank"
                            class="text-xs text-[#128c54] font-bold hover:underline inline-flex items-center space-x-1"
                        >
                            <span>💬 Or Chat Directly on WhatsApp Now &rarr;</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dynamic-component>
