<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.guest'" header="Tax Guides & FAQs" title="Tax Knowledge Base & FAQs - TaxFiler.pk">
    <div class="max-w-5xl mx-auto space-y-10 py-4" x-data="{ activeCategory: 'all', openFaq: 1 }">
        <!-- Header -->
        <div class="text-center space-y-3">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                <span>📚</span>
                <span>Pakistan Tax Guide 2026-27</span>
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Tax Knowledge Base & Frequently Asked Questions
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-2xl mx-auto">
                Simple, straightforward answers to Pakistan income tax laws, FBR Active Taxpayer status benefits, and corporate registrations without confusing accounting jargon.
            </p>
        </div>

        <!-- Category Filters -->
        <div class="flex flex-wrap items-center justify-center gap-2">
            <button
                type="button"
                @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
            >
                All Topics
            </button>
            <button
                type="button"
                @click="activeCategory = 'filer_benefits'"
                :class="activeCategory === 'filer_benefits' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
            >
                ⚡ Filer vs Non-Filer
            </button>
            <button
                type="button"
                @click="activeCategory = 'salaried'"
                :class="activeCategory === 'salaried' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
            >
                💼 Salaried Individuals
            </button>
            <button
                type="button"
                @click="activeCategory = 'business_ntn'"
                :class="activeCategory === 'business_ntn' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
            >
                🏢 NTN & Company Setup
            </button>
            <button
                type="button"
                @click="activeCategory = 'wealth'"
                :class="activeCategory === 'wealth' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
            >
                💰 Wealth Reconciliation
            </button>
        </div>

        <!-- Quick Key Fact Cards (3 Pillars) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <span class="text-2xl">📉</span>
                <h3 class="text-sm font-bold text-slate-800 mt-2">Zero Tax up to PKR 600,000</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Agar aapki saalana aamdani PKR 600,000 (PKR 50,000/month) tak hai to tax 0% hai. Phir bhi Filer ban kar 50% extra withholding tax se bach sakte hain.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <span class="text-2xl">🚗</span>
                <h3 class="text-sm font-bold text-slate-800 mt-2">Property & Vehicle WHT Savings</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Gari ki purchase par non-filer ko double tax lagta hai. Property par filer tax 3% hai jabke non-filer par 10.5% tak bharna parta hai.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <span class="text-2xl">📱</span>
                <h3 class="text-sm font-bold text-slate-800 mt-2">Electricity & Mobile Tax Adjustments</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Mobile load aur bijli ke bill par kata hua advance tax return mein adjust ho kar aapka payable tax kam karta hai ya refund banata hai.
                </p>
            </div>
        </div>

        <!-- Accordion FAQ Questions List (Dynamic from CMS) -->
        <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 shadow-sm overflow-hidden">
            @forelse($faqs as $faq)
                <div class="p-5 sm:p-6" x-show="activeCategory === 'all' || activeCategory === '{{ $faq->category }}'">
                    <button
                        type="button"
                        @click="openFaq = (openFaq === {{ $faq->id }} ? null : {{ $faq->id }})"
                        class="w-full flex items-center justify-between text-left cursor-pointer"
                    >
                        <div>
                            <span class="text-sm sm:text-base font-bold text-slate-800 block">
                                {{ $faq->question }}
                            </span>
                            @if($faq->urdu_question)
                                <span class="text-xs text-blue-600 block mt-0.5 font-medium">
                                    {{ $faq->urdu_question }}
                                </span>
                            @endif
                        </div>
                        <span class="ml-4 w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 flex-shrink-0" x-text="openFaq === {{ $faq->id }} ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === {{ $faq->id }}" x-collapse x-cloak class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed space-y-2">
                        {!! \Illuminate\Support\Str::markdown($faq->answer, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400">
                    <p class="text-3xl mb-2">📚</p>
                    <p class="text-sm font-semibold text-slate-700">Abhi koi FAQ available nahi hai</p>
                </div>
            @endforelse
        </div>

        <!-- Direct CTA Banner -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 text-white text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-6 shadow-md">
            <div>
                <h3 class="text-xl font-bold">Aapka koi khaas sawal hai jo yahan nahi mila?</h3>
                <p class="text-xs text-blue-100 mt-1 max-w-lg">
                    Hamare High Court aur FBR certified advocates se direct rabta karein aur confidential consultation hasil karein.
                </p>
            </div>
            <div class="flex items-center space-x-3 flex-shrink-0">
                <a
                    href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20tax%20ke%20hawaale%20se%20guidance%20chahiye."
                    target="_blank"
                    class="px-5 py-3 bg-white text-blue-700 hover:bg-blue-50 font-bold rounded-xl text-xs transition shadow-sm"
                >
                    WhatsApp Consultant &rarr;
                </a>
            </div>
        </div>
    </div>
</x-dynamic-component>
