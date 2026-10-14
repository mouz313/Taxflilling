<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Primary Meta Tags -->
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="{{ $robotsMeta ?? 'index, follow' }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Open Graph / Facebook / WhatsApp Share Preview -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $ogTitle ?? $metaTitle }}">
    <meta property="og:description" content="{{ $ogDescription ?? $metaDescription }}">
    <meta property="og:site_name" content="{{ $firmName }}">
    <meta property="og:locale" content="en_PK">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="{{ $firmName }}">
    @endif

    <!-- Twitter Card -->
    <meta name="twitter:card" content="{{ $twitterCard ?? 'summary_large_image' }}">
    <meta name="twitter:site" content="{{ $twitterHandle ?? '@TaxFilerPk' }}">
    <meta name="twitter:url" content="{{ $canonicalUrl }}">
    <meta name="twitter:title" content="{{ $ogTitle ?? $metaTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? $metaDescription }}">
    @if(!empty($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    <!-- Schema.org JSON-LD Structured Data for Google -->
    <script type="application/ld+json">
    {!! json_encode($schemaGraph, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    <!-- Marketing Tracking & Search Console Verifications -->
    <x-marketing-head />

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        .font-urdu { font-family: 'Noto Nastaliq Urdu', serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white"
    x-data="{
        activeCategory: 'all',
        calcFreq: 'monthly',
        calcSalary: 150000,
        quickCnic: '',
        isCheckingAtl: false,
        atlResult: null,
        atlError: null,
        callbackModalOpen: false,
        selectedService: null,

        calculateAnnualGross() {
            let s = parseFloat(this.calcSalary) || 0;
            return this.calcFreq === 'monthly' ? s * 12 : s;
        },
        calculateAnnualTax() {
            let gross = this.calculateAnnualGross();
            if (gross <= 600000) return 0;
            if (gross <= 1200000) return (gross - 600000) * 0.05;
            if (gross <= 2200000) return 30000 + (gross - 1200000) * 0.15;
            if (gross <= 3200000) return 180000 + (gross - 2200000) * 0.25;
            if (gross <= 4100000) return 430000 + (gross - 3200000) * 0.30;
            return 700000 + (gross - 4100000) * 0.35;
        },
        calculateMonthlyTax() {
            return Math.round(this.calculateAnnualTax() / 12);
        },
        calculateMonthlyTakeHome() {
            let mGross = this.calcFreq === 'monthly' ? (parseFloat(this.calcSalary) || 0) : (parseFloat(this.calcSalary) || 0) / 12;
            return Math.max(0, Math.round(mGross - this.calculateMonthlyTax()));
        },
        async checkAtl() {
            let clean = (this.quickCnic || '').replace(/[^0-9]/g, '');
            if (clean.length !== 13) {
                this.atlError = 'Please enter a valid 13-digit CNIC (e.g. 42101-1234567-1)';
                this.atlResult = null;
                return;
            }
            this.atlError = null;
            this.isCheckingAtl = true;
            try {
                let token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';
                let res = await fetch('{{ route('atl.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cnic: this.quickCnic })
                });
                let data = await res.json();
                if (data.success) {
                    this.atlResult = data;
                } else {
                    this.atlError = data.message || 'Unable to verify status at this moment.';
                    this.atlResult = null;
                }
            } catch (err) {
                this.atlError = 'FBR IRIS connection timeout. Please try again.';
                this.atlResult = null;
            } finally {
                this.isCheckingAtl = false;
            }
        },
        autoRotateTimer: null,
        init() {
            this.startAutoRotate();
        },
        startAutoRotate() {
            this.stopAutoRotate();
            this.autoRotateTimer = setInterval(() => {
                this.scrollServices(1);
            }, 3500);
        },
        stopAutoRotate() {
            if (this.autoRotateTimer) {
                clearInterval(this.autoRotateTimer);
                this.autoRotateTimer = null;
            }
        },
        openCallback(serviceName, serviceCode) {
            this.selectedService = { name: serviceName, code: serviceCode };
            this.callbackModalOpen = true;
        },
        scrollServices(dir) {
            let el = this.$refs.serviceCarousel;
            if (el) {
                let card = el.querySelector('.service-card');
                let scrollAmt = card ? (card.offsetWidth + 24) : 280;
                let maxScroll = el.scrollWidth - el.clientWidth;

                if (dir > 0 && el.scrollLeft >= maxScroll - 15) {
                    el.scrollTo({ left: 0, behavior: 'smooth' });
                } else if (dir < 0 && el.scrollLeft <= 15) {
                    el.scrollTo({ left: maxScroll, behavior: 'smooth' });
                } else {
                    el.scrollBy({ left: dir * scrollAmt, behavior: 'smooth' });
                }
            }
        }
    }"
>
    <!-- Marketing Body (GTM & Pixel NoScript + Custom Tracking Scripts) -->
    <x-marketing-body />

    <!-- TOP TRUST STRIP -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span>
                    FBR IRIS 2.0 Live Bridge Active
                </span>
                <span class="hidden md:inline text-slate-400">Official CPR Receipts | SECP Approved Corporate Advisory</span>
            </div>
            <div class="flex items-center space-x-4 text-slate-300">
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $firmPhone) }}" class="hover:text-white transition flex items-center gap-1.5">
                    <span>📞</span> <span>{{ $firmPhone }}</span>
                </a>
                <span class="text-slate-700">|</span>
                <a href="{{ route('track') }}" class="hover:text-blue-400 transition font-medium">
                    🔍 Track Application
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN NAVBAR -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-blue-600 transition">{{ $firmName }}</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-blue-600">Tax & Corporate Advisory</span>
                </div>
            </a>

            <!-- Dynamic Header Menu Navigation -->
            <nav class="hidden md:flex items-center space-x-7 text-sm font-semibold text-slate-600">
                @php
                    $navItems = header_menu();
                @endphp
                @if($navItems->isNotEmpty())
                    @foreach($navItems as $item)
                        <a href="{{ $item->url }}" target="{{ $item->target }}" class="hover:text-blue-600 transition">
                            {{ $item->title }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('services.index') }}" class="hover:text-blue-600 transition">Business Services</a>
                    <a href="{{ route('track') }}" class="hover:text-blue-600 transition">Track Application</a>
                    <a href="{{ route('faq') }}" class="hover:text-blue-600 transition">Tax Guides & FAQ</a>
                    <a href="{{ route('contact') }}" class="hover:text-blue-600 transition">Contact</a>
                @endif
            </nav>

            <!-- Actions & Direct WhatsApp -->
            <div class="flex items-center space-x-3">
                <a
                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $firmWhatsapp) }}?text={{ urlencode('Assalam-o-Alaikum, mujhe tax filing aur business services ke bare mein rabta karna hai.') }}"
                    target="_blank"
                    class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition shadow-2xs"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.791.82 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.766-5.767-5.766zm3.385 8.165c-.144.405-.837.774-1.17.823-.312.043-.681.085-2.227-.552-1.776-.732-2.871-2.544-2.958-2.661-.087-.116-.717-.954-.717-1.821 0-.866.452-1.294.613-1.468.161-.173.351-.216.468-.216.116 0 .233.002.333.007.106.006.248-.04.389.297.144.348.491 1.199.534 1.286.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.179.182-.077.357.102.174.453.748.972 1.211.669.597 1.233.782 1.408.869.174.087.276.072.378-.043.102-.116.435-.507.551-.681.116-.174.233-.145.391-.087s1.015.478 1.189.565c.174.087.29.13.333.203.043.072.043.419-.101.824zM12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.2-1.31c1.42.79 3.05 1.31 4.8 1.31 5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
                    </svg>
                    <span>WhatsApp Help</span>
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition shadow-sm">
                        Client Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-blue-600 transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition shadow-sm shadow-blue-500/20">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 text-white py-16 sm:py-24">
        <!-- Glow Orbs -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-blue-500/10 blur-3xl pointer-events-none rounded-full"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Hero Copy -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-400/20 text-blue-300 text-xs font-semibold">
                        <span>🏆</span>
                        <span>Pakistan’s Trusted Tax Advisory & Corporate Partner</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.15]">
                        Tax Filing & Corporate Services Made <span class="bg-gradient-to-r from-blue-400 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">Simple & Certified</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl">
                        FBR Income Tax Returns, NTN Registration, Sales Tax (STRN), SECP Company Incorporation, and USA LLC Setup handled by certified tax advocates with live status tracking.
                    </p>

                    <div class="flex flex-wrap gap-4 pt-2">
                        <a href="#services-matrix" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl transition shadow-lg shadow-blue-600/30 flex items-center space-x-2">
                            <span>Browse All Services</span>
                            <span>&darr;</span>
                        </a>
                        <a href="{{ route('track') }}" class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm rounded-xl border border-slate-700 transition flex items-center space-x-2">
                            <span>Track Application Status</span>
                            <span>&rarr;</span>
                        </a>
                        <button
                            type="button"
                            @click="openCallback('General Tax Advisory', 'general')"
                            class="px-5 py-3.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 font-bold text-sm rounded-xl transition flex items-center space-x-2"
                        >
                            <span>📞</span>
                            <span>Request Free Call</span>
                        </button>
                    </div>

                    <!-- Trust Stats Bar -->
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-800 max-w-lg">
                        <div>
                            <div class="text-2xl font-black text-white">24-48h</div>
                            <div class="text-xs text-slate-400 font-medium">Turnaround Time</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-emerald-400">100%</div>
                            <div class="text-xs text-slate-400 font-medium">FBR IRIS Verified</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-blue-400">CPR Valid</div>
                            <div class="text-xs text-slate-400 font-medium">Official Bank Proof</div>
                        </div>
                    </div>
                </div>

                <!-- Right Hero: Dual Quick Tools Card -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- Tool 1: Live FBR ATL Check -->
                    <div class="bg-white rounded-2xl p-6 text-slate-900 shadow-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600 text-lg">🔍</span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Live FBR ATL Status Check</h3>
                                    <p class="text-[11px] text-slate-500">Active Taxpayer List Verification</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 uppercase">Live IRIS</span>
                        </div>

                        <div class="space-y-3">
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="quickCnic"
                                    placeholder="Enter 13-digit CNIC (e.g. 42101-1234567-1)"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                    @keydown.enter.prevent="checkAtl()"
                                />
                            </div>

                            <button
                                type="button"
                                @click="checkAtl()"
                                :disabled="isCheckingAtl"
                                class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center space-x-2"
                            >
                                <template x-if="isCheckingAtl">
                                    <span>Verifying with FBR...</span>
                                </template>
                                <template x-if="!isCheckingAtl">
                                    <span>Check ATL Status &rarr;</span>
                                </template>
                            </button>

                            <!-- ATL Error -->
                            <template x-if="atlError">
                                <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs" x-text="atlError"></div>
                            </template>

                            <!-- ATL Result Display -->
                            <template x-if="atlResult">
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-medium">Status:</span>
                                        <span
                                            class="font-bold px-2 py-0.5 rounded text-[11px]"
                                            :class="atlResult.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                            x-text="atlResult.is_active ? 'Active Filer (ATL)' : 'Inactive / Non-Filer'"
                                        ></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-medium">CNIC:</span>
                                        <span class="font-mono font-bold text-slate-800" x-text="atlResult.cnic"></span>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                                        <span class="text-[11px] text-slate-500" x-text="atlResult.is_active ? 'You enjoy 50% tax rebate on transactions.' : 'File your return now to activate ATL.'"></span>
                                        <a href="{{ route('services.index') }}" class="text-blue-600 font-bold hover:underline">File Return &rarr;</a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Tool 2: Income Tax Slabs Mini Calculator -->
                    <div class="bg-gradient-to-br from-blue-900 to-indigo-950 rounded-2xl p-6 text-white shadow-2xl border border-blue-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <span class="p-2 rounded-lg bg-blue-500/20 text-blue-300 text-lg">🧮</span>
                                <div>
                                    <h3 class="text-sm font-bold text-white">FY 2024-25 Salary Tax Calculator</h3>
                                    <p class="text-[11px] text-blue-300">Updated Finance Act Rates</p>
                                </div>
                            </div>
                            <div class="flex bg-blue-950/60 p-0.5 rounded-lg border border-blue-700/50 text-[10px]">
                                <button type="button" @click="calcFreq = 'monthly'" :class="calcFreq === 'monthly' ? 'bg-blue-600 text-white font-bold' : 'text-blue-300'" class="px-2 py-1 rounded">Monthly</button>
                                <button type="button" @click="calcFreq = 'annual'" :class="calcFreq === 'annual' ? 'bg-blue-600 text-white font-bold' : 'text-blue-300'" class="px-2 py-1 rounded">Annual</button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="text-[11px] text-blue-200 font-medium block mb-1">
                                    <span x-text="calcFreq === 'monthly' ? 'Monthly Gross Salary (PKR)' : 'Annual Gross Salary (PKR)'"></span>
                                </label>
                                <input
                                    type="number"
                                    x-model="calcSalary"
                                    class="w-full px-3.5 py-2 rounded-xl bg-blue-950/80 border border-blue-700 text-white text-sm font-bold focus:ring-2 focus:ring-blue-400 font-mono"
                                    step="5000"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-1 text-center">
                                <div class="p-2.5 rounded-xl bg-blue-950/60 border border-blue-800">
                                    <span class="text-[10px] text-blue-300 font-medium block">Monthly Tax</span>
                                    <span class="text-sm font-mono font-black text-amber-300 tabular-nums">
                                        Rs. <span x-text="calculateMonthlyTax().toLocaleString()"></span>
                                    </span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-blue-950/60 border border-blue-800">
                                    <span class="text-[10px] text-blue-300 font-medium block">Monthly Take-Home</span>
                                    <span class="text-sm font-mono font-black text-emerald-400 tabular-nums">
                                        Rs. <span x-text="calculateMonthlyTakeHome().toLocaleString()"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DYNAMIC SERVICES CAROUSEL SECTION -->
    <section id="services-matrix" class="py-16 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="max-w-2xl">
                <span class="text-xs font-bold tracking-wider text-blue-600 uppercase bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                    Services Marketplace
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                    Comprehensive Tax & Corporate Solutions
                </h2>
                <p class="text-sm text-slate-600 mt-2">
                    Official FBR IRIS & SECP verified filings with live tracking, document checklists, and dedicated consultants.
                </p>
            </div>

            <!-- Carousel Controls & View More Link -->
            <div class="flex items-center space-x-3 self-start md:self-end">
                <a
                    href="{{ route('services.index') }}"
                    class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline flex items-center space-x-1 mr-2"
                >
                    <span>View Full Catalog</span>
                    <span>&rarr;</span>
                </a>
                <button
                    type="button"
                    @click="scrollServices(-1)"
                    class="w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-base transition shadow-2xs hover:shadow-xs active:scale-95"
                    title="Previous Services"
                >
                    &larr;
                </button>
                <button
                    type="button"
                    @click="scrollServices(1)"
                    class="w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-base transition shadow-2xs hover:shadow-xs active:scale-95"
                    title="Next Services"
                >
                    &rarr;
                </button>
            </div>
        </div>

        <!-- Dynamic Category Filter Tabs -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-3 mb-6 scrollbar-none" style="scrollbar-width: none;">
            <button
                type="button"
                @click="activeCategory = 'all'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-xs whitespace-nowrap"
                :class="activeCategory === 'all' ? 'bg-blue-600 text-white shadow-blue-500/20' : 'bg-white text-slate-700 border border-slate-200 hover:border-blue-400 hover:text-blue-600'"
            >
                🌐 All Services
            </button>

            @foreach($categories as $cat)
                <button
                    type="button"
                    @click="activeCategory = '{{ $cat->slug }}'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-xs flex items-center space-x-1.5 whitespace-nowrap"
                    :class="activeCategory === '{{ $cat->slug }}' ? 'bg-blue-600 text-white shadow-blue-500/20' : 'bg-white text-slate-700 border border-slate-200 hover:border-blue-400 hover:text-blue-600'"
                >
                    <span>{{ $cat->icon ?: '💼' }}</span>
                    <span>{{ $cat->name }}</span>
                </button>
            @endforeach
        </div>

        <!-- Services Carousel Slider (Auto-rotating with 4 Cards on Desktop) -->
        <div
            x-ref="serviceCarousel"
            @mouseenter="stopAutoRotate()"
            @mouseleave="startAutoRotate()"
            @touchstart="stopAutoRotate()"
            @touchend="startAutoRotate()"
            class="flex gap-5 overflow-x-auto pb-6 pt-2 scroll-smooth snap-x snap-mandatory scrollbar-none"
            style="scrollbar-width: none; -ms-overflow-style: none;"
        >
            @foreach($services as $svc)
                <div
                    x-show="activeCategory === 'all' || activeCategory === '{{ $svc->category }}'"
                    class="service-card w-[85%] sm:w-[calc(50%-10px)] md:w-[calc(33.333%-14px)] lg:w-[calc(25%-15px)] flex-shrink-0 snap-start bg-white rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-xl transition-all duration-200 flex flex-col justify-between p-5 relative group"
                >
                    @if($svc->popular)
                        <span class="absolute -top-3 right-5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[10px] font-extrabold px-3 py-0.5 rounded-full uppercase tracking-wider shadow-sm">
                            ★ Recommended
                        </span>
                    @endif

                    <div>
                        <!-- Top Icon & Official Fee -->
                        <div class="flex items-start justify-between mb-4">
                            <span class="text-3xl p-3 rounded-2xl bg-slate-100 group-hover:bg-blue-50 group-hover:scale-110 transition duration-200 inline-block">
                                {{ $svc->icon ?: '💼' }}
                            </span>
                            <div class="text-right">
                                <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider block">Official Fee</span>
                                <span class="text-lg font-black font-mono text-blue-700 tabular-nums">
                                    {{ $svc->currency ?: 'PKR' }} {{ number_format($svc->fee, 0) }}
                                </span>
                            </div>
                        </div>

                        <!-- Heading and Urdu Subtitle -->
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition leading-snug">
                            {{ $svc->heading }}
                        </h3>
                        @if(!empty($svc->urdu))
                            <span class="text-xs font-semibold text-blue-600 block mt-0.5 font-urdu">
                                {{ $svc->urdu }}
                            </span>
                        @endif

                        <p class="text-xs text-slate-500 mt-2.5 leading-relaxed line-clamp-3">
                            {{ $svc->desc ?: 'Certified consultant assistance with direct FBR IRIS submission.' }}
                        </p>

                        <!-- Turnaround Badge -->
                        <div class="mt-3.5 flex items-center space-x-1.5 text-xs text-slate-600 font-medium">
                            <span class="text-slate-400">⏱️ Turnaround:</span>
                            <span class="font-bold text-slate-800">{{ $svc->turnaround ?: '1 - 2 Working Days' }}</span>
                        </div>

                        <!-- Requirements Pills -->
                        @if(!empty($svc->requirements) && is_array($svc->requirements))
                            <div class="mt-3.5 pt-3.5 border-t border-slate-100 space-y-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Required Documents:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(array_slice($svc->requirements, 0, 3) as $req)
                                        @php
                                            $reqLabel = is_array($req) ? ($req['label'] ?? ($req['name'] ?? '')) : (is_string($req) ? $req : '');
                                        @endphp
                                        @if(!empty($reqLabel))
                                            <span class="text-[11px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium">
                                                ✓ {{ $reqLabel }}
                                            </span>
                                        @endif
                                    @endforeach
                                    @if(count($svc->requirements) > 3)
                                        <span class="text-[10px] text-blue-600 font-bold self-center">+{{ count($svc->requirements) - 3 }} more</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- THE 3 MANDATORY ACTION BUTTONS -->
                    <div class="mt-6 pt-4 border-t border-slate-100 grid grid-cols-3 gap-2">
                        <!-- 1. Request Call -->
                        <button
                            type="button"
                            @click="openCallback('{{ addslashes($svc->heading) }}', '{{ $svc->code }}')"
                            class="px-2 py-2 rounded-xl text-[11px] font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition text-center flex flex-col items-center justify-center gap-0.5"
                            title="Request Phone Callback"
                        >
                            <span>📞</span>
                            <span>Call</span>
                        </button>

                        <!-- 2. Direct WhatsApp -->
                        <a
                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $firmWhatsapp) }}?text={{ urlencode('Assalam-o-Alaikum, mujhe ' . $svc->heading . ' ki service ke hawaale se maloomat chahiye.') }}"
                            target="_blank"
                            class="px-2 py-2 rounded-xl text-[11px] font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition text-center flex flex-col items-center justify-center gap-0.5"
                            title="WhatsApp Consultant"
                        >
                            <span>💬</span>
                            <span>WhatsApp</span>
                        </a>

                        <!-- 3. Online Application Form -->
                        <a
                            href="{{ route('services.apply.code', ['code' => $svc->code]) }}"
                            class="px-2 py-2 rounded-xl text-[11px] font-bold text-white bg-blue-600 hover:bg-blue-700 transition text-center flex flex-col items-center justify-center gap-0.5 shadow-xs"
                            title="Apply Online Now"
                        >
                            <span>📝</span>
                            <span>Apply</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- VIEW MORE CTA CARD (LEADS TO SERVICES PAGE) -->
        <div class="mt-8 p-6 rounded-2xl bg-gradient-to-r from-blue-900 to-indigo-950 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6 border border-blue-800">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-600/30 border border-blue-400/30 flex items-center justify-center text-2xl flex-shrink-0">
                    📂
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Looking for More Tax & Corporate Services?</h3>
                    <p class="text-xs text-blue-200 mt-1">Browse our complete catalogue of 18+ services including SRB, PRA, SECP, Trademark, and USA LLC.</p>
                </div>
            </div>
            <a
                href="{{ route('services.index') }}"
                class="px-6 py-3 bg-white hover:bg-blue-50 text-blue-900 text-xs font-bold rounded-xl transition shadow-md whitespace-nowrap flex items-center space-x-2 group"
            >
                <span>View More Services &rarr;</span>
            </a>
        </div>
    </section>

    <!-- WHY CHOOSE US (TRUST & COMPLIANCE) -->
    <section class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold tracking-wider text-blue-600 uppercase bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                    Why Choose {{ $firmName }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-3">
                    Your Legal & Financial Shield in Pakistan
                </h2>
                <p class="text-sm text-slate-500 mt-2">
                    Avoid heavy non-filer withholding tax penalties and protect your assets legally.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                    <span class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 inline-flex items-center justify-center text-2xl">⚖️</span>
                    <h3 class="text-base font-bold text-slate-900">Certified Advocates</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Returns prepared and reviewed by licensed High Court Tax Advocates and Corporate Consultants.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                    <span class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 inline-flex items-center justify-center text-2xl">🧾</span>
                    <h3 class="text-base font-bold text-slate-900">Official CPR Proofs</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Every single submission comes with official FBR IRIS CPR receipt and wealth statement reconciliation.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                    <span class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 inline-flex items-center justify-center text-2xl">🛡️</span>
                    <h3 class="text-base font-bold text-slate-900">Audit & Notice Defense</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Full year legal support in responding to FBR Section 111, 114, and 122 inquiry notices.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                    <span class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 inline-flex items-center justify-center text-2xl">🔒</span>
                    <h3 class="text-base font-bold text-slate-900">100% Confidential</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Bank-grade encryption protecting all client CNICs, statements, and wealth disclosures.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS (CONNECTIVE VISUAL TIMELINE) -->
    <section class="py-16 sm:py-24 bg-gradient-to-b from-slate-50 via-white to-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold tracking-wider text-blue-600 uppercase bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                    Simple & Frictionless
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                    Your Step-by-Step Filing Timeline
                </h2>
                <p class="text-sm text-slate-600 mt-2">
                    Transparent, fully audited, and completed with official government CPR receipts in 4 simple milestones.
                </p>
            </div>

            <!-- TIMELINE STEPPER ROAD (Desktop: Connected Milestones / Mobile: Vertical Stepper) -->
            <div class="relative">
                <!-- Desktop Connecting Gradient Stepper Bar -->
                <div class="hidden lg:block absolute top-7 left-[10%] right-[10%] h-1.5 bg-slate-200 z-0 rounded-full">
                    <div class="h-full bg-gradient-to-r from-blue-600 via-indigo-600 via-blue-700 to-emerald-500 rounded-full w-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 relative z-10">
                    <!-- TIMELINE NODE 01 -->
                    <div class="flex flex-col items-center text-center group">
                        <!-- Node Circle with glowing ring -->
                        <div class="w-14 h-14 rounded-full bg-blue-600 text-white font-black text-base flex items-center justify-center shadow-lg shadow-blue-500/30 border-4 border-white ring-4 ring-blue-100 group-hover:scale-110 transition duration-200 z-10 mb-6">
                            01
                        </div>

                        <!-- Timeline Card -->
                        <div class="w-full bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-blue-400 hover:shadow-xl transition-all duration-200 text-left flex flex-col justify-between flex-1 relative">
                            <!-- Caret pointing up to node -->
                            <div class="hidden lg:block absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-slate-50 border-t border-l border-slate-200 rotate-45 group-hover:bg-white group-hover:border-blue-400 transition"></div>

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Step 01</span>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">⏱ 5 - 10 Mins</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition">
                                    Select Service & Upload Docs
                                </h3>
                                <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                                    Pick your required package (NTN, Tax Return, SECP, or Trademark) and attach basic copies of CNIC, paid electricity bill, or business letterhead.
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center text-[11px] text-slate-600 font-semibold">
                                <span class="text-emerald-600 mr-1.5">✓</span> Encrypted Document Vault
                            </div>
                        </div>
                    </div>

                    <!-- TIMELINE NODE 02 -->
                    <div class="flex flex-col items-center text-center group">
                        <!-- Node Circle with glowing ring -->
                        <div class="w-14 h-14 rounded-full bg-indigo-600 text-white font-black text-base flex items-center justify-center shadow-lg shadow-indigo-500/30 border-4 border-white ring-4 ring-indigo-100 group-hover:scale-110 transition duration-200 z-10 mb-6">
                            02
                        </div>

                        <!-- Timeline Card -->
                        <div class="w-full bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-indigo-400 hover:shadow-xl transition-all duration-200 text-left flex flex-col justify-between flex-1 relative">
                            <!-- Caret pointing up to node -->
                            <div class="hidden lg:block absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-slate-50 border-t border-l border-slate-200 rotate-45 group-hover:bg-white group-hover:border-indigo-400 transition"></div>

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Step 02</span>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">⏱ 2 - 4 Hours</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition">
                                    Advocate Audit & Calculation
                                </h3>
                                <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                                    Our licensed High Court tax consultants review your documents, calculate legal rebates, reconcile wealth statements, and eliminate audit risks.
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center text-[11px] text-slate-600 font-semibold">
                                <span class="text-emerald-600 mr-1.5">✓</span> Senior Tax Advocate Audit
                            </div>
                        </div>
                    </div>

                    <!-- TIMELINE NODE 03 -->
                    <div class="flex flex-col items-center text-center group">
                        <!-- Node Circle with glowing ring -->
                        <div class="w-14 h-14 rounded-full bg-blue-700 text-white font-black text-base flex items-center justify-center shadow-lg shadow-blue-700/30 border-4 border-white ring-4 ring-blue-100 group-hover:scale-110 transition duration-200 z-10 mb-6">
                            03
                        </div>

                        <!-- Timeline Card -->
                        <div class="w-full bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-blue-500 hover:shadow-xl transition-all duration-200 text-left flex flex-col justify-between flex-1 relative">
                            <!-- Caret pointing up to node -->
                            <div class="hidden lg:block absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-slate-50 border-t border-l border-slate-200 rotate-45 group-hover:bg-white group-hover:border-blue-500 transition"></div>

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Step 03</span>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-900">⏱ Same Day</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-700 transition">
                                    Live FBR IRIS 2.0 Lodgement
                                </h3>
                                <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                                    Direct electronic submission on FBR IRIS and SECP eServices portals. An immediate 9-character live tracking ID is generated for your filing.
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center text-[11px] text-slate-600 font-semibold">
                                <span class="text-emerald-600 mr-1.5">✓</span> Live Public Tracking Number
                            </div>
                        </div>
                    </div>

                    <!-- TIMELINE NODE 04 -->
                    <div class="flex flex-col items-center text-center group">
                        <!-- Node Circle with glowing ring -->
                        <div class="w-14 h-14 rounded-full bg-emerald-600 text-white font-black text-base flex items-center justify-center shadow-lg shadow-emerald-500/30 border-4 border-white ring-4 ring-emerald-100 group-hover:scale-110 transition duration-200 z-10 mb-6">
                            04
                        </div>

                        <!-- Timeline Card -->
                        <div class="w-full bg-slate-50 hover:bg-white rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-xl transition-all duration-200 text-left flex flex-col justify-between flex-1 relative">
                            <!-- Caret pointing up to node -->
                            <div class="hidden lg:block absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-slate-50 border-t border-l border-slate-200 rotate-45 group-hover:bg-white group-hover:border-emerald-500 transition"></div>

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Step 04</span>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900">⏱ 24 - 48 Hours</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition">
                                    Official CPR Proof & Active ATL
                                </h3>
                                <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                                    Download your official FBR Computerized Payment Receipt (CPR) and SECP Certificate directly. Enjoy lowest withholding tax rates across Pakistan as an Active Filer.
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center text-[11px] text-slate-600 font-semibold">
                                <span class="text-emerald-600 mr-1.5">✓</span> Official FBR CPR Certificate
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DYNAMIC FAQ SECTION (MATCHES SCHEMA.ORG FAQPAGE) -->
    @if($faqs->isNotEmpty())
        <section class="py-16 bg-white border-t border-slate-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <span class="text-xs font-bold tracking-wider text-blue-600 uppercase bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                        Frequently Asked Questions
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-3">
                        Tax & Corporate Advisory Questions Answered
                    </h2>
                </div>

                <div class="space-y-3" x-data="{ activeFaq: null }">
                    @foreach($faqs as $idx => $faq)
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50">
                            <button
                                type="button"
                                @click="activeFaq = (activeFaq === {{ $idx }} ? null : {{ $idx }})"
                                class="w-full px-5 py-4 text-left text-sm font-bold text-slate-800 flex items-center justify-between hover:bg-slate-100/50 transition"
                            >
                                <span>{{ $faq->question }}</span>
                                <span class="text-blue-600 text-lg transition-transform" :class="activeFaq === {{ $idx }} ? 'rotate-180' : ''">
                                    &darr;
                                </span>
                            </button>
                            <div
                                x-show="activeFaq === {{ $idx }}"
                                x-collapse
                                class="px-5 py-4 border-t border-slate-200 bg-white text-xs text-slate-600 leading-relaxed"
                            >
                                {!! nl2br(e($faq->answer)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- BOTTOM CTA CALL-OUT -->
    <section class="py-16 bg-gradient-to-tr from-slate-900 to-blue-950 text-white text-center">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400 bg-blue-900/50 px-3 py-1 rounded-full border border-blue-700">
                Ready to Become An Active Taxpayer?
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                Get Your NTN or File Return with Pakistan’s Leading Tax Experts
            </h2>
            <p class="text-sm text-slate-300 max-w-xl mx-auto">
                Join thousands of individuals and companies who file taxes confidently with zero hassle.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                <a href="{{ route('services.index') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 font-bold text-xs rounded-xl transition shadow-lg shadow-blue-600/30">
                    Apply for Service Now &rarr;
                </a>
                <a
                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $firmWhatsapp) }}?text={{ urlencode('Assalam-o-Alaikum, mujhe free tax consultation chahiye.') }}"
                    target="_blank"
                    class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 font-bold text-xs rounded-xl transition shadow-lg shadow-emerald-600/30 flex items-center space-x-1.5"
                >
                    <span>💬</span>
                    <span>Chat on WhatsApp</span>
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER (DYNAMIC & FULLY BRANDED) -->
    <footer class="bg-slate-900 text-slate-400 text-xs py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-3">
                <span class="text-base font-extrabold text-white tracking-tight">{{ $firmName }}</span>
                <p class="text-[11px] leading-relaxed text-slate-400">
                    Authorized Tax Consultants, Legal Advocates, and Corporate Financial Advisors. Direct FBR IRIS 2.0 and SECP eServices filing.
                </p>
                <div class="text-[11px] space-y-1 text-slate-400 pt-1">
                    <div>📍 {{ $firmAddress }}</div>
                    <div>📞 {{ $firmPhone }}</div>
                    <div>✉️ {{ $firmEmail }}</div>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Quick Navigation</h4>
                <ul class="space-y-2">
                    @php
                        $footerNav = footer_menu();
                    @endphp
                    @if($footerNav->isNotEmpty())
                        @foreach($footerNav as $fItem)
                            <li>
                                <a href="{{ $fItem->url }}" target="{{ $fItem->target }}" class="hover:text-white transition">
                                    {{ $fItem->title }}
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li><a href="{{ route('services.index') }}" class="hover:text-white transition">Business Services</a></li>
                        <li><a href="{{ route('track') }}" class="hover:text-white transition">Track Application</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition">Tax Guides & FAQ</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact Us</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Popular Services</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('services.apply.code', ['code' => 'ntn_individual']) }}" class="hover:text-white transition">Individual NTN Registration</a></li>
                    <li><a href="{{ route('services.apply.code', ['code' => 'annual_salaried_return']) }}" class="hover:text-white transition">Annual Income Tax Return</a></li>
                    <li><a href="{{ route('services.apply.code', ['code' => 'secp_pvt_ltd']) }}" class="hover:text-white transition">SECP Company Registration</a></li>
                    <li><a href="{{ route('services.apply.code', ['code' => 'gst_registration']) }}" class="hover:text-white transition">Sales Tax (STRN) Registration</a></li>
                    <li><a href="{{ route('services.apply.code', ['code' => 'usa_llc_formation']) }}" class="hover:text-white transition">USA LLC & Tax Filing</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Security & Compliance</h4>
                <div class="space-y-2 text-[11px] text-slate-400">
                    <p>🔒 256-bit Bank Grade SSL Encryption protecting client financial records.</p>
                    <p>✓ All submissions reconciled with official FBR Computerized Payment Receipts (CPR).</p>
                    <p>&copy; {{ date('Y') }} {{ $firmName }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- INTERACTIVE CALLBACK REQUEST MODAL -->
    <div
        x-show="callbackModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div
            @click.outside="callbackModalOpen = false"
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative space-y-4"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <span class="p-2 rounded-xl bg-blue-50 text-blue-600 text-lg">📞</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Request A Call Back</h3>
                        <p class="text-[11px] text-slate-500" x-text="selectedService ? selectedService.name : 'General Consultation'"></p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="callbackModalOpen = false"
                    class="text-slate-400 hover:text-slate-700 text-lg font-bold"
                >&times;</button>
            </div>

            <form action="{{ route('services.callback') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="service_name" :value="selectedService ? selectedService.name : 'General Tax Inquiry'">
                <input type="hidden" name="service_code" :value="selectedService ? selectedService.code : 'general'">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Your Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Muhammad Ali" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone (WhatsApp Active)</label>
                    <input type="text" name="phone" required placeholder="03001234567" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Preferred Time to Call</label>
                    <select name="preferred_time" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600">
                        <option value="immediate">Immediate / As soon as possible</option>
                        <option value="morning">Morning (09:00 AM - 12:00 PM)</option>
                        <option value="afternoon">Afternoon (12:00 PM - 04:00 PM)</option>
                        <option value="evening">Evening (04:00 PM - 07:00 PM)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Query (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Tell us briefly about your tax or company registration question..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600"></textarea>
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <button
                        type="button"
                        @click="callbackModalOpen = false"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition"
                    >Cancel</button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition shadow-xs"
                    >Submit Request &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
