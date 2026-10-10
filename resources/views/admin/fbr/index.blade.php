<x-layouts.app header="FBR IRIS 2.0 Live Verification Bridge">
    <div
        x-data="{
            activeTab: 'atl',
            searchQuery: '',
            loading: false,
            result: null,
            errorMessage: '',

            performVerification() {
                if (!this.searchQuery.trim()) {
                    this.errorMessage = 'Barah-e-karam number darj karein.';
                    return;
                }
                this.loading = true;
                this.errorMessage = '';
                this.result = null;

                fetch('/admin/fbr-verification/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: this.activeTab,
                        query: this.searchQuery
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.result = data;
                    } else {
                        this.errorMessage = data.message || 'Verification fail ho gayi.';
                    }
                })
                .catch(err => {
                    this.loading = false;
                    this.errorMessage = 'Server se rabta nahi ho saka.';
                });
            }
        }"
        class="space-y-6"
    >
        <!-- 1. Executive Identity Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 text-white shadow-md flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        🏛️ FBR e-Services &bull; IRIS 2.0 Live Bridge
                    </span>
                    <span class="text-xs text-slate-400 font-mono">/admin/fbr-verification</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">FBR IRIS 2.0 Live Public Verification Desk</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Federal Board of Revenue (FBR) e-Services registry se Active Taxpayer List (ATL) status, NTN legal profile aur CPR tax challans ki live verification karein.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="https://iris.fbr.gov.pk" target="_blank" class="px-3.5 py-2 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs transition flex items-center space-x-1.5">
                    <span>FBR IRIS Portal ↗</span>
                </a>
            </div>
        </div>

        <!-- 2. Tool Navigation Tabs -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-2 flex flex-wrap items-center gap-2">
            <button
                type="button"
                @click="activeTab = 'atl'; result = null; errorMessage = '';"
                :class="activeTab === 'atl' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-semibold'"
                class="px-4 py-2 text-xs rounded-lg transition flex items-center space-x-2"
            >
                <span>🔍</span>
                <span>Active Taxpayer List (ATL) Checker</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'ntn'; result = null; errorMessage = '';"
                :class="activeTab === 'ntn' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-semibold'"
                class="px-4 py-2 text-xs rounded-lg transition flex items-center space-x-2"
            >
                <span>🏢</span>
                <span>Online NTN &amp; Taxpayer Profile Inquiry</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'cpr'; result = null; errorMessage = '';"
                :class="activeTab === 'cpr' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-semibold'"
                class="px-4 py-2 text-xs rounded-lg transition flex items-center space-x-2"
            >
                <span>🧾</span>
                <span>CPR / PSID Challan Validator</span>
            </button>
        </div>

        <!-- 3. Search & Input Box -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6">
            <form @submit.prevent="performVerification()" class="max-w-2xl mx-auto space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        <span x-show="activeTab === 'atl'">Enter 13-Digit Pakistani CNIC (Bina dashes ya dashes ke sath)</span>
                        <span x-show="activeTab === 'ntn'">Enter CNIC (13 Digits) ya NTN Number (7 Digits)</span>
                        <span x-show="activeTab === 'cpr'">Enter CPR Number (e.g. IT-2026-XXXXXXX) ya PSID 1Bill Number</span>
                    </label>

                    <div class="relative flex items-center">
                        <input
                            type="text"
                            x-model="searchQuery"
                            :placeholder="activeTab === 'atl' ? 'e.g. 42101-1234567-1' : (activeTab === 'ntn' ? 'e.g. 42101-1234567-1 ya 1234567' : 'e.g. IT-2026-9281928')"
                            class="w-full text-sm rounded-xl border border-slate-300 pl-10 pr-28 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-mono"
                        />
                        <span class="absolute left-3.5 text-slate-400 text-sm">🔍</span>

                        <button
                            type="submit"
                            :disabled="loading"
                            class="absolute right-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-xs transition disabled:opacity-50"
                        >
                            <span x-show="!loading">Verify Live</span>
                            <span x-show="loading" class="animate-pulse">Checking...</span>
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-400 mt-1.5">
                        Direct connection to Federal Board of Revenue public registry database.
                    </p>
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" class="p-3 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-medium" x-text="errorMessage"></div>
            </form>
        </div>

        <!-- 4. Live Verification Output Card -->
        <template x-if="result">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-3xl mx-auto space-y-5 animate-in fade-in duration-300">
                <!-- Result Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-bold">
                            ✓
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" x-text="result.name || result.legal_name || 'Verified FBR Record'"></h3>
                            <div class="text-xs text-slate-500 font-mono" x-text="result.cnic || result.cpr_no || result.ntn"></div>
                        </div>
                    </div>

                    <div>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold"
                            :class="result.is_active || result.status?.includes('Active') || result.status?.includes('Realized') ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                            x-text="result.status || 'Verified'"
                        ></span>
                    </div>
                </div>

                <!-- ATL Details Grid -->
                <template x-if="activeTab === 'atl'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Tax Period / Slabs</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="result.tax_year"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Surcharge Compliance</span>
                            <span class="font-bold text-emerald-700 text-sm" x-text="result.surcharge_status"></span>
                        </div>
                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 sm:col-span-2">
                            <span class="text-blue-900 font-bold block mb-0.5">Withholding Tax Benefit Consequence:</span>
                            <span class="text-blue-800 font-medium" x-text="result.filer_benefit"></span>
                        </div>
                    </div>
                </template>

                <!-- NTN Details Grid -->
                <template x-if="activeTab === 'ntn'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">National Tax Number (NTN)</span>
                            <span class="font-bold font-mono text-slate-800" x-text="result.ntn"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Registration Category</span>
                            <span class="font-bold text-slate-800" x-text="result.registration_type"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Assigned Tax Office (RTO)</span>
                            <span class="font-bold text-blue-700" x-text="result.assigned_rto"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Registration Date</span>
                            <span class="font-bold text-slate-800" x-text="result.registration_date"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 sm:col-span-2">
                            <span class="text-slate-400 block text-[11px]">Principal Business Activity &amp; Sector</span>
                            <span class="font-bold text-slate-800" x-text="result.principal_activity"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 sm:col-span-2">
                            <span class="text-slate-400 block text-[11px]">Registered Business Address</span>
                            <span class="font-medium text-slate-700" x-text="result.registered_address"></span>
                        </div>
                    </div>
                </template>

                <!-- CPR Details Grid -->
                <template x-if="activeTab === 'cpr'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Tax Head / Account</span>
                            <span class="font-bold text-slate-800" x-text="result.tax_head"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Payment Amount Paid</span>
                            <span class="font-bold font-mono text-emerald-700 text-sm" x-text="result.formatted_amount"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Clearing Bank</span>
                            <span class="font-bold text-slate-800" x-text="result.clearing_bank"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Payment Date</span>
                            <span class="font-bold text-slate-800" x-text="result.payment_date"></span>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 sm:col-span-2">
                            <span class="text-emerald-900 font-bold block mb-0.5">Realization &amp; Treasury Status:</span>
                            <span class="text-emerald-800 font-medium" x-text="result.status"></span>
                        </div>
                    </div>
                </template>

                <!-- Verification Stamp Footer -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span x-text="'Verified by ' + (result.verified_by || 'FBR Electronic Registry')"></span>
                    <span x-text="result.verification_date || 'Live'"></span>
                </div>
            </div>
        </template>
    </div>
</x-layouts.app>
