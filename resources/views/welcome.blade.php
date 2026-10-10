<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>TaxFiler.pk - Pakistan’s Smartest Tax Filing & Corporate Advisory Platform</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js Application Component Definition -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('taxPortal', () => ({
                lang: 'en',
                salaryFreq: 'monthly',
                salaryAmount: 150000,
                quickCnic: '',
                isCheckingAtl: false,
                atlErrorMessage: null,
                cnicCheckResult: null,

                calculateAnnualGross() {
                    let amt = parseFloat(this.salaryAmount) || 0;
                    return this.salaryFreq === 'monthly' ? amt * 12 : amt;
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
                    let monthlyGross = this.salaryFreq === 'monthly' ? (parseFloat(this.salaryAmount) || 0) : (parseFloat(this.salaryAmount) || 0) / 12;
                    return Math.max(0, Math.round(monthlyGross - this.calculateMonthlyTax()));
                },
                calculateEffectiveRate() {
                    let gross = this.calculateAnnualGross();
                    if (gross <= 0) return 0;
                    let tax = this.calculateAnnualTax();
                    return ((tax / gross) * 100).toFixed(1);
                },
                async checkAtlStatus() {
                    let clean = (this.quickCnic || '').replace(/[^0-9]/g, '');
                    if (clean.length !== 13) {
                        this.atlErrorMessage = 'Barah-e-karam durust 13-hanso ka CNIC number darj karein (e.g. 42101-1234567-1).';
                        this.cnicCheckResult = null;
                        return;
                    }
                    this.atlErrorMessage = null;
                    this.isCheckingAtl = true;
                    try {
                        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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
                            this.cnicCheckResult = data;
                        } else {
                            this.atlErrorMessage = data.message || 'Status check karne mein dushwari pesh aayi.';
                            this.cnicCheckResult = null;
                        }
                    } catch (err) {
                        this.atlErrorMessage = 'Online verification server se rabta nahi ho saka. Barah-e-karam dobara koshish karein.';
                        this.cnicCheckResult = null;
                    } finally {
                        this.isCheckingAtl = false;
                    }
                }
            }));
        });
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, sans-serif; }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        @keyframes floatReverse {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(8px); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.08); }
        }
        @keyframes badgePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.25); }
            50% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
        }
        .animate-float {
            animation: floatSlow 5s ease-in-out infinite;
        }
        .animate-float-delayed {
            animation: floatReverse 6s ease-in-out infinite 1s;
        }
        .animate-pulse-glow {
            animation: pulseGlow 7s ease-in-out infinite;
        }
        .animate-badge-pulse {
            animation: badgePulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body x-data="taxPortal" class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Top Announcement Bar (Full Width) -->
    <div class="bg-slate-900 text-slate-200 px-4 sm:px-8 lg:px-12 py-2 text-xs font-medium border-b border-slate-800">
        <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center space-x-2 text-center sm:text-left">
                <span class="bg-blue-600 text-white text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">FBR TAX YEAR 2026-27</span>
                <span x-show="lang === 'en'">File in 6 minutes or register your NTN & company with certified FBR advocates.</span>
                <span x-show="lang === 'ur'" class="font-urdu">صرف 6 منٹ میں ٹیکس ریٹرن فائل کریں یا این ٹی این اور کمپنی رجسٹر کروائیں!</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300">Salaried Return: <strong class="text-white font-mono font-bold tabular-nums">Rs. 3,000</strong></span>
                <span class="text-slate-600">|</span>
                <!-- Language Switcher Toggle -->
                <button
                    @click="lang = (lang === 'en' ? 'ur' : 'en')"
                    class="bg-slate-800 hover:bg-slate-700 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold border border-slate-700 transition cursor-pointer"
                >
                    <span x-text="lang === 'en' ? 'اردو / Roman' : 'English'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Header (Full Width with Breathing Room) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-2xs">
        <div class="w-full px-4 sm:px-8 lg:px-12 h-18 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center space-x-3 group flex-shrink-0">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs transition-transform group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-bold tracking-tight text-slate-900">TaxFiler<span class="text-blue-600">.pk</span></span>
                    <span class="text-[10px] block uppercase font-medium tracking-wider text-slate-500">Tax & Corporate Advisory Platform</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden lg:flex items-center space-x-6 text-sm font-medium text-slate-600">
                <a href="{{ route('services.index') }}" class="text-blue-600 font-semibold hover:text-blue-700 transition">
                    Business Services
                </a>
                <a href="#how-it-works" class="hover:text-blue-600 transition">How It Works</a>
                <a href="#popular-services" class="hover:text-blue-600 transition">Products</a>
                <a href="#calculator" class="hover:text-blue-600 transition">Tax Calculator</a>
                <a href="{{ route('track') }}" class="hover:text-blue-600 transition">Track Status</a>
                <a href="{{ route('faq') }}" class="hover:text-blue-600 transition">Guides & FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-blue-600 transition">Contact</a>
            </nav>

            <!-- Action Buttons with WhatsApp Pill -->
            <div class="flex items-center space-x-3 flex-shrink-0">
                <!-- WhatsApp Chat Button (Replaces Help Pill) -->
                <a
                    href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20TaxFiler%20tax%20filing%20aur%20corporate%20services%20ke%20bare%20mein%20maloomat%20chahiye."
                    target="_blank"
                    class="inline-flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:border-[#25D366] hover:bg-emerald-50/50 hover:text-emerald-900 transition shadow-2xs group cursor-pointer"
                    title="Chat with Tax Consultant on WhatsApp"
                >
                    <svg class="w-4 h-4 text-[#25D366] transition-transform group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.791.82 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.766-5.767-5.766zm3.385 8.165c-.144.405-.837.774-1.17.823-.312.043-.681.085-2.227-.552-1.776-.732-2.871-2.544-2.958-2.661-.087-.116-.717-.954-.717-1.821 0-.866.452-1.294.613-1.468.161-.173.351-.216.468-.216.116 0 .233.002.333.007.106.006.248-.04.389.297.144.348.491 1.199.534 1.286.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.179.182-.077.357.102.174.453.748.972 1.211.669.597 1.233.782 1.408.869.174.087.276.072.378-.043.102-.116.435-.507.551-.681.116-.174.233-.145.391-.087s1.015.478 1.189.565c.174.087.29.13.333.203.043.072.043.419-.101.824zM12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.2-1.31c1.42.79 3.05 1.31 4.8 1.31 5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
                    </svg>
                    <span class="hidden sm:inline font-medium">WhatsApp</span>
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition shadow-xs">
                        My Portal &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-slate-700 hover:text-slate-900 rounded-lg text-xs font-medium transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg text-xs font-medium transition shadow-xs">
                        Start Filing 🚀
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section (Enhanced with Fluid Animations & Depth) -->
    <section class="relative pt-12 pb-20 lg:pt-18 lg:pb-28 overflow-hidden bg-gradient-to-b from-blue-50/70 via-slate-50/50 to-slate-50">
        <!-- Ambient animated glowing lights -->
        <div class="absolute top-8 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-tr from-blue-200/40 via-indigo-200/30 to-sky-100/40 blur-[110px] pointer-events-none rounded-full animate-pulse-glow"></div>
        <div class="absolute -top-24 right-10 w-96 h-96 bg-blue-100/40 blur-[90px] pointer-events-none rounded-full"></div>

        <!-- Floating Badges for Large Screens -->
        <div class="hidden xl:flex items-center space-x-2.5 absolute top-20 left-12 bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-sm rounded-xl px-4 py-2.5 text-xs text-slate-700 animate-float pointer-events-none z-10">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-semibold text-slate-800">⚡ Instant FBR 114(1) CPR Slips</span>
        </div>

        <div class="hidden xl:flex items-center space-x-2.5 absolute top-28 right-12 bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-sm rounded-xl px-4 py-2.5 text-xs text-slate-700 animate-float-delayed pointer-events-none z-10">
            <span class="text-blue-600 text-sm">🛡️</span>
            <div>
                <span class="font-semibold text-slate-800 block">100% Active Taxpayer (ATL)</span>
                <span class="text-[10px] text-slate-500 font-mono">Tax Year 2026-27</span>
            </div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto space-y-6">
                <!-- Trust Badge with Subtle Ripple Animation -->
                <div class="inline-flex items-center space-x-2.5 bg-white border border-slate-200 shadow-2xs text-slate-800 px-4 py-1.5 rounded-full text-xs font-medium tracking-wide animate-badge-pulse">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-semibold text-slate-800">⚡ FBR Active Taxpayer Status (ATL) 2026-27 — 100% Guaranteed</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.14]">
                    File your Pakistan taxes in just <br class="hidden sm:inline"/>
                    <span class="relative inline-block text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600">
                        6 minutes
                        <svg class="absolute -bottom-2 left-0 w-full h-3 text-blue-300/80" viewBox="0 0 200 12" fill="none" preserveAspectRatio="none">
                            <path d="M0,7 C50,13 150,0 200,7" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </span>
                    with certified experts
                </h1>

                <!-- Subheadline -->
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed font-normal max-w-2xl mx-auto">
                    FBR Active Filer ban kar bank cash withdrawal, gari aur property par <strong>50% double withholding tax</strong> se bachain. Koi accounting jargon nahi, sirf 3 aasan sawalaat!
                </p>

                <!-- Quick Fast-Track Action Cards (3 Pillars) -->
                <div class="pt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
                    <!-- Choice 1: Salaried Tax Return -->
                    <a
                        href="{{ route('register', ['service' => 'salaried_return']) }}"
                        class="p-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow-lg hover:shadow-blue-600/20 transition-all duration-300 hover:-translate-y-1.5 group flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl p-2 rounded-lg bg-white/10 group-hover:scale-110 transition duration-200">💼</span>
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-white/20 text-white">Popular</span>
                            </div>
                            <h3 class="text-base font-semibold text-white">Salaried Tax Return</h3>
                            <p class="text-xs text-blue-100 mt-1 leading-relaxed">
                                Salary slips, employer tax deduction, aur instant 114(1) FBR acknowledgement.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-blue-500/80 flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-white tabular-nums">PKR 3,000</span>
                            <span class="text-xs font-semibold text-blue-100 group-hover:translate-x-1 transition duration-200">File Now &rarr;</span>
                        </div>
                    </a>

                    <!-- Choice 2: Individual NTN -->
                    <a
                        href="{{ route('register', ['service' => 'ntn_individual']) }}"
                        class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-md transition-all duration-300 hover:-translate-y-1.5 group flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl p-2 rounded-lg bg-blue-50 group-hover:scale-110 transition duration-200">🆔</span>
                                <span class="text-[10px] uppercase font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Fresh Filer</span>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 group-hover:text-blue-600 transition duration-200">Individual NTN</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                "Register, Upload, Pay" — 3 aasan marahil mein apna FBR NTN registration hasil karein.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-mono font-semibold text-slate-900 tabular-nums">PKR 1,500</span>
                            <span class="text-xs font-semibold text-blue-600 group-hover:translate-x-1 transition duration-200">Apply &rarr;</span>
                        </div>
                    </a>

                    <!-- Choice 3: Corporate Services Hub -->
                    <a
                        href="{{ route('services.index') }}"
                        class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-400 hover:shadow-md transition-all duration-300 hover:-translate-y-1.5 group flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-2xl p-2 rounded-lg bg-amber-50 group-hover:scale-110 transition duration-200">🏛️</span>
                                <span class="text-[10px] uppercase font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Advisory</span>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 group-hover:text-blue-600 transition duration-200">Business & Corporate</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                SECP Private Ltd Company, Sales Tax (GST STRN), Trademark, aur USA Delaware LLC.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-amber-700">Explore Catalog</span>
                            <span class="text-xs font-semibold text-blue-600 group-hover:translate-x-1 transition duration-200">View All &rarr;</span>
                        </div>
                    </a>
                </div>

                <!-- Trust Badges Bar -->
                <div class="pt-6 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-slate-500 text-xs font-semibold">
                    <span class="flex items-center"><span class="text-blue-600 font-bold mr-1.5">✓</span> 100% FBR IRIS 2.0 Compliant</span>
                    <span class="flex items-center"><span class="text-blue-600 font-bold mr-1.5">✓</span> High Court & FBR Certified Advocates</span>
                    <span class="flex items-center"><span class="text-blue-600 font-bold mr-1.5">✓</span> Bank-Grade 256-Bit Data Encryption</span>
                    <span class="flex items-center"><span class="text-blue-600 font-bold mr-1.5">✓</span> Official CPR Acknowledgement</span>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works: 3-Step Guided Process -->
    <section id="how-it-works" class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
                <span class="text-blue-600 text-xs font-semibold tracking-wider uppercase">Simple 6-Minute Flow</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    How TaxFiler Works for You
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Aapko FBR ke pecheeda codes samajhne ki zaroorat nahi. 3 aasan marahil mein apna official return jama karein.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
                <!-- Step 1 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-blue-400 hover:shadow-xs transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm font-mono">
                                01
                            </span>
                            <span class="text-2xl">📝</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Answer 3 Simple Questions</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            Sirf apni basic salary slips, bank withholding deduction aur basic personal details faraham karein. Koi accounting formulas nahi.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 text-[11px] text-blue-700 font-semibold flex items-center">
                        <span>⏱️ Time required: 2-3 minutes</span>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-blue-400 hover:shadow-xs transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm font-mono">
                                02
                            </span>
                            <span class="text-2xl">⚖️</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Advocate Review & Optimization</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            High Court & FBR certified advocate aapke data ko verify karta hai, wealth reconcile karta hai, aur legal tax credits apply karta hai.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 text-[11px] text-indigo-700 font-semibold flex items-center">
                        <span>🛡️ 100% Audit-Proof Guarantee</span>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-blue-400 hover:shadow-xs transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-sm font-mono">
                                03
                            </span>
                            <span class="text-2xl">📜</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Instant FBR 114(1) CPR Slip</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            FBR IRIS portal par return file ho kar official acknowledgement slip download ke liye tayyar hoti hai aur Active status activate hota hai.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200/80 text-[11px] text-emerald-700 font-semibold flex items-center">
                        <span>⚡ CPR Acknowledgement Ready</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FBR Active Taxpayer Status (ATL) Quick Checker Widget -->
    <section id="atl-checker" class="py-12 bg-slate-900 text-white relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 sm:p-8 backdrop-blur-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                    <div>
                        <div class="inline-flex items-center space-x-1.5 text-blue-400 text-xs font-semibold uppercase tracking-wider mb-1">
                            <span>🔍</span>
                            <span>Live Online Verification</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white">Check Your FBR Active Filer (ATL) Status</h3>
                        <p class="text-xs text-slate-400 mt-1">Live check connected with FBR Active Taxpayer List registry. Your search is strictly confidential & never saved.</p>
                    </div>
                    <div class="flex items-center space-x-2 self-start sm:self-auto">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping mr-1.5"></span>
                            API Live
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-700 text-slate-300">Tax Year 2026-27</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <div class="sm:col-span-8">
                        <input
                            type="text"
                            x-model="quickCnic"
                            @keydown.enter="checkAtlStatus()"
                            placeholder="Enter 13-digit CNIC (e.g. 42101-1234567-1)"
                            maxlength="15"
                            class="w-full h-11 px-4 rounded-lg bg-slate-900 border border-slate-700 text-white placeholder-slate-500 font-mono text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                        />
                    </div>
                    <div class="sm:col-span-4">
                        <button
                            type="button"
                            @click="checkAtlStatus()"
                            :disabled="isCheckingAtl"
                            class="w-full h-11 px-5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-60 text-white font-medium rounded-lg text-xs uppercase tracking-wider transition shadow-sm cursor-pointer flex items-center justify-center space-x-2"
                        >
                            <span x-show="!isCheckingAtl" class="flex items-center space-x-1.5">
                                <span>Verify CNIC Status</span>
                                <span>&rarr;</span>
                            </span>
                            <span x-show="isCheckingAtl" x-cloak class="flex items-center space-x-1.5">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Verifying FBR...</span>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="atlErrorMessage" x-transition x-cloak class="mt-3 p-3 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                    <span x-text="atlErrorMessage"></span>
                </div>

                <!-- Live Result Display -->
                <div x-show="cnicCheckResult" x-transition x-cloak class="mt-5 p-5 rounded-xl bg-slate-900/90 border border-slate-700 text-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-800 gap-2">
                        <div>
                            <span class="text-slate-400">Verified CNIC:</span>
                            <span class="text-white font-mono font-bold text-sm ml-1" x-text="cnicCheckResult?.cnic"></span>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Checked at: <span x-text="cnicCheckResult?.verified_at"></span></span>
                        </div>
                        <div>
                            <span
                                :class="cnicCheckResult?.is_active ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border-rose-500/30'"
                                class="px-3 py-1 rounded-full text-xs font-semibold border uppercase tracking-wider inline-flex items-center"
                            >
                                <span class="mr-1" x-text="cnicCheckResult?.is_active ? '✓' : '✕'"></span>
                                <span x-text="cnicCheckResult?.status"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-800">
                            <span class="text-slate-400 block text-[11px]">Tax Year:</span>
                            <span class="text-white font-semibold" x-text="cnicCheckResult?.tax_year"></span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-800">
                            <span class="text-slate-400 block text-[11px]">Registry Source:</span>
                            <span class="text-white font-semibold" x-text="cnicCheckResult?.source"></span>
                        </div>
                    </div>

                    <!-- Benefits & Penalties Breakdown -->
                    <div class="p-3.5 rounded-lg bg-slate-950/60 border border-slate-800 space-y-2">
                        <span class="text-slate-300 font-semibold block text-[11px] uppercase tracking-wide">Withholding Tax Impact:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                            <div class="flex items-start space-x-2">
                                <span :class="cnicCheckResult?.is_active ? 'text-emerald-400' : 'text-amber-400'">•</span>
                                <span class="text-slate-300">Bank Cash: <strong class="text-white" x-text="cnicCheckResult?.benefits?.cash_withdrawal"></strong></span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span :class="cnicCheckResult?.is_active ? 'text-emerald-400' : 'text-amber-400'">•</span>
                                <span class="text-slate-300">Vehicle Purchase: <strong class="text-white" x-text="cnicCheckResult?.benefits?.vehicle_purchase"></strong></span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span :class="cnicCheckResult?.is_active ? 'text-emerald-400' : 'text-amber-400'">•</span>
                                <span class="text-slate-300">Property Transfer: <strong class="text-white" x-text="cnicCheckResult?.benefits?.property_transfer"></strong></span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span :class="cnicCheckResult?.is_active ? 'text-emerald-400' : 'text-amber-400'">•</span>
                                <span class="text-slate-300">Bank Profit: <strong class="text-white" x-text="cnicCheckResult?.benefits?.bank_profit"></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation & CTA -->
                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-slate-800">
                        <span class="text-slate-300 font-medium text-xs leading-relaxed" x-text="cnicCheckResult?.action_needed"></span>
                        <template x-if="!cnicCheckResult?.is_active">
                            <a href="{{ route('register', ['service' => 'salaried_return']) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-xs transition whitespace-nowrap self-start sm:self-auto shadow-sm">
                                File Return to Become Active (PKR 3,000) &rarr;
                            </a>
                        </template>
                        <template x-if="cnicCheckResult?.is_active">
                            <a href="{{ route('services.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-medium rounded-lg text-xs transition whitespace-nowrap self-start sm:self-auto border border-slate-700">
                                Explore Other Services &rarr;
                            </a>
                        </template>
                    </div>

                    <div class="text-[10px] text-slate-500 text-right">
                        🔒 Confidential Lookup — Search data is strictly not saved or stored in database.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Salary Tax Calculator (Finance Act 2024 Slabs) -->
    <section id="calculator" class="py-16 lg:py-20 bg-slate-50 border-y border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-blue-600 text-xs font-semibold tracking-wider uppercase">Interactive Financial Tool</span>
                <h2 class="text-2xl sm:text-4xl font-bold text-slate-900 tracking-tight mt-1">
                    Pakistan Salary Tax Calculator 2026-27
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Official revised income tax slabs with real-time Take-Home Pay & Monthly Deduction breakdown.
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                <!-- Period Toggle -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-slate-200 gap-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl">🧮</span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Calculate Your Exact Monthly & Annual Tax</h3>
                            <span class="text-xs text-slate-500">Section 149 & First Schedule Division I</span>
                        </div>
                    </div>

                    <div class="flex items-center bg-slate-100 p-1 rounded-lg text-xs font-medium self-start sm:self-auto border border-slate-200">
                        <button
                            type="button"
                            @click="salaryFreq = 'monthly'"
                            :class="salaryFreq === 'monthly' ? 'bg-white text-blue-600 shadow-xs font-semibold' : 'text-slate-600'"
                            class="px-3.5 py-1.5 rounded-md transition cursor-pointer"
                        >
                            Monthly Salary
                        </button>
                        <button
                            type="button"
                            @click="salaryFreq = 'annual'"
                            :class="salaryFreq === 'annual' ? 'bg-white text-blue-600 shadow-xs font-semibold' : 'text-slate-600'"
                            class="px-3.5 py-1.5 rounded-md transition cursor-pointer"
                        >
                            Annual Salary
                        </button>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <!-- Left: Interactive Inputs & Slider (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-slate-700 uppercase tracking-wide">
                                    Enter Your <span x-text="salaryFreq === 'monthly' ? 'Monthly' : 'Annual'"></span> Gross Salary (PKR)
                                </label>
                                <span class="text-xs font-mono font-bold text-blue-600">
                                    PKR <span x-text="Number(salaryAmount || 0).toLocaleString()"></span>
                                </span>
                            </div>

                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-mono text-xs text-slate-400 font-bold">PKR</span>
                                <input
                                    type="number"
                                    x-model="salaryAmount"
                                    class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-lg font-mono text-base font-bold text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                />
                            </div>

                            <!-- Range Slider for Instant Scrubbing -->
                            <div class="mt-4">
                                <input
                                    type="range"
                                    :min="salaryFreq === 'monthly' ? 50000 : 600000"
                                    :max="salaryFreq === 'monthly' ? 1000000 : 12000000"
                                    :step="salaryFreq === 'monthly' ? 10000 : 100000"
                                    x-model="salaryAmount"
                                    class="w-full accent-blue-600 cursor-pointer"
                                />
                                <div class="flex justify-between text-[11px] text-slate-400 mt-1 font-mono">
                                    <span x-text="salaryFreq === 'monthly' ? '50k / mo' : '600k / yr'"></span>
                                    <span x-text="salaryFreq === 'monthly' ? '500k / mo' : '6M / yr'"></span>
                                    <span x-text="salaryFreq === 'monthly' ? '1M+ / mo' : '12M+ / yr'"></span>
                                </div>
                            </div>

                            <!-- Quick Preset Pills -->
                            <div class="flex flex-wrap items-center gap-2 mt-4 text-xs">
                                <span class="text-slate-500 text-[11px] font-medium">Quick Slabs:</span>
                                <button @click="salaryAmount = (salaryFreq === 'monthly' ? 100000 : 1200000)" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-md text-slate-700 font-medium cursor-pointer border border-slate-200">100k (Tax-Free)</button>
                                <button @click="salaryAmount = (salaryFreq === 'monthly' ? 150000 : 1800000)" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-md text-slate-700 font-medium cursor-pointer border border-slate-200">150k / mo</button>
                                <button @click="salaryAmount = (salaryFreq === 'monthly' ? 250000 : 3000000)" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-md text-slate-700 font-medium cursor-pointer border border-slate-200">250k / mo</button>
                                <button @click="salaryAmount = (salaryFreq === 'monthly' ? 400000 : 4800000)" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-md text-slate-700 font-medium cursor-pointer border border-slate-200">400k / mo</button>
                            </div>
                        </div>

                        <!-- Active Filer Advantage Callout -->
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-start space-x-3 text-xs">
                            <span class="text-xl">🛡️</span>
                            <div>
                                <strong class="text-blue-900 font-semibold block">Why filing your return pays for itself:</strong>
                                <span class="text-blue-700">As an active filer, you save Rs. 50,000+ on vehicle token tax, car registration, cash withdrawals over Rs. 50,000, and advance property withholding.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Calculated Take-Home Summary Card (5 Cols) -->
                    <div class="lg:col-span-5 bg-slate-900 text-white rounded-2xl p-6 shadow-md space-y-4 border border-slate-800">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <span class="text-xs font-medium text-slate-400">Effective Tax Rate:</span>
                            <span class="text-xs font-bold font-mono text-blue-400" x-text="calculateEffectiveRate() + '%'"></span>
                        </div>

                        <!-- Monthly Net Pay -->
                        <div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Monthly Take-Home Pay</span>
                            <div class="text-2xl sm:text-3xl font-bold font-mono text-blue-400 mt-0.5 tabular-nums">
                                PKR <span x-text="calculateMonthlyTakeHome().toLocaleString()"></span>
                            </div>
                            <span class="text-[11px] text-slate-400">After monthly tax deduction</span>
                        </div>

                        <div class="space-y-2.5 pt-3 border-t border-slate-800 text-xs font-mono">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Monthly Tax Deduction:</span>
                                <span class="font-bold text-white tabular-nums">PKR <span x-text="calculateMonthlyTax().toLocaleString()"></span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Annual Tax Liability:</span>
                                <span class="font-bold text-white tabular-nums">PKR <span x-text="Math.round(calculateAnnualTax()).toLocaleString()"></span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Total Annual Gross:</span>
                                <span class="font-semibold text-slate-300 tabular-nums">PKR <span x-text="Math.round(calculateAnnualGross()).toLocaleString()"></span></span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a
                                href="{{ route('register', ['service' => 'salaried_return']) }}"
                                class="w-full block text-center py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium rounded-lg text-xs uppercase tracking-wider transition shadow-sm"
                            >
                                File This Return for PKR 3,000 &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filer vs Non-Filer Real Savings Calculator -->
    <section class="py-16 bg-slate-900 text-white relative overflow-hidden" x-data="{
        withdrawalAmount: 1000000,
        carPurchase: true,
        carPrice: 4000000,
        propertyTx: true,
        propertyPrice: 10000000,
        bankProfit: 200000,

        calculateSavings() {
            let cashSave = (this.withdrawalAmount * 0.006);
            let carSave = this.carPurchase ? (this.carPrice * 0.03) : 0;
            let propSave = this.propertyTx ? (this.propertyPrice * 0.075) : 0;
            let profitSave = (this.bankProfit * 0.15);
            return Math.round(cashSave + carSave + propSave + profitSave);
        }
    }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
                <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    <span>💡</span>
                    <span>Cost-Benefit Analysis</span>
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    How Much Money Do You Save As An Active Filer?
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto">
                    Adjust your expected annual transactions to see your direct cash savings compared to remaining a Non-Filer under FBR Finance Act 2026-27.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Left: Controls -->
                <div class="lg:col-span-7 bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 sm:p-8 space-y-6">
                    <!-- Control 1: Annual Cash Withdrawal -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-2">
                            <span class="font-semibold text-slate-300">Annual Cash Withdrawals Over PKR 50,000 / Day:</span>
                            <span class="font-mono font-bold text-blue-400 tabular-nums">PKR <span x-text="Number(withdrawalAmount).toLocaleString()"></span></span>
                        </div>
                        <input
                            type="range"
                            min="0"
                            max="5000000"
                            step="200000"
                            x-model="withdrawalAmount"
                            class="w-full accent-blue-500 cursor-pointer"
                        />
                        <div class="flex justify-between text-[11px] text-slate-500 font-mono mt-1">
                            <span>PKR 0</span>
                            <span>PKR 2.5M</span>
                            <span>PKR 5.0M</span>
                        </div>
                    </div>

                    <!-- Control 2: Vehicle Purchase -->
                    <div class="pt-4 border-t border-slate-700 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-white block">Planning to Buy / Register a Vehicle?</span>
                            <span class="text-[11px] text-slate-400">Non-Filers pay double advance withholding tax at showroom</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="carPurchase" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Control 3: Property Purchase / Sale -->
                    <div class="pt-4 border-t border-slate-700 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-white block">Buying or Selling Real Estate Property?</span>
                            <span class="text-[11px] text-slate-400">Filers pay 3% advance tax vs Non-Filers 10.5% (7.5% penalty difference)</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="propertyTx" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Control 4: Bank Profit / Savings -->
                    <div class="pt-4 border-t border-slate-700">
                        <div class="flex items-center justify-between text-xs mb-2">
                            <span class="font-semibold text-slate-300">Expected Annual Bank Profit / Prize Bonds:</span>
                            <span class="font-mono font-bold text-blue-400 tabular-nums">PKR <span x-text="Number(bankProfit).toLocaleString()"></span></span>
                        </div>
                        <input
                            type="range"
                            min="0"
                            max="1000000"
                            step="50000"
                            x-model="bankProfit"
                            class="w-full accent-blue-500 cursor-pointer"
                        />
                    </div>
                </div>

                <!-- Right: Savings Impact Box -->
                <div class="lg:col-span-5 bg-gradient-to-br from-blue-900/60 via-slate-800 to-slate-900 border border-blue-500/40 rounded-2xl p-6 sm:p-8 text-center space-y-5 shadow-xl">
                    <span class="text-xs uppercase font-bold tracking-widest text-emerald-400 block">Your Annual Net Savings</span>

                    <div class="text-3xl sm:text-5xl font-extrabold font-mono text-white tracking-tight tabular-nums">
                        PKR <span x-text="calculateSavings().toLocaleString()"></span>
                    </div>

                    <p class="text-xs text-slate-300 leading-relaxed max-w-sm mx-auto">
                        Filing your return for <strong class="text-white font-mono">PKR 3,000</strong> saves you up to <strong class="text-emerald-400 font-mono">PKR <span x-text="calculateSavings().toLocaleString()"></span></strong> in avoidable government withholding penalties!
                    </p>

                    <div class="pt-4 border-t border-slate-700/80">
                        <a
                            href="{{ route('register', ['service' => 'salaried_return']) }}"
                            class="w-full inline-block py-3 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-md shadow-blue-600/30 cursor-pointer"
                        >
                            Become Active Filer Now (PKR 3,000) &rarr;
                        </a>
                    </div>
                    <span class="text-[11px] text-slate-400 block">⚡ Instant Active Taxpayer List (ATL) Enrollment</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Products: 5 Core Cards -->
    <section id="popular-services" class="py-16 lg:py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-blue-600 text-xs font-semibold tracking-wider uppercase">What We Offer</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">
                    Products
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Aam afrad se lekar corporate enterprises tak, hamari 5 bunyadi aur teiz-tareen services
                </p>
            </div>

            <!-- The 5 Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Card 1: Individual NTN Registration -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-sm transition-all duration-200 flex flex-col justify-between group">
                    <div>
                        <span class="text-2xl p-2.5 rounded-lg bg-blue-50 inline-block mb-3 group-hover:scale-105 transition">🆔</span>
                        <h3 class="text-sm font-semibold text-slate-900 group-hover:text-blue-600 transition">Individual NTN</h3>
                        <span class="text-xs font-medium text-blue-600 block mt-0.5">"Register, Upload, Pay"</span>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Salaried afraad aur aam citizens ke liye 3 steps mein FBR NTN registration.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold font-mono text-slate-900 tabular-nums">PKR 1,500</span>
                        <a href="{{ route('register', ['service' => 'ntn_individual']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Apply &rarr;</a>
                    </div>
                </div>

                <!-- Card 2: Salaried Tax Filing -->
                <div class="p-5 rounded-xl bg-blue-50/40 border-2 border-blue-600 hover:shadow-md transition-all duration-200 flex flex-col justify-between group relative">
                    <span class="absolute -top-3 right-4 bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Most Popular</span>
                    <div>
                        <span class="text-2xl p-2.5 rounded-lg bg-blue-100 inline-block mb-3 group-hover:scale-105 transition">💼</span>
                        <h3 class="text-sm font-semibold text-slate-900 group-hover:text-blue-600 transition">Tax Return Filing</h3>
                        <span class="text-xs font-medium text-blue-600 block mt-0.5">Salaried Individuals</span>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Salary slips, bank withholding, aur FBR 114(1) active filer status in 6 mins.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-blue-200/60 flex items-center justify-between">
                        <span class="text-xs font-bold font-mono text-blue-800 tabular-nums">PKR 3,000</span>
                        <a href="{{ route('register', ['service' => 'salaried_return']) }}" class="text-xs font-bold text-blue-700 hover:text-blue-800">File Now &rarr;</a>
                    </div>
                </div>

                <!-- Card 3: Business NTN Registration -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-sm transition-all duration-200 flex flex-col justify-between group">
                    <div>
                        <span class="text-2xl p-2.5 rounded-lg bg-blue-50 inline-block mb-3 group-hover:scale-105 transition">🏬</span>
                        <h3 class="text-sm font-semibold text-slate-900 group-hover:text-blue-600 transition">Business NTN</h3>
                        <span class="text-xs font-medium text-slate-500 block mt-0.5">Sole Proprietor Setup</span>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Apne existing NTN par karobar ka naam aur category shamil karwayein.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold font-mono text-slate-900 tabular-nums">PKR 3,000</span>
                        <a href="{{ route('register', ['service' => 'ntn_business']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Apply &rarr;</a>
                    </div>
                </div>

                <!-- Card 4: Sales Tax Registration -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-sm transition-all duration-200 flex flex-col justify-between group">
                    <div>
                        <span class="text-2xl p-2.5 rounded-lg bg-blue-50 inline-block mb-3 group-hover:scale-105 transition">📜</span>
                        <h3 class="text-sm font-semibold text-slate-900 group-hover:text-blue-600 transition">Sales Tax (GST)</h3>
                        <span class="text-xs font-medium text-slate-500 block mt-0.5">STRN Registration</span>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            FBR, PRA, SRB ya KPRA se Sales Tax Registration Number (STRN) hasil karein.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold font-mono text-slate-900 tabular-nums">PKR 12,000</span>
                        <a href="{{ route('register', ['service' => 'sales_tax_reg']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Apply &rarr;</a>
                    </div>
                </div>

                <!-- Card 5: Business Tax Filing -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-sm transition-all duration-200 flex flex-col justify-between group">
                    <div>
                        <span class="text-2xl p-2.5 rounded-lg bg-blue-50 inline-block mb-3 group-hover:scale-105 transition">🏪</span>
                        <h3 class="text-sm font-semibold text-slate-900 group-hover:text-blue-600 transition">Business Filing</h3>
                        <span class="text-xs font-medium text-slate-500 block mt-0.5">Traders & Freelancers</span>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Comprehensive P&L, balance sheet, aur revenue return submission on IRIS.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold font-mono text-slate-900 tabular-nums">PKR 4,500</span>
                        <a href="{{ route('register', ['service' => 'business_return']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">File Now &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Corporate Services Spotlight -->
    <section class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-blue-600 text-xs font-semibold tracking-wider uppercase">Corporate & Business Solutions</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">
                        Looking for SECP, IPO, or USA LLC Setup?
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                        Hamare corporate consultants SECP company incorporation se lekar international IT export setups tak mukammal legal compliance provide karte hain.
                    </p>
                </div>
                <a
                    href="{{ route('services.index') }}"
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition shadow-sm self-start md:self-auto flex items-center space-x-1.5"
                >
                    <span>Explore Business Services</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- SECP Pvt Ltd -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-xs transition">
                    <span class="text-2xl p-2 rounded-xl bg-blue-50 inline-block mb-3">🏢</span>
                    <h3 class="text-sm font-semibold text-slate-900">SECP Private Limited</h3>
                    <p class="text-xs text-slate-500 mt-1">Company name reservation, Digital Signature, Form 29 & Certified Incorporation.</p>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="font-mono font-bold text-slate-900 tabular-nums">PKR 18,000</span>
                        <a href="{{ route('services.index', ['tab' => 'company']) }}" class="text-blue-600 font-medium hover:underline">Details &rarr;</a>
                    </div>
                </div>

                <!-- Sales Tax STRN -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-xs transition">
                    <span class="text-2xl p-2 rounded-xl bg-purple-50 inline-block mb-3">📑</span>
                    <h3 class="text-sm font-semibold text-slate-900">Sales Tax (GST / PRA)</h3>
                    <p class="text-xs text-slate-500 mt-1">Biometric verification, IRIS STRN portal enrollment, and post-reg assistance.</p>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="font-mono font-bold text-slate-900 tabular-nums">PKR 12,000</span>
                        <a href="{{ route('services.index', ['tab' => 'sales']) }}" class="text-blue-600 font-medium hover:underline">Details &rarr;</a>
                    </div>
                </div>

                <!-- Trademark Registration -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-xs transition">
                    <span class="text-2xl p-2 rounded-xl bg-amber-50 inline-block mb-3">™️</span>
                    <h3 class="text-sm font-semibold text-slate-900">Trademark (IPO Pakistan)</h3>
                    <p class="text-xs text-slate-500 mt-1">Brand name & logo protection under Trade Marks Registry to prevent copying.</p>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="font-mono font-bold text-slate-900 tabular-nums">PKR 25,000</span>
                        <a href="{{ route('services.index', ['tab' => 'intellectual']) }}" class="text-blue-600 font-medium hover:underline">Details &rarr;</a>
                    </div>
                </div>

                <!-- USA Delaware / Wyoming LLC -->
                <div class="p-5 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-xs transition">
                    <span class="text-2xl p-2 rounded-xl bg-emerald-50 inline-block mb-3">🌐</span>
                    <h3 class="text-sm font-semibold text-slate-900">USA LLC Setup & EIN</h3>
                    <p class="text-xs text-slate-500 mt-1">Stripe, PayPal, and Mercury US bank account opening for Pakistani IT exporters.</p>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="font-mono font-bold text-slate-900 tabular-nums">USD 250</span>
                        <a href="{{ route('services.index', ['tab' => 'llc']) }}" class="text-blue-600 font-medium hover:underline">Details &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose TaxFiler.pk (Comparison Table) -->
    <section id="why-us" class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-blue-600 text-xs font-semibold tracking-wider uppercase">Unmatched Convenience</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">Why Pakistanis Choose TaxFiler.pk</h2>
                <p class="text-xs text-slate-500 mt-1">Compare traditional tax filing methods with our modern 6-minute automated platform.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Feature / Benefit</th>
                            <th class="py-3 px-4 bg-blue-50 text-blue-900 font-bold">TaxFiler.pk (Us)</th>
                            <th class="py-3 px-4">Traditional Tax Lawyers</th>
                            <th class="py-3 px-4">Complicated Portals</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-semibold text-slate-900">Filing Time Required</td>
                            <td class="py-3 px-4 bg-blue-50/50 font-semibold text-blue-700">⚡ 6 Minutes (Self-Service)</td>
                            <td class="py-3 px-4 text-slate-500">3 to 7 Days (Multiple visits)</td>
                            <td class="py-3 px-4 text-slate-500">45+ Minutes (Complex)</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-semibold text-slate-900">Accounting / Wealth Formulas</td>
                            <td class="py-3 px-4 bg-blue-50/50 font-semibold text-blue-700">✓ None (Handled automatically)</td>
                            <td class="py-3 px-4 text-slate-500">Must bring bank receipts & invoices</td>
                            <td class="py-3 px-4 text-slate-500">Manual asset reconciliation errors</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-semibold text-slate-900">Salaried Filing Fee</td>
                            <td class="py-3 px-4 bg-blue-50/50 font-mono font-bold text-blue-700 tabular-nums">PKR 3,000 (Transparent)</td>
                            <td class="py-3 px-4 font-mono text-slate-500">PKR 8,000 - 15,000</td>
                            <td class="py-3 px-4 font-mono text-slate-500">PKR 4,500+ hidden charges</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-semibold text-slate-900">Consultant WhatsApp Support</td>
                            <td class="py-3 px-4 bg-blue-50/50 font-semibold text-blue-700">✓ Dedicated Direct Chat</td>
                            <td class="py-3 px-4 text-slate-500">In-person office visits only</td>
                            <td class="py-3 px-4 text-slate-500">Slow email tickets</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-semibold text-slate-900">Official FBR 114(1) CPR Slip</td>
                            <td class="py-3 px-4 bg-blue-50/50 font-semibold text-blue-700">✓ Instant Portal Download</td>
                            <td class="py-3 px-4 text-slate-500">Wait for photocopy / courier</td>
                            <td class="py-3 px-4 text-slate-500">Delay up to 5 days</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Required Documents Checklist Guide -->
    <section id="documents" class="py-16 bg-slate-50 border-t border-slate-200" x-data="{ docTab: 'salaried' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10 space-y-2">
                <span class="text-blue-600 text-xs font-semibold tracking-wider uppercase">Document Guide</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    What Documents Do You Actually Need?
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    No physical copies or couriers needed. Simply snap a clear photo or upload PDF from your phone.
                </p>
            </div>

            <!-- Tab Pills -->
            <div class="flex flex-wrap justify-center gap-2 mb-8">
                <button
                    type="button"
                    @click="docTab = 'salaried'"
                    :class="docTab === 'salaried' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer"
                >
                    💼 Salaried Individual Return
                </button>
                <button
                    type="button"
                    @click="docTab = 'business'"
                    :class="docTab === 'business' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer"
                >
                    🏪 Business / Freelancer Return
                </button>
                <button
                    type="button"
                    @click="docTab = 'ntn'"
                    :class="docTab === 'ntn' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer"
                >
                    🆔 Fresh NTN Registration
                </button>
            </div>

            <!-- Tab 1: Salaried -->
            <div x-show="docTab === 'salaried'" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">📄</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">1. Annual Salary Certificate / Pay Slips</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Showing monthly gross pay and tax deducted under Section 149 by employer.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">🏦</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">2. Bank Account Statements (1 July - 30 June)</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">For closing balance on 30th June and withholding tax deductions certificate.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">💡</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">3. Paid Utility Bills / Mobile Tax Certificate</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Electricity bill paid tax and telco tax deduction slip to reduce payable tax.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">🚗</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">4. Vehicle / Property Details (If acquired)</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Registration book or sale deed if purchased during the tax year.</p>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Business -->
            <div x-show="docTab === 'business'" x-cloak class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">📊</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">1. Annual Sales / Revenue Summary</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Gross revenue earned through invoices, local sales or foreign remittances.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">🧾</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">2. Business Operating Expenses Record</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Rent, salaries, internet, software subscriptions, and raw material costs.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">🏦</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">3. Business Bank Account Statement</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Complete 12-month statement reflecting inflows and business closing balance.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">📑</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">4. Advance Tax / Withholding Invoices</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Tax deducted by clients, payment gateways or vendors under Section 153.</p>
                    </div>
                </div>
            </div>

            <!-- Tab 3: NTN -->
            <div x-show="docTab === 'ntn'" x-cloak class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">🪪</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">1. Original CNIC (Front & Back)</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Clear color photo of valid NADRA Computerized National Identity Card.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">📱</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">2. Biometric Registered Mobile SIM</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Cell number must be registered against applicant's own CNIC for FBR OTP.</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">⚡</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">3. Paid Electricity Bill (Recent Month)</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">For residential address verification (ownership is not required).</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start space-x-3">
                    <span class="text-xl">✉️</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">4. Personal Email Address</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Where official FBR 181 registration certificate and PIN will be delivered.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-blue-600 text-xs font-semibold tracking-wider uppercase">Client Feedback</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">What Our Clients Say</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex text-amber-500 text-sm mb-3">★★★★★</div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Pehli martaba tax file karne ka itna aasan aur teiz tareen experience mila. Sirf apni salary aur bank slip lagayi aur aglay hi din mujhe FBR CPR receipt mil gayi!"
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200 text-xs font-semibold text-slate-900">
                        Zeeshan Tariq <span class="block text-[10px] text-slate-400 font-normal">Software Engineer, Lahore</span>
                    </div>
                </div>

                <div class="p-6 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex text-amber-500 text-sm mb-3">★★★★★</div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "Mera pehli dafa filer banne ka tajurba bohat smooth raha. Pehle NTN bana phir return file hua bina kisi dafter ke chakkar ke."
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200 text-xs font-semibold text-slate-900">
                        Fatima Zahra <span class="block text-[10px] text-slate-400 font-normal">Bank Officer, Karachi</span>
                    </div>
                </div>

                <div class="p-6 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex text-amber-500 text-sm mb-3">★★★★★</div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "FBR ke codes samajh nahi aate the. Is platform par simple Urdu sawalat the aur consultant ne foran WhatsApp par support di."
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-200 text-xs font-semibold text-slate-900">
                        Hamza Asif <span class="block text-[10px] text-slate-400 font-normal">Business Owner, Islamabad</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 text-xs py-14 border-t border-slate-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
                <div>
                    <h4 class="text-white font-semibold text-sm mb-3">Tax Services</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('register', ['service' => 'salaried_return']) }}" class="hover:text-white transition">Salaried Tax Return (PKR 3,000)</a></li>
                        <li><a href="{{ route('register', ['service' => 'business_return']) }}" class="hover:text-white transition">Business Tax Return (PKR 4,500)</a></li>
                        <li><a href="{{ route('register', ['service' => 'ntn_individual']) }}" class="hover:text-white transition">Individual NTN (PKR 1,500)</a></li>
                        <li><a href="{{ route('register', ['service' => 'ntn_business']) }}" class="hover:text-white transition">Business NTN Setup</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-3">Corporate Services</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('services.index', ['tab' => 'company']) }}" class="hover:text-white transition">SECP Company Incorporation</a></li>
                        <li><a href="{{ route('services.index', ['tab' => 'sales']) }}" class="hover:text-white transition">Sales Tax STRN (GST / PRA)</a></li>
                        <li><a href="{{ route('services.index', ['tab' => 'intellectual']) }}" class="hover:text-white transition">IPO Trademark Registration</a></li>
                        <li><a href="{{ route('services.index', ['tab' => 'llc']) }}" class="hover:text-white transition">USA LLC & EIN Setup</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-3">Tools & Guides</h4>
                    <ul class="space-y-2">
                        <li><a href="#calculator" class="hover:text-white transition">Salary Tax Calculator 2026-27</a></li>
                        <li><a href="#atl-checker" class="hover:text-white transition">Active Taxpayer (ATL) Checker</a></li>
                        <li><a href="{{ route('track') }}" class="hover:text-white transition">Live Application Tracker</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition">Tax Knowledge Base & FAQs</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-3">Contact & Support</h4>
                    <ul class="space-y-2">
                        <li class="text-white font-bold">📞 +92 300 1234567</li>
                        <li>✉️ support@taxfiler.pk</li>
                        <li>💬 WhatsApp Support: 0300-1234567</li>
                        <li><a href="{{ route('contact') }}" class="text-blue-400 hover:underline">Office Locations & Consultation &rarr;</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; {{ date('Y') }} TaxFiler.pk. All rights reserved. 100% Secure FBR IRIS Integrated Platform.</p>
                <div class="flex flex-wrap gap-x-6 gap-y-2">
                    <a href="{{ route('track') }}" class="hover:text-white transition">Track Status</a>
                    <a href="{{ route('faq') }}" class="hover:text-white transition">Guides & FAQ</a>
                    <a href="{{ route('contact') }}" class="hover:text-white transition">Contact Us</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition">Client Portal</a>
                    <a href="{{ route('services.index') }}" class="hover:text-white transition">Services Catalog</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Sticky Floating WhatsApp Assistant -->
    <div class="fixed bottom-6 right-6 z-40">
        <a
            href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20TaxFiler%20tax%20filing%20aur%20corporate%20services%20ke%20bare%20mein%20maloomat%20chahiye."
            target="_blank"
            class="flex items-center space-x-2.5 bg-[#25D366] hover:bg-[#1ebd59] text-slate-950 font-bold px-4 py-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group cursor-pointer border border-white/20"
            title="Chat with Tax Expert on WhatsApp"
        >
            <svg class="w-5 h-5 fill-current text-slate-950" viewBox="0 0 24 24">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.791.82 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.766-5.767-5.766zm3.385 8.165c-.144.405-.837.774-1.17.823-.312.043-.681.085-2.227-.552-1.776-.732-2.871-2.544-2.958-2.661-.087-.116-.717-.954-.717-1.821 0-.866.452-1.294.613-1.468.161-.173.351-.216.468-.216.116 0 .233.002.333.007.106.006.248-.04.389.297.144.348.491 1.199.534 1.286.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.179.182-.077.357.102.174.453.748.972 1.211.669.597 1.233.782 1.408.869.174.087.276.072.378-.043.102-.116.435-.507.551-.681.116-.174.233-.145.391-.087s1.015.478 1.189.565c.174.087.29.13.333.203.043.072.043.419-.101.824zM12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.2-1.31c1.42.79 3.05 1.31 4.8 1.31 5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
            </svg>
            <span class="text-xs font-bold font-sans">Chat on WhatsApp</span>
        </a>
    </div>

</body>
</html>
