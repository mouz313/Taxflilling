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
        class="w-full space-y-4"
    >
        <!-- 1. AdminLTE Callout Banner -->
        <div class="callout callout-info flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white">
            <div>
                <h5 class="text-sm font-bold text-[#117a8b] m-0 flex items-center gap-2">
                    <i class="fas fa-shield-alt"></i>
                    <span>Federal Board of Revenue (FBR) IRIS 2.0 Live Public Verification Desk</span>
                </h5>
                <p class="text-xs text-[#6c757d] mt-1 m-0">
                    Active Taxpayer List (ATL) status, NTN legal profile aur CPR tax challans ki electronic verification karein.
                </p>
            </div>
            <div>
                <a
                    href="https://iris.fbr.gov.pk"
                    target="_blank"
                    class="btn btn-xs btn-outline-primary font-bold inline-flex items-center gap-1.5"
                >
                    <span>FBR IRIS Portal</span>
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- 2. AdminLTE Card with Tabs -->
        <div class="card card-success card-outline card-outline-tabs w-full">
            <!-- Nav Tabs Header -->
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs flex flex-wrap border-b border-[#dee2e6] bg-[#f8f9fa] px-3 pt-2">
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'atl'; result = null; errorMessage = '';"
                            :class="activeTab === 'atl' ? 'active bg-white text-[#28a745] border-t-2 border-t-[#28a745] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-search mr-1.5"></i>
                            <span>Active Taxpayer List (ATL)</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'ntn'; result = null; errorMessage = '';"
                            :class="activeTab === 'ntn' ? 'active bg-white text-[#28a745] border-t-2 border-t-[#28a745] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-building mr-1.5"></i>
                            <span>Online NTN &amp; Profile Inquiry</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'cpr'; result = null; errorMessage = '';"
                            :class="activeTab === 'cpr' ? 'active bg-white text-[#28a745] border-t-2 border-t-[#28a745] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-receipt mr-1.5"></i>
                            <span>CPR / PSID Challan Validator</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Card Body: Input Form -->
            <div class="card-body p-6">
                <form @submit.prevent="performVerification()" class="max-w-2xl mx-auto space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-[#212529] mb-1.5">
                            <span x-show="activeTab === 'atl'">Enter 13-Digit Pakistani CNIC (Bina dashes ya dashes ke sath):</span>
                            <span x-show="activeTab === 'ntn'">Enter CNIC (13 Digits) ya NTN Number (7 Digits):</span>
                            <span x-show="activeTab === 'cpr'">Enter CPR Number (e.g. IT-2026-XXXXXXX) ya PSID 1Bill Number:</span>
                        </label>

                        <!-- Input Group with Button -->
                        <div class="flex items-stretch rounded shadow-2xs">
                            <span class="inline-flex items-center px-3 bg-[#f8f9fa] border border-r-0 border-[#ced4da] rounded-l text-[#6c757d] text-sm">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="text"
                                x-model="searchQuery"
                                :placeholder="activeTab === 'atl' ? 'e.g. 42101-1234567-1' : (activeTab === 'ntn' ? 'e.g. 42101-1234567-1 ya 1234567' : 'e.g. IT-2026-9281928')"
                                class="form-control rounded-none text-xs font-mono"
                            />
                            <button
                                type="submit"
                                :disabled="loading"
                                class="btn btn-success rounded-l-none font-bold text-xs px-4"
                            >
                                <span x-show="!loading"><i class="fas fa-check-circle mr-1"></i> Verify Live</span>
                                <span x-show="loading"><i class="fas fa-spinner fa-spin mr-1"></i> Checking...</span>
                            </button>
                        </div>

                        <p class="text-[11px] text-[#6c757d] mt-1.5">
                            Direct connection to Federal Board of Revenue public registry database.
                        </p>
                    </div>

                    <!-- Error Alert -->
                    <div x-show="errorMessage" class="p-3 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded text-xs font-semibold" x-text="errorMessage"></div>
                </form>
            </div>
        </div>

        <!-- 3. Live Verification Result Dossier Card -->
        <template x-if="result">
            <div class="card card-outline card-success max-w-3xl mx-auto shadow-sm animate-in fade-in zoom-in-95 duration-200">
                <div class="card-header flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-[#d4edda] text-[#155724] flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h3 class="card-title text-sm font-bold text-[#212529]" x-text="result.name || result.legal_name || 'Verified FBR Record'"></h3>
                            <div class="text-[11px] text-[#6c757d] font-mono" x-text="result.cnic || result.cpr_no || result.ntn"></div>
                        </div>
                    </div>

                    <div>
                        <span
                            class="badge px-3 py-1 font-bold text-xs"
                            :class="result.is_active || result.status?.includes('Active') || result.status?.includes('Realized') ? 'badge-success' : 'badge-danger'"
                            x-text="result.status || 'Verified'"
                        ></span>
                    </div>
                </div>

                <div class="card-body p-6 space-y-4">
                    <!-- ATL Details Grid -->
                    <template x-if="activeTab === 'atl'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Tax Period / Slabs</span>
                                <span class="font-bold text-[#212529] text-sm" x-text="result.tax_year"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Surcharge Compliance</span>
                                <span class="font-bold text-[#28a745] text-sm" x-text="result.surcharge_status"></span>
                            </div>
                            <div class="p-3 bg-[#e7f1ff] rounded border border-[#b8daff] sm:col-span-2">
                                <span class="text-[#004085] font-bold block mb-0.5">Withholding Tax Benefit Consequence:</span>
                                <span class="text-[#004085] font-medium" x-text="result.filer_benefit"></span>
                            </div>
                        </div>
                    </template>

                    <!-- NTN Details Grid -->
                    <template x-if="activeTab === 'ntn'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">National Tax Number (NTN)</span>
                                <span class="font-bold font-mono text-[#212529]" x-text="result.ntn"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Registration Category</span>
                                <span class="font-bold text-[#212529]" x-text="result.registration_type"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Assigned Tax Office (RTO)</span>
                                <span class="font-bold text-[#007bff]" x-text="result.assigned_rto"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Registration Date</span>
                                <span class="font-bold text-[#212529]" x-text="result.registration_date"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6] sm:col-span-2">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Principal Business Activity &amp; Sector</span>
                                <span class="font-bold text-[#212529]" x-text="result.principal_activity"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6] sm:col-span-2">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Registered Business Address</span>
                                <span class="font-medium text-[#495057]" x-text="result.registered_address"></span>
                            </div>
                        </div>
                    </template>

                    <!-- CPR Details Grid -->
                    <template x-if="activeTab === 'cpr'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Tax Head / Account</span>
                                <span class="font-bold text-[#212529]" x-text="result.tax_head"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Payment Amount Paid</span>
                                <span class="font-bold font-mono text-[#28a745] text-sm" x-text="result.formatted_amount"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Clearing Bank</span>
                                <span class="font-bold text-[#212529]" x-text="result.clearing_bank"></span>
                            </div>
                            <div class="p-3 bg-[#f8f9fa] rounded border border-[#dee2e6]">
                                <span class="text-[#6c757d] block text-[11px] uppercase font-bold tracking-wider">Payment Date</span>
                                <span class="font-bold text-[#212529]" x-text="result.payment_date"></span>
                            </div>
                            <div class="p-3 bg-[#d4edda] rounded border border-[#c3e6cb] sm:col-span-2">
                                <span class="text-[#155724] font-bold block mb-0.5">Realization &amp; Treasury Status:</span>
                                <span class="text-[#155724] font-medium" x-text="result.status"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer Stamp -->
                <div class="card-footer bg-[#f8f9fa] border-t border-[#dee2e6] py-2.5 px-6 flex items-center justify-between text-[11px] text-[#6c757d]">
                    <span x-text="'Verified by ' + (result.verified_by || 'FBR Electronic Registry')"></span>
                    <span class="font-mono" x-text="result.verification_date || 'Live Sync'"></span>
                </div>
            </div>
        </template>
    </div>
</x-layouts.app>
