<x-layouts.app :header="'TaxFiler Return Wizard: Step ' . $step . ' of 12'">
    <div class="max-w-7xl mx-auto space-y-6" x-data="{
        wizardLang: 'ur',
        salaryFreq: 'annual',
        inputSalary: {{ $return->incomeItems->where('source_type', 'salary')->first()?->gross_amount ?? 1800000 }},
        taxDeducted: {{ $return->incomeItems->where('source_type', 'salary')->first()?->tax_deducted ?? 0 }},

        hasCharity: false,
        hasZakat: false,
        hasVehicle: false,
        hasProperty: false,
        hasFbrCreds: false,

        computeLiveTax() {
            let annual = (this.salaryFreq === 'monthly') ? (this.inputSalary * 12) : Number(this.inputSalary);
            if (annual <= 600000) return 0;
            if (annual <= 1200000) return (annual - 600000) * 0.05;
            if (annual <= 2200000) return 30000 + (annual - 1200000) * 0.15;
            if (annual <= 3200000) return 180000 + (annual - 2200000) * 0.25;
            if (annual <= 4100000) return 430000 + (annual - 3200000) * 0.30;
            return 700000 + (annual - 4100000) * 0.35;
        }
    }">
        @php
            $stepTitles = [
                1 => 'Disclaimer & Terms',
                2 => 'Filer Category Selection',
                3 => 'Personal Information',
                4 => 'Tax Year Selection',
                5 => 'Salary & Tax (Live Estimator)',
                6 => 'Tax Credits (Charity/VPS)',
                7 => 'Tax Deductions (Zakat)',
                8 => 'Assets (Bank & Property)',
                9 => 'Living Expenses (Code 7087)',
                10 => 'FBR Credentials / OTP Consent',
                11 => 'Review & Calculation Summary',
                12 => 'Payment (PKR 3,000) & Submit',
            ];

            $wizardPhases = ['Terms', 'Category', 'Personal', 'Income', 'Wealth & Tax', 'Review & Pay'];
            $currentPhase = match(true) {
                $step === 1 => 1,
                $step === 2 => 2,
                $step <= 4 => 3,
                $step === 5 => 4,
                $step <= 10 => 5,
                default => 6,
            };
        @endphp

        <!-- Top Progress & Auto-Save Header (Spec 3.6, 3.15, 4.3) -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">
                        Step {{ $step }} of 12
                    </span>
                    <h2 class="text-base font-semibold text-slate-800">
                        {{ $stepTitles[$step] ?? 'Tax Filing' }}
                    </h2>
                </div>

                <div class="flex items-center space-x-3">
                    <button
                        type="button"
                        @click="wizardLang = (wizardLang === 'ur' ? 'en' : 'ur')"
                        class="text-[11px] font-medium px-2 py-0.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition cursor-pointer"
                    >
                        <span x-text="wizardLang === 'ur' ? 'English' : 'اردو / Roman'"></span>
                    </button>

                    <!-- Auto-save indicator -->
                    <div class="flex items-center space-x-1.5 text-xs text-slate-500 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Saved just now</span>
                    </div>
                </div>
            </div>

            <!-- Stepper Component -->
            <x-ui.stepper :steps="$wizardPhases" :current="$currentPhase" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Main Form Card (Col 8) -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
                <form method="POST" action="{{ route('wizard.filing.save', ['return' => $return, 'step' => $step]) }}">
                    @csrf

                <!-- STEP 1: DISCLAIMER -->
                @if($step === 1)
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            📜
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800" x-show="wizardLang === 'en'">Accept Disclaimer & Get Started</h2>
                        <h2 class="text-lg font-semibold text-slate-800" x-show="wizardLang === 'ur'">Iqrar-nama aur Sharaait (Disclaimer)</h2>

                        <div class="p-4 rounded-xl bg-slate-50 text-xs text-slate-600 leading-relaxed space-y-2 border border-slate-200">
                            <p>1. TaxFiler Portal aapke darj kardah data ke mutabiq FBR IRIS par return tayyar karega.</p>
                            <p>2. Aapka CNIC aur confidential data 256-bit bank-level encryption se mehfooz hai.</p>
                            <p>3. Return submit hone ke baad FBR se milne wali official 114(1) CPR receipt aapke portal par dastiyab hogi.</p>
                        </div>

                        <label class="flex items-center space-x-3 pt-2 cursor-pointer select-none">
                            <input type="checkbox" required checked class="w-4 h-4 text-blue-600 accent-blue-600 rounded border-slate-300">
                            <span class="text-xs font-medium text-slate-800">Main tamam sharaait se mutafiq hoon (I accept the terms).</span>
                        </label>
                    </div>

                <!-- STEP 2: SELECT FILER TYPE -->
                @elseif($step === 2)
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            🔀
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800" x-show="wizardLang === 'en'">Select Filer Category</h2>
                        <h2 class="text-lg font-semibold text-slate-800" x-show="wizardLang === 'ur'">Aap kis category mein aate hain?</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <label class="border-2 border-blue-600 bg-blue-50/40 rounded-xl p-4 cursor-pointer hover:shadow-sm transition block">
                                <input type="radio" name="filer_type" value="fresh_filer" checked class="hidden">
                                <span class="text-2xl block mb-2">🟢</span>
                                <h3 class="text-sm font-semibold text-slate-900">Fresh Filer (Pehli Dafa)</h3>
                                <p class="text-xs text-slate-500 mt-1">Pehli dafa FBR return file kar rahe hain (Zero opening wealth).</p>
                            </label>

                            <label class="border border-slate-200 hover:border-blue-500 rounded-xl p-4 cursor-pointer hover:shadow-sm transition block">
                                <input type="radio" name="filer_type" value="already_filer" class="hidden">
                                <span class="text-2xl block mb-2">🔵</span>
                                <h3 class="text-sm font-semibold text-slate-900">Already Filer (Pehle se)</h3>
                                <p class="text-xs text-slate-500 mt-1">Pichle saal file kiya tha (Express 3-minute form).</p>
                            </label>
                        </div>
                    </div>

                <!-- STEP 3: PERSONAL INFORMATION -->
                @elseif($step === 3)
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            👤
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Shanakhti Maloomat (Personal Info)</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 uppercase mb-1">Full Name (As per CNIC) *</label>
                                <input type="text" name="name" value="{{ $return->client->name }}" required class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 uppercase mb-1">Active WhatsApp Mobile *</label>
                                <input type="tel" name="phone" inputmode="numeric" value="{{ $return->client->phone }}" required class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 uppercase mb-1">Residential Address</label>
                                <input type="text" name="address" value="{{ $return->client->address }}" placeholder="House #, Street, City" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" />
                            </div>
                        </div>
                    </div>

                <!-- STEP 4: TAX YEAR SELECTION -->
                @elseif($step === 4)
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            📅
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Tax Year Selection</h2>
                        <div class="p-4 rounded-xl border-2 border-blue-600 bg-blue-50/50 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">{{ $return->taxYear->name }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">1 July 2023 se 30 June 2024 tak ka maliyati saal (Finance Act 2024)</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-600 text-white">Active</span>
                        </div>
                    </div>

                <!-- STEP 5: INCOME DETAILS (SALARY WITH LIVE TAX ESTIMATOR) -->
                @elseif($step === 5)
                    @php $salaryItem = $return->incomeItems->where('source_type', 'salary')->first(); @endphp
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            💼
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-800">Tankhwah / Salary Income (Code 7001)</h2>
                                <p class="text-xs text-slate-500">Apni monthly ya annual tankhwah darj karein</p>
                            </div>
                            <!-- Monthly vs Annual Toggle -->
                            <div class="flex items-center bg-slate-100 p-0.5 rounded-lg text-xs font-medium border border-slate-200">
                                <button
                                    type="button"
                                    @click="salaryFreq = 'monthly'"
                                    :class="salaryFreq === 'monthly' ? 'bg-white text-blue-700 shadow-xs font-semibold' : 'text-slate-600'"
                                    class="px-2.5 py-1 rounded-md transition cursor-pointer"
                                >
                                    Monthly
                                </button>
                                <button
                                    type="button"
                                    @click="salaryFreq = 'annual'"
                                    :class="salaryFreq === 'annual' ? 'bg-white text-blue-700 shadow-xs font-semibold' : 'text-slate-600'"
                                    class="px-2.5 py-1 rounded-md transition cursor-pointer"
                                >
                                    Annual
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="salary_frequency" :value="salaryFreq">

                        <div class="space-y-3 pt-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Company / Employer Name</label>
                                <input type="text" name="employer" value="{{ $salaryItem?->employer_or_institution ?? '' }}" placeholder="e.g. Systems Ltd / Bank Alfalah" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" />
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">
                                    <span x-text="salaryFreq === 'monthly' ? 'Monthly Gross Salary (PKR) *' : 'Annual Gross Salary (PKR) *'"></span>
                                </label>
                                <input
                                    type="number"
                                    inputmode="numeric"
                                    name="gross_salary"
                                    x-model="inputSalary"
                                    placeholder="150000"
                                    class="w-full px-3.5 py-2.5 text-sm font-semibold font-mono rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                                />
                            </div>

                            <!-- Live FBR Tax Estimator Box -->
                            <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200/60 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-500">Salana Total Income:</span>
                                    <span class="font-bold font-mono text-slate-900 ml-1 tabular-nums">
                                        PKR <span x-text="((salaryFreq === 'monthly') ? (inputSalary * 12) : Number(inputSalary)).toLocaleString()"></span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-500">Estimated FBR Tax:</span>
                                    <span class="font-bold font-mono text-blue-700 ml-1 tabular-nums">
                                        PKR <span x-text="computeLiveTax().toLocaleString()"></span>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Tax Deducted by Employer (Salary Slip par)</label>
                                <input
                                    type="number"
                                    inputmode="numeric"
                                    name="salary_tax_deducted"
                                    x-model="taxDeducted"
                                    placeholder="0"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none font-mono"
                                />
                                <span class="text-[11px] text-slate-500 mt-0.5 block">Agar employer ne tax kaata hai to yahan likhein taake refund ya adjustment ho sake.</span>
                        </div>
                    </div>

                <!-- STEP 6: TAX CREDITS (SIMPLIFIED YES/NO TOGGLE) -->
                @elseif($step === 6)
                    @php $charityItem = $return->taxDeductions->where('deduction_type', 'charity_sec61')->first(); @endphp
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            🤝
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Tax Credits (Charity Donations & VPS)</h2>
                        <p class="text-xs text-slate-500">Kia aapne is saal kisi approved charity (Shaukat Khanum/Edhi) ko donation di ya VPS Pension Fund mein invest kiya?</p>

                        <!-- Quick Toggle -->
                        <div class="grid grid-cols-2 gap-2 text-xs font-medium pt-2">
                            <button
                                type="button"
                                @click="hasCharity = false"
                                :class="!hasCharity ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-100 text-slate-600'"
                                class="p-2.5 rounded-lg border border-slate-200 transition cursor-pointer"
                            >
                                ✕ No, None (Skip to Next)
                            </button>
                            <button
                                type="button"
                                @click="hasCharity = true"
                                :class="hasCharity ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-100 text-slate-600'"
                                class="p-2.5 rounded-lg border border-slate-200 transition cursor-pointer"
                            >
                                ✓ Yes, I Made Donations
                            </button>
                        </div>

                        <div x-show="hasCharity" x-transition class="space-y-3 pt-3">
                            <x-ui.input label="Charity / NGO Name" name="charity_ngo" value="{{ $charityItem?->institution_or_fund ?? '' }}" placeholder="e.g. Shaukat Khanum Memorial" />
                            <x-ui.input label="Donation Amount (PKR)" name="charity_amount" type="number" inputmode="numeric" value="{{ $charityItem?->eligible_amount ?? '' }}" placeholder="50000" />
                        </div>
                    </div>

                <!-- STEP 7: TAX DEDUCTIONS (ZAKAT YES/NO) -->
                @elseif($step === 7)
                    @php $zakatItem = $return->taxDeductions->where('deduction_type', 'zakat')->first(); @endphp
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            🎁
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Tax Deductions (Zakat Sec 60)</h2>
                        <p class="text-xs text-slate-500">Kia 1st Ramadan ko aapke bank account se Zakat deduction hui thi?</p>

                        <!-- Quick Toggle -->
                        <div class="grid grid-cols-2 gap-2 text-xs font-medium pt-2">
                            <button
                                type="button"
                                @click="hasZakat = false"
                                :class="!hasZakat ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-100 text-slate-600'"
                                class="p-2.5 rounded-lg border border-slate-200 transition cursor-pointer"
                            >
                                ✕ No Bank Zakat (Skip)
                            </button>
                            <button
                                type="button"
                                @click="hasZakat = true"
                                :class="hasZakat ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-100 text-slate-600'"
                                class="p-2.5 rounded-lg border border-slate-200 transition cursor-pointer"
                            >
                                ✓ Yes, Bank Deducted Zakat
                            </button>
                        </div>

                        <div x-show="hasZakat" x-transition class="pt-3">
                            <x-ui.input label="Bank Deducted Zakat Amount (PKR)" name="zakat_amount" type="number" inputmode="numeric" value="{{ $zakatItem?->eligible_amount ?? '' }}" placeholder="15000" helper="Yeh raqam taxable income se minus hoti hai" />
                        </div>
                    </div>

                <!-- STEP 8: ASSET DETAILS (PLAIN HUMAN QUESTIONS) -->
                @elseif($step === 8)
                    @php
                        $bankAsset = $return->wealthAssets->where('asset_type', 'bank_account')->first();
                        $vehicleAsset = $return->wealthAssets->where('asset_type', 'vehicle')->first();
                        $propAsset = $return->wealthAssets->where('asset_type', 'immovable_property')->first();
                    @endphp
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            💎
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Zati Asasay / Personal Assets (Wealth Statement)</h2>
                        <p class="text-xs text-slate-500">Normal insaan ke asan alfaz mein 3 basic sawal:</p>

                        <div class="space-y-4 pt-1">
                            <!-- Question 1: Bank Balance -->
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                <label class="block text-xs font-semibold text-slate-800 mb-1">1. Bank Accounts Closing Balance (30 June ko) *</label>
                                <p class="text-[11px] text-slate-500 mb-2">Tamam bank accounts ka 30 June tak ka total balance</p>
                                <input
                                    type="number"
                                    inputmode="numeric"
                                    name="bank_balance"
                                    value="{{ $bankAsset?->cost_value ?? '' }}"
                                    placeholder="450000"
                                    class="w-full px-3.5 py-2 text-xs font-mono rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                                />
                            </div>

                            <!-- Question 2: Vehicle -->
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-slate-800">2. Koi Gari / Motorcycle aapke naam par hai?</span>
                                    <button type="button" @click="hasVehicle = !hasVehicle" class="text-xs text-blue-600 font-semibold hover:underline cursor-pointer">
                                        <span x-text="hasVehicle ? '✕ Remove' : '+ Add Vehicle'"></span>
                                    </button>
                                </div>
                                <div x-show="hasVehicle" x-transition class="space-y-2 pt-1">
                                    <input type="text" name="vehicle_description" value="{{ $vehicleAsset?->title ?? '' }}" placeholder="e.g. Honda City 2021 (Reg # LEA-1234)" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300" />
                                    <input type="number" inputmode="numeric" name="vehicle_value" value="{{ $vehicleAsset?->cost_value ?? '' }}" placeholder="Purchase Price / Cost (e.g. 2800000)" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 font-mono" />
                                </div>
                            </div>

                            <!-- Question 3: House / Plot -->
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-slate-800">3. Koi Makan, Plot ya Flat aapke naam par hai?</span>
                                    <button type="button" @click="hasProperty = !hasProperty" class="text-xs text-blue-600 font-semibold hover:underline cursor-pointer">
                                        <span x-text="hasProperty ? '✕ Remove' : '+ Add Property'"></span>
                                    </button>
                                </div>
                                <div x-show="hasProperty" x-transition class="space-y-2 pt-1">
                                    <input type="text" name="property_description" value="{{ $propAsset?->title ?? '' }}" placeholder="e.g. 5 Marla House, Bahria Town Lahore" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300" />
                                    <input type="number" inputmode="numeric" name="property_value" value="{{ $propAsset?->cost_value ?? '' }}" placeholder="Purchase Cost (e.g. 7500000)" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 font-mono" />
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- STEP 9: PERSONAL EXPENSES (QUICK 1-CLICK PILLS) -->
                @elseif($step === 9)
                    <div class="space-y-4" x-data="{ expenseVal: '{{ $return->household_expenses ?: 960000 }}' }">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            🛒
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Personal & Household Expenses (Code 7087)</h2>
                        <p class="text-xs text-slate-500">Pure saal ke gharelu ikhrajat (bijli, gas, rashan, bachon ki fees). 1-Click se select karein:</p>

                        <!-- Quick Selection Pills -->
                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <button
                                type="button"
                                @click="expenseVal = 600000"
                                :class="expenseVal == 600000 ? 'border-blue-600 bg-blue-50 text-blue-900 font-semibold' : 'border-slate-200 bg-slate-50 text-slate-600'"
                                class="border rounded-lg p-3 text-center transition cursor-pointer text-xs"
                            >
                                <span class="block text-[11px] text-slate-400">~50k / Month</span>
                                <span class="tabular-nums">PKR 600,000 / Yr</span>
                            </button>

                            <button
                                type="button"
                                @click="expenseVal = 960000"
                                :class="expenseVal == 960000 ? 'border-blue-600 bg-blue-50 text-blue-900 font-semibold' : 'border-slate-200 bg-slate-50 text-slate-600'"
                                class="border rounded-lg p-3 text-center transition cursor-pointer text-xs"
                            >
                                <span class="block text-[11px] text-slate-400">~80k / Month</span>
                                <span class="tabular-nums">PKR 960,000 / Yr</span>
                            </button>

                            <button
                                type="button"
                                @click="expenseVal = 1440000"
                                :class="expenseVal == 1440000 ? 'border-blue-600 bg-blue-50 text-blue-900 font-semibold' : 'border-slate-200 bg-slate-50 text-slate-600'"
                                class="border rounded-lg p-3 text-center transition cursor-pointer text-xs"
                            >
                                <span class="block text-[11px] text-slate-400">~120k / Month</span>
                                <span class="tabular-nums">PKR 1,440,000 / Yr</span>
                            </button>
                        </div>

                        <div class="pt-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Custom Annual Expenses (PKR)</label>
                            <input
                                type="number"
                                inputmode="numeric"
                                name="household_expenses"
                                x-model="expenseVal"
                                class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none tabular-nums"
                            />
                        </div>
                    </div>

                <!-- STEP 10: FBR FILING AUTHORIZATION & CONSENT -->
                @elseif($step === 10)
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            ✍️
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Filing Authorization & Consent</h2>
                        <p class="text-xs text-slate-500">Hamare certified tax consultant ko apni taraf se FBR IRIS par return file karne ki ijazat dein.</p>

                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                            <label class="flex items-start space-x-3 cursor-pointer text-xs select-none">
                                <input type="checkbox" name="consent_consultant_files" value="1" required checked class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-700 leading-relaxed">
                                    Main tasdeeq karta hoon ke di gayi aamdani aur asason ki maloomat durust hain aur main consultant ko FBR IRIS portal par yeh return file karne ki ijazat deta hoon.
                                </span>
                            </label>
                            <p class="text-[11px] text-slate-400">
                                Security note: Hamara platform aapka FBR password ya PIN save nahi karta. Agar verification OTP ki zaroorat hui to consultant aap se WhatsApp par rabta karega.
                            </p>
                        </div>
                    </div>

                <!-- STEP 11: REVIEW & CALCULATION SUMMARY -->
                @elseif($step === 11)
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-4">
                            📊
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Return Calculation Summary</h2>

                        <div class="bg-slate-50 p-5 rounded-xl space-y-2.5 text-xs border border-slate-200 font-mono">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Total Income:</span>
                                <span class="font-semibold text-slate-900 tabular-nums">PKR {{ number_format($return->total_income, 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Taxable Income:</span>
                                <span class="font-semibold text-slate-900 tabular-nums">PKR {{ number_format($return->taxable_income, 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Computed Tax (Finance Act 2024 Slabs):</span>
                                <span class="font-semibold text-blue-700 tabular-nums">PKR {{ number_format($return->computed_tax, 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Advance Tax Paid (Employer):</span>
                                <span class="font-semibold text-emerald-700 tabular-nums">PKR {{ number_format($return->tax_deducted_advance, 0) }}</span>
                            </div>
                            <div class="flex justify-between pt-2.5 border-t border-slate-200 text-sm">
                                <span class="font-bold text-slate-900">Net Tax Due / Refund:</span>
                                <span class="font-bold tabular-nums {{ $return->isPayable() ? 'text-red-600' : 'text-emerald-600' }}">
                                    PKR {{ number_format(abs($return->net_tax_payable_or_refundable), 0) }}
                                    <span class="text-[10px] uppercase font-semibold">{{ $return->isPayable() ? '(Payable)' : '(Refundable)' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                <!-- STEP 12: PAYMENT (PAKISTANI REALITY: RAAST / JAZZCASH / BANK) -->
                @elseif($step === 12)
                    <div class="space-y-4 text-center">
                        <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-2">
                            💳
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Payment & Final Submission</h2>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Apni filing fee pay karein. Fee confirmation ke baad consultant 114(1) FBR receipt file karega.
                        </p>

                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-200 max-w-sm mx-auto my-3">
                            <span class="text-xs text-slate-500 block">Total Salaried Filing Fee</span>
                            <span class="text-3xl font-bold font-mono text-blue-800 tabular-nums">PKR 3,000</span>
                        </div>

                        <!-- Real Pakistani Payment Details -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-left max-w-md mx-auto space-y-3">
                            <div>
                                <span class="font-semibold text-slate-800 block text-xs">🏦 Option 1: Raast / Direct Bank Transfer</span>
                                <div class="font-mono text-[11px] text-slate-600 mt-1 space-y-0.5">
                                    <div>Bank: <strong>Meezan Bank Limited</strong></div>
                                    <div>Title: <strong>Apex Tax Advisory</strong></div>
                                    <div>IBAN: <code class="bg-white px-1 border border-slate-200 rounded">PK45MEZN0001234567890101</code></div>
                                    <div>Raast ID: <code class="bg-white px-1 border border-slate-200 rounded font-bold">03001234567</code></div>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200">
                                <span class="font-semibold text-slate-800 block text-xs">📱 Option 2: JazzCash / EasyPaisa</span>
                                <div class="font-mono text-[11px] text-slate-600 mt-1">
                                    <div>Account / Mobile: <strong>0300-1234567</strong></div>
                                    <div>Title: <strong>Apex Tax Advisory</strong></div>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction ID (TID) / Reference No *</label>
                                <input
                                    type="text"
                                    name="transaction_reference"
                                    required
                                    placeholder="e.g. 1234567890 or Bank Ref #"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none bg-white font-mono"
                                />
                            </div>
                    </div>
                @endif

                <!-- Sticky Navigation Bar (Spec 4.3) -->
                <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-slate-200 mt-8 pt-4 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 px-6 sm:px-8 pb-5 rounded-b-xl z-20 flex items-center justify-between">
                    <div>
                        @if($step > 1)
                            <a href="{{ route('wizard.filing', ['return' => $return, 'step' => $step - 1]) }}">
                                <x-ui.button variant="secondary" size="md">
                                    &larr; Back
                                </x-ui.button>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center space-x-3">
                        @if($step < 12)
                            <a href="{{ route('wizard.filing', ['return' => $return, 'step' => $step + 1]) }}" class="text-xs font-medium text-slate-500 hover:text-slate-700 underline mr-1">
                                Baad mein bharunga
                            </a>

                            <x-ui.button variant="primary" type="submit" size="md">
                                Save & Continue &rarr;
                            </x-ui.button>
                        @else
                            <x-ui.button variant="primary" type="submit" size="lg">
                                Confirm Fee (PKR 3,000) & Submit Return 🚀
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Sticky Summary & Support Sidebar (Col 4) -->
        <div class="lg:col-span-4 space-y-5">
            <!-- Return Dossier Summary Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Return Overview</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase font-mono">
                        Step {{ $step }} / 12
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Tax Year:</span>
                        <span class="font-bold text-slate-900">{{ $return->taxYear?->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Taxpayer:</span>
                        <span class="font-medium text-slate-900 truncate max-w-[150px]">{{ $return->client?->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>CNIC:</span>
                        <span class="font-mono text-slate-800">{{ $return->client?->masked_cnic }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Estimated Income Tax:</span>
                        <span class="font-bold font-mono text-blue-700" x-text="'PKR ' + computeLiveTax().toLocaleString()">PKR 0</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Advocate Filing Fee:</span>
                        <span class="font-semibold text-slate-900 font-mono">PKR {{ number_format($return->fee_amount, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Fee Status:</span>
                        <span class="font-bold {{ $return->fee_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $return->fee_status === 'paid' ? '✓ Paid' : '⏳ Pending' }}
                        </span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 leading-relaxed">
                    🔒 <strong>FBR IRIS Compliance:</strong> Aapka data 256-bit encrypted hai aur verified advocate ke review ke baad jama kiya jayega.
                </div>
            </div>

            <!-- Dedicated Advocate Assistance Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-5 text-white border border-slate-800 shadow-2xs space-y-3">
                <div class="flex items-center space-x-2.5">
                    <span class="text-xl">⚖️</span>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">Legal Consultation</h4>
                        <p class="text-[11px] text-slate-400">High Court & FBR Certified</p>
                    </div>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Agar kisi step mein dushwari pesh aa rahi ho to foran apne tax advocate se rabta karein.
                </p>
                <a href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20Return%20Step%20{{ $step }}%20mein%20rehnumai%20chahiye."
                   target="_blank"
                   class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-[#25D366] text-slate-950 hover:bg-[#1ebd59] transition shadow-2xs cursor-pointer">
                    <span>💬 Chat on WhatsApp</span>
                </a>
            </div>

            <!-- Safe Return & Discard Actions -->
            <div class="p-4 bg-white rounded-2xl border border-slate-200 text-xs space-y-2.5 shadow-2xs">
                <a href="{{ route('dashboard') }}" class="block text-center font-semibold text-slate-600 hover:text-slate-900 py-1 transition">
                    &larr; Save &amp; Return to Dashboard
                </a>

                @if($return->status === 'draft')
                    <form method="POST" action="{{ route('client.returns.destroy', $return) }}" onsubmit="return confirm('Kya aap is draft return application ko delete karna chahte hain?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-center text-rose-600 hover:text-rose-700 font-semibold py-1 text-xs transition cursor-pointer">
                            🗑️ Discard &amp; Delete Draft
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
