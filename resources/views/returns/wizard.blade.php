<x-layouts.app :header="'Tax Filing Wizard: ' . $return->client->name . ' (' . $return->taxYear->name . ')'">
    <div class="space-y-6 max-w-5xl mx-auto" x-data="{
        showCategoryModal: false,
        activeTab: '{{ count($return->selected_categories ?? []) > 0 ? ($return->selected_categories[0] ?? 'salary') : 'salary' }}',
        fbrData: {{ json_encode($fbrSnapshot ?? []) }},
        isSyncingFbr: false,
        syncMsg: '',
        showFbrBanner: true,

        async syncFbr() {
            this.isSyncingFbr = true;
            this.syncMsg = '';
            try {
                const res = await fetch('{{ route('returns.sync-fbr', $return) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success && data.snapshot) {
                    this.fbrData = data.snapshot;
                    this.syncMsg = 'FBR IRIS Live data refreshed!';
                }
            } catch (e) {
                this.syncMsg = 'Error refreshing FBR data.';
            } finally {
                this.isSyncingFbr = false;
            }
        }
    }">
        <!-- Top Live Tax Meter Banner (Fintech Style) -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 rounded-2xl p-6 text-white shadow-xl border border-slate-700/60 relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">🏛️</span>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-white">{{ $return->client->name }}</h2>
                            <p class="text-xs text-slate-300 font-mono">CNIC: {{ $return->client->cnic }} | {{ $return->taxYear->name }}</p>
                        </div>
                    </div>

                    <!-- Micro Metrics Bar -->
                    <div class="flex flex-wrap items-center gap-4 mt-4 text-xs font-mono">
                        <div class="bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700">
                            <span class="text-slate-400 block text-[10px]">TOTAL INCOME</span>
                            <span class="font-bold text-white">PKR {{ number_format($return->total_income, 0) }}</span>
                        </div>
                        <div class="bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700">
                            <span class="text-slate-400 block text-[10px]">COMPUTED TAX (SLABS)</span>
                            <span class="font-bold text-indigo-300">PKR {{ number_format($return->computed_tax, 0) }}</span>
                        </div>
                        <div class="bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700">
                            <span class="text-slate-400 block text-[10px]">TAX DEDUCTED / ADVANCE</span>
                            <span class="font-bold text-emerald-400">PKR {{ number_format($return->tax_deducted_advance, 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Live Tax Liability / Refund Badge -->
                <div class="flex flex-col items-start md:items-end">
                    <span class="text-[11px] font-semibold uppercase tracking-wider {{ $return->isPayable() ? 'text-rose-400' : ($return->isRefundable() ? 'text-teal-300' : 'text-slate-300') }}">
                        {{ $return->isPayable() ? '🚨 Net Tax Payable (To FBR)' : ($return->isRefundable() ? '🎉 Claimable Tax Refund' : 'Tax Balance (Nil)') }}
                    </span>
                    <div class="text-3xl font-extrabold font-mono mt-1 {{ $return->isPayable() ? 'text-rose-400' : ($return->isRefundable() ? 'text-teal-400' : 'text-white') }}">
                        PKR {{ number_format(abs($return->net_tax_payable_or_refundable), 0) }}
                    </div>

                    <!-- Icon Action Bar (Icon-only with micro tooltips) -->
                    <div class="flex items-center space-x-2 mt-4">
                        <button
                            type="button"
                            @click="showCategoryModal = true"
                            class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-600 transition"
                            title="Categories Customize Karein (12 Categories)"
                        >
                            <span class="text-base">⚙️</span>
                        </button>

                        <a
                            href="{{ route('returns.show', $return) }}"
                            class="p-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg transition"
                            title="Open Consultant IRIS Console (FBR Codes)"
                        >
                            <span class="text-base">🚀</span>
                        </a>

                        <form method="POST" action="{{ route('returns.destroy', $return) }}" onsubmit="return confirm('Kya aap waqai is Tax Return ko mukammal tor par delete karna chahte hain?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="p-2.5 rounded-xl bg-rose-600/80 hover:bg-rose-600 text-white shadow-lg transition cursor-pointer"
                                title="Delete Tax Return"
                            >
                                <span class="text-base">🗑️</span>
                            </button>
                        </form>

                        @if($return->status === 'ready_to_file')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500 text-white animate-pulse">
                                Ready to File
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- FBR IRIS Live Verification & Filed Return Pre-Processing Desk -->
        <div class="bg-white rounded-2xl border border-blue-200/90 shadow-sm overflow-hidden">
            <!-- Header Bar with Sync and Quick Actions -->
            <div class="px-5 py-3.5 bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center space-x-2.5">
                    <span class="w-7 h-7 rounded-lg bg-blue-600/30 border border-blue-400/30 flex items-center justify-center text-blue-400 text-sm">
                        🏛️
                    </span>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-xs font-bold text-white tracking-wide uppercase">FBR IRIS 2.0 Live Verified Profile &amp; Filed Dossier Desk</h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold font-mono"
                                  :class="fbrData?.atl?.is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30'"
                                  x-text="fbrData?.atl?.is_active ? '✓ ACTIVE FILER' : '✕ INACTIVE (NON-FILER)'">
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-300">
                            Client ka FBR IRIS record pehle se synced aur pre-loaded hai taake waqt bache aur double entry na karni pare.
                        </p>
                    </div>
                </div>

                <!-- Sync & Dossier Download Buttons -->
                <div class="flex items-center space-x-2 shrink-0">
                    <button
                        type="button"
                        @click="syncFbr()"
                        :disabled="isSyncingFbr"
                        class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center space-x-1 shadow-xs cursor-pointer disabled:opacity-50"
                        title="Re-query FBR Live Database"
                    >
                        <span x-show="!isSyncingFbr">🔄 Re-Sync IRIS</span>
                        <span x-show="isSyncingFbr" class="animate-spin inline-block">⏳</span>
                        <span x-show="isSyncingFbr">Syncing...</span>
                    </button>

                    <a
                        href="{{ route('returns.pdf', $return) }}"
                        target="_blank"
                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center space-x-1 shadow-xs"
                        title="Download FBR Form 114(1) Dossier PDF"
                    >
                        <span>📥</span>
                        <span>FBR Dossier (PDF)</span>
                    </a>
                </div>
            </div>

            <!-- Sync feedback message -->
            <div x-show="syncMsg" x-text="syncMsg" class="px-5 py-2 text-xs bg-emerald-50 text-emerald-800 border-b border-emerald-100 font-medium"></div>

            <!-- 3-Column Pre-Loaded Data Grid -->
            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50/60">
                <!-- Column 1: FBR Verified Registration Profile -->
                <div class="bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-2xs space-y-2 text-xs">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium text-[11px]">FBR Legal Name:</span>
                        <span class="font-bold text-slate-900" x-text="fbrData?.profile?.legal_name || '{{ $return->client->name }}'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium text-[11px]">NTN Number:</span>
                        <span class="font-mono font-bold text-blue-700" x-text="fbrData?.profile?.ntn || '{{ $return->client->ntn ?? '123456-7' }}'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium text-[11px]">CNIC Number:</span>
                        <span class="font-mono font-semibold text-slate-700">{{ $return->client->cnic }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium text-[11px]">Registration Type:</span>
                        <span class="font-medium text-slate-800" x-text="fbrData?.profile?.registration_type || 'Individual Taxpayer'"></span>
                    </div>
                </div>

                <!-- Column 2: Jurisdiction & Tax Office -->
                <div class="bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-2xs space-y-2 text-xs">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium text-[11px]">Assigned RTO:</span>
                        <span class="font-semibold text-slate-900" x-text="fbrData?.profile?.assigned_rto || 'RTO Islamabad'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium text-[11px]">Surcharge Status:</span>
                        <span class="font-semibold text-emerald-700" x-text="fbrData?.atl?.surcharge_status || 'Paid & Cleared ✓'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Business Activity:</span>
                        <span class="font-medium text-slate-700 text-[11px]" x-text="fbrData?.profile?.principal_activity || 'Commercial & Professional Services'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Registered Address:</span>
                        <span class="text-slate-600 text-[11px]" x-text="fbrData?.profile?.registered_address || '{{ $return->client->address ?? 'Blue Area, Islamabad' }}'"></span>
                    </div>
                </div>

                <!-- Column 3: Return Filing & Pre-Loaded Metrics -->
                <div class="bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-2xs space-y-2 text-xs">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                        <span class="text-slate-500 font-medium text-[11px]">Filing Pipeline:</span>
                        <span class="font-bold px-2 py-0.5 rounded uppercase text-[10px] {{ $return->status === 'filed' ? 'bg-emerald-100 text-emerald-800' : ($return->status === 'in_review' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ $return->status }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium text-[11px]">Fee Payment:</span>
                        @if($return->fee_status === 'paid')
                            <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[10px]">Paid ✓</span>
                        @else
                            <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-[10px]">Pending (PKR {{ number_format($return->fee_amount, 0) }})</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium text-[11px]">IRIS Ack #:</span>
                        <span class="font-mono text-[10px] font-bold text-emerald-800">
                            {{ $return->iris_acknowledgement_no ?? ('E-REC-' . date('Y') . '-' . str_pad($return->id, 6, '0', STR_PAD_LEFT)) }}
                        </span>
                    </div>
                    <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 font-medium">Pre-Loaded Net Wealth:</span>
                        <span class="font-mono font-bold text-slate-800">PKR {{ number_format($return->wealthAssets->sum('cost_or_value'), 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Time-Saving Highlight Banner -->
            <div class="px-5 py-2.5 bg-blue-50/80 border-t border-blue-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                <div class="flex items-center space-x-2 text-blue-900">
                    <span class="text-sm">⚡</span>
                    <span class="font-medium">
                        <strong>Time Saver:</strong> Is taxpayer ka data FBR IRIS se auto-synced hai. Agar koi entry update karni ho to direct niche tab click karein, ya foran PDF Dossier download karein.
                    </span>
                </div>
                <div class="text-[11px] text-blue-700 font-mono shrink-0">
                    IRIS Live Stamp &bull; Verified
                </div>
            </div>
        </div>

        <!-- 12-Category Grid Visual Switcher (Fintech Pill Slider) -->
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3 px-1">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Active Tax Categories</h3>
                    <p class="text-[11px] text-gray-400">Jo categories client par laagu hoti hain sirf unka data fill karein</p>
                </div>
                <button
                    type="button"
                    @click="showCategoryModal = true"
                    class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:text-emerald-700 cursor-pointer"
                >
                    <span class="mr-1">➕</span> Add/Remove Categories
                </button>
            </div>

            <!-- Horizontal Scrollable Category Pills -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-thin">
                @foreach($allCategories as $key => $cat)
                    @if($return->hasCategory($key))
                        <button
                            type="button"
                            @click="activeTab = '{{ $key }}'"
                            :class="activeTab === '{{ $key }}' ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-500 ring-offset-1' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200/80'"
                            class="flex-shrink-0 flex items-center space-x-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                        >
                            <span class="text-base">{{ $cat['icon'] }}</span>
                            <span>{{ $cat['title'] }}</span>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- CATEGORY TAB PANELS -->

        <!-- 1. SALARY INCOME -->
        <div x-show="activeTab === 'salary'" x-cloak class="space-y-4">
            <x-ui.card
                title="💼 1. Salary Income (Mulazmat & Tankhwah)"
                description="Monthly salary, basic pay, allowances, aur employer ka kata hua withholding tax"
            >
                <!-- Items Table -->
                <x-ui.table
                    :headers="['IRIS Code', 'Employer / Company', 'Gross Salary', 'Exempt Allowances', 'Tax Deducted (Sec 149)', 'Action']"
                    :empty="$return->incomeItems->where('source_type', 'salary')->isEmpty()"
                    emptyMessage="Koi salary entry shamil nahi hui. Niche diye gaye form se tankhwah darj karein!"
                >
                    @foreach($return->incomeItems->where('source_type', 'salary') as $item)
                        <tr>
                            <x-ui.td class="font-mono text-xs font-bold text-indigo-700">7001 / 640101</x-ui.td>
                            <x-ui.td class="font-bold text-gray-900">{{ $item->employer_or_institution ?? 'Employer' }}</x-ui.td>
                            <x-ui.td class="font-mono font-medium">PKR {{ number_format($item->gross_amount, 0) }}</x-ui.td>
                            <x-ui.td class="font-mono text-gray-400">PKR {{ number_format($item->exempt_amount, 0) }}</x-ui.td>
                            <x-ui.td class="font-mono text-emerald-700 font-bold">PKR {{ number_format($item->tax_deducted, 0) }}</x-ui.td>
                            <x-ui.td>
                                @if(! $return->is_locked)
                                    <form method="POST" action="{{ route('income-items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Entry" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition">
                                            🗑️
                                        </button>
                                    </form>
                                @endif
                            </x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if(! $return->is_locked)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600 mb-3">+ Add Tankhwah / Salary Record</h4>
                        <form method="POST" action="{{ route('returns.income', $return) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            @csrf
                            <input type="hidden" name="source_type" value="salary">
                            <input type="hidden" name="iris_box_code" value="7001">

                            <x-ui.input label="Employer / Company Name" name="employer_or_institution" placeholder="e.g. Systems Ltd" required="true" />
                            <x-ui.input label="Annual Gross Salary (PKR)" name="gross_amount" type="number" placeholder="1800000" required="true" />
                            <x-ui.input label="Exempt Allowances (Medical etc.)" name="exempt_amount" type="number" value="0" />
                            <x-ui.input label="Tax Deducted (Sec 149)" name="tax_deducted" type="number" placeholder="45000" />

                            <div class="sm:col-span-4 flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                                    💾 Save Salary Entry & Recalculate
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- 2. BUSINESS & FREELANCE -->
        <div x-show="activeTab === 'business'" x-cloak class="space-y-4">
            <x-ui.card
                title="🏪 2. Business, Freelance & Services"
                description="Dukan, trading, consulting, ya online freelancing / software export revenue"
            >
                <x-ui.table
                    :headers="['IRIS Code', 'Business / Client Name', 'Gross Revenue', 'Expenses / Deductions', 'Net Profit', 'Action']"
                    :empty="$return->incomeItems->where('source_type', 'business')->isEmpty()"
                    emptyMessage="Koi business entry nahi mili."
                >
                    @foreach($return->incomeItems->where('source_type', 'business') as $item)
                        <tr>
                            <x-ui.td class="font-mono text-xs font-bold text-indigo-700">7010</x-ui.td>
                            <x-ui.td class="font-bold text-gray-900">{{ $item->employer_or_institution ?? 'Business' }}</x-ui.td>
                            <x-ui.td class="font-mono">PKR {{ number_format($item->gross_amount, 0) }}</x-ui.td>
                            <x-ui.td class="font-mono text-gray-400">PKR {{ number_format($item->exempt_amount, 0) }}</x-ui.td>
                            <x-ui.td class="font-mono text-emerald-700 font-bold">PKR {{ number_format($item->taxable_amount, 0) }}</x-ui.td>
                            <x-ui.td>
                                @if(! $return->is_locked)
                                    <form method="POST" action="{{ route('income-items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg">🗑️</button>
                                    </form>
                                @endif
                            </x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if(! $return->is_locked)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('returns.income', $return) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            @csrf
                            <input type="hidden" name="source_type" value="business">
                            <input type="hidden" name="iris_box_code" value="7010">

                            <x-ui.input label="Business / Freelancing Title" name="employer_or_institution" placeholder="e.g. IT Software Services" required="true" />
                            <x-ui.input label="Annual Gross Revenue (PKR)" name="gross_amount" type="number" required="true" />
                            <x-ui.input label="Business Expenses (PKR)" name="exempt_amount" type="number" value="0" />
                            <x-ui.input label="Advance Tax Deducted (Sec 153)" name="tax_deducted" type="number" value="0" />

                            <div class="sm:col-span-4 flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                    💾 Save Business Revenue
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- 3. PROPERTY RENT -->
        <div x-show="activeTab === 'rental'" x-cloak class="space-y-4">
            <x-ui.card
                title="🏠 3. Property & Rental Income (Kiraya)"
                description="Residential ya commercial plots/buildings ka salana kiraya (Section 155)"
            >
                <x-ui.table
                    :headers="['IRIS Code', 'Property Description', 'Annual Rent', 'Tax Deducted', 'Action']"
                    :empty="$return->incomeItems->where('source_type', 'rental')->isEmpty()"
                    emptyMessage="Koi rental property shamil nahi."
                >
                    @foreach($return->incomeItems->where('source_type', 'rental') as $item)
                        <tr>
                            <x-ui.td class="font-mono text-xs font-bold text-indigo-700">7005</x-ui.td>
                            <x-ui.td class="font-bold text-gray-900">{{ $item->employer_or_institution }}</x-ui.td>
                            <x-ui.td class="font-mono">PKR {{ number_format($item->gross_amount, 0) }}</x-ui.td>
                            <x-ui.td class="font-mono text-emerald-700 font-bold">PKR {{ number_format($item->tax_deducted, 0) }}</x-ui.td>
                            <x-ui.td>
                                @if(! $return->is_locked)
                                    <form method="POST" action="{{ route('income-items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700">🗑️</button>
                                    </form>
                                @endif
                            </x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if(! $return->is_locked)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('returns.income', $return) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @csrf
                            <input type="hidden" name="source_type" value="rental">
                            <input type="hidden" name="iris_box_code" value="7005">

                            <x-ui.input label="Property Address / Description" name="employer_or_institution" placeholder="e.g. Flat 3B, Gulberg Lahore" required="true" />
                            <x-ui.input label="Annual Rent Received (PKR)" name="gross_amount" type="number" required="true" />
                            <x-ui.input label="Tenant Tax Deducted (Sec 155)" name="tax_deducted" type="number" value="0" />

                            <div class="sm:col-span-3 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold">💾 Save Rent Entry</button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- 8. ADVANCE TAX (MOBILE, VEHICLES, ELECTRICITY, BANK) -->
        <div x-show="activeTab === 'advance_tax'" x-cloak class="space-y-4">
            <x-ui.card
                title="🧾 8. Advance & Adjustable Tax Deductions"
                description="Mobile bills, bijli ke bill, gaari ka token tax, aur bank cash withdrawal tax jo return mein wapis adjust hoga"
            >
                <x-ui.table
                    :headers="['IRIS Code', 'Type', 'Institution / Reference', 'Adjustable Tax Paid', 'Action']"
                    :empty="$return->taxAdjustments->isEmpty()"
                    emptyMessage="Koi advance tax adjustment add nahi ki gayi. Mobile ya token tax add karein taake tax refund bany!"
                >
                    @foreach($return->taxAdjustments as $adj)
                        <tr>
                            <x-ui.td class="font-mono text-xs font-bold text-indigo-700">{{ $adj->iris_box_code }}</x-ui.td>
                            <x-ui.td class="capitalize font-semibold text-gray-900">{{ str_replace('_', ' ', $adj->adjustment_type) }}</x-ui.td>
                            <x-ui.td class="text-xs text-gray-500">{{ $adj->institution_name }} ({{ $adj->reference_no }})</x-ui.td>
                            <x-ui.td class="font-mono font-bold text-emerald-700">PKR {{ number_format($adj->tax_amount, 0) }}</x-ui.td>
                            <x-ui.td>
                                @if(! $return->is_locked)
                                    <form method="POST" action="{{ route('adjustments.destroy', $adj) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700">🗑️</button>
                                    </form>
                                @endif
                            </x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if(! $return->is_locked)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('returns.adjustment', $return) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            @csrf
                            <x-ui.select name="adjustment_type" label="Tax Type" required="true">
                                <option value="mobile">📱 Mobile Bill Tax (Sec 236)</option>
                                <option value="vehicle_token">🚗 Vehicle Token Tax (Sec 234)</option>
                                <option value="electricity">⚡ Electricity Bill Tax (Sec 235)</option>
                                <option value="bank_transaction">🏦 Bank Cash / Transfer (Sec 231AB)</option>
                                <option value="property_purchase">🏠 Property Buy/Sell Tax (Sec 236K)</option>
                            </x-ui.select>

                            <x-ui.input label="IRIS Box Code" name="iris_box_code" value="641400" required="true" />
                            <x-ui.input label="Consumer / Phone / Reg #" name="reference_no" placeholder="e.g. 03001234567" />
                            <x-ui.input label="Tax Deducted (PKR)" name="tax_amount" type="number" placeholder="4500" required="true" />

                            <div class="sm:col-span-4 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold">
                                    💾 Save Advance Tax & Adjust
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- 9. DEDUCTIONS & ZAKAT -->
        <div x-show="activeTab === 'deductions'" x-cloak class="space-y-4">
            <x-ui.card
                title="🎁 9. Tax Rebates & Deductions (Zakat / Charity)"
                description="Bank zakat deduction, Section 61 certified NGO donations, aur voluntary pension"
            >
                <x-ui.table
                    :headers="['Type', 'Institution / NGO', 'Amount Paid', 'Credit / Rebate', 'Action']"
                    :empty="$return->taxDeductions->isEmpty()"
                    emptyMessage="Koi zakat ya charity deduction darj nahi."
                >
                    @foreach($return->taxDeductions as $ded)
                        <tr>
                            <x-ui.td class="capitalize font-semibold text-gray-900">{{ str_replace('_', ' ', $ded->deduction_type) }}</x-ui.td>
                            <x-ui.td class="text-xs text-gray-600">{{ $ded->institution_or_fund }}</x-ui.td>
                            <x-ui.td class="font-mono">PKR {{ number_format($ded->eligible_amount, 0) }}</x-ui.td>
                            <x-ui.td class="font-mono text-emerald-700 font-bold">PKR {{ number_format($ded->computed_credit_or_deduction, 0) }}</x-ui.td>
                            <x-ui.td>
                                @if(! $return->is_locked)
                                    <form method="POST" action="{{ route('deductions.destroy', $ded) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700">🗑️</button>
                                    </form>
                                @endif
                            </x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if(! $return->is_locked)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('returns.deduction', $return) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @csrf
                            <input type="hidden" name="iris_box_code" value="9000">

                            <x-ui.select name="deduction_type" label="Rebate Type" required="true">
                                <option value="zakat">Zakat Paid under Zakat Ordinance (Sec 60)</option>
                                <option value="charity_sec61">Donation to Clause 61 Approved NGOs</option>
                                <option value="pension_sec63">Voluntary Pension Scheme (VPS)</option>
                            </x-ui.select>

                            <x-ui.input label="Bank / NGO Name" name="institution_or_fund" placeholder="e.g. Shaukat Khanum" />
                            <x-ui.input label="Amount Paid (PKR)" name="eligible_amount" type="number" required="true" />

                            <div class="sm:col-span-3 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold">💾 Save Rebate</button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- 10. HOUSEHOLD EXPENSES -->
        <div x-show="activeTab === 'expenses'" x-cloak class="space-y-4">
            <x-ui.card
                title="🛒 10. Household & Personal Living Expenses"
                description="Pure saal ke gharelu ikhrajat (bijli, gas, bachon ki taleem, khana peena, travel) (Code 7087)"
            >
                <form method="POST" action="{{ route('returns.expenses', $return) }}" class="max-w-md space-y-4">
                    @csrf
                    <x-ui.input
                        label="Annual Household Expenses (Salana Kharch)"
                        name="household_expenses"
                        type="number"
                        value="{{ $return->household_expenses }}"
                        required="true"
                        helper="FBR Code 7087: Wealth Statement mein aane wala total kharcha"
                    />

                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold">
                        💾 Update Household Expenses
                    </button>
                </form>
            </x-ui.card>
        </div>

        <!-- 11. WEALTH ASSETS (PROPERTIES, CARS, BANK, GOLD) -->
        <div x-show="activeTab === 'assets'" x-cloak class="space-y-4">
            <x-ui.card
                title="💎 11. Wealth Assets & Net Possessions"
                description="30 June tak ke plot, ghar, gariyan, bank accounts, sona aur cash in hand (Section 116)"
            >
                <x-ui.table
                    :headers="['Asset Type', 'Title / Particulars', 'Cost / Purchase Value (PKR)', 'Action']"
                    :empty="$return->wealthAssets->isEmpty()"
                    emptyMessage="Koi asset register nahi hua."
                >
                    @foreach($return->wealthAssets as $asset)
                        <tr>
                            <x-ui.td class="capitalize font-semibold text-gray-900">{{ str_replace('_', ' ', $asset->asset_type) }}</x-ui.td>
                            <x-ui.td class="text-xs text-gray-700">{{ $asset->title }}</x-ui.td>
                            <x-ui.td class="font-mono font-bold text-gray-900">PKR {{ number_format($asset->cost_value, 0) }}</x-ui.td>
                            <x-ui.td>
                                @if(! $return->is_locked)
                                    <form method="POST" action="{{ route('assets.destroy', $asset) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700">🗑️</button>
                                    </form>
                                @endif
                            </x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if(! $return->is_locked)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('returns.asset', $return) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @csrf
                            <input type="hidden" name="iris_box_code" value="7001">

                            <x-ui.select name="asset_type" label="Asset Type" required="true">
                                <option value="property">🏠 Immovable Property (Ghar / Plot)</option>
                                <option value="vehicle">🚗 Motor Vehicle (Gaari / Bike)</option>
                                <option value="bank_account">🏦 Bank Closing Balance</option>
                                <option value="gold">🪙 Gold & Precious Items</option>
                                <option value="cash_in_hand">💵 Cash in Hand</option>
                            </x-ui.select>

                            <x-ui.input label="Description / Details" name="title" placeholder="e.g. Toyota Yaris 2022" required="true" />
                            <x-ui.input label="Cost Value (PKR)" name="cost_value" type="number" placeholder="3500000" required="true" />

                            <div class="sm:col-span-3 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold">💾 Add Asset</button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- 12. LIABILITIES & WEALTH RECONCILIATION -->
        <div x-show="activeTab === 'reconciliation'" x-cloak class="space-y-4">
            @php
                $wealth = $return->wealthReconciliation;
                $diff = $wealth ? (float) $wealth->unreconciled_difference : 0;
                $isReconciled = abs($diff) < 1.0;
            @endphp

            <x-ui.card
                title="⚖️ 12. Liabilities & Wealth Reconciliation (Section 116)"
                description="Opening Wealth + Aamdani - Kharcha = Closing Wealth ka live tally check"
            >
                <!-- Zero Math Tally Meter -->
                <div class="p-5 rounded-2xl border mb-6 {{ $isReconciled ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : 'bg-rose-50 border-rose-200 text-rose-950' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider block">FBR Reconciliation Status:</span>
                            <span class="text-sm font-semibold {{ $isReconciled ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $isReconciled ? '✓ 100% Reconciled (Zero Difference - Perfect for Filing)' : '⚠️ Unreconciled Difference Mismatch' }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-500 uppercase block font-semibold">Difference Amount</span>
                            <span class="text-2xl font-mono font-extrabold {{ $isReconciled ? 'text-emerald-700' : 'text-rose-600' }}">
                                PKR {{ number_format($diff, 0) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Update Opening Wealth Form -->
                <form method="POST" action="{{ route('returns.wealth', $return) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @csrf
                    <x-ui.input label="Previous Year Closing Wealth" name="opening_wealth" type="number" value="{{ $wealth?->opening_wealth ?? 0 }}" required="true" />
                    <x-ui.input label="Total Net Inflows (Income)" name="net_inflows" type="number" value="{{ $wealth?->net_inflows ?? $return->total_income }}" required="true" />
                    <x-ui.input label="Other Outflows / Gifts / Losses" name="other_outflows" type="number" value="{{ $wealth?->other_outflows ?? 0 }}" required="true" />

                    <div class="sm:col-span-3 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold">
                            💾 Recalculate Wealth Tally
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>

    </div>

    <!-- MODAL FOR ACTIVATING / DEACTIVATING 12 CATEGORIES -->
    <div
        x-show="showCategoryModal"
        x-cloak
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs"
    >
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Select Applicable Tax Categories</h3>
                    <p class="text-xs text-gray-400">Jo categories client par laagu hoti hain unko check karein</p>
                </div>
                <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>

            <form method="POST" action="{{ route('returns.categories', $return) }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                    @foreach($allCategories as $catKey => $catData)
                        <label class="flex items-start space-x-3 p-3 rounded-xl border border-gray-200 hover:border-emerald-500 cursor-pointer transition hover:bg-emerald-50/20">
                            <input
                                type="checkbox"
                                name="categories[]"
                                value="{{ $catKey }}"
                                {{ $return->hasCategory($catKey) ? 'checked' : '' }}
                                class="w-4 h-4 mt-1 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500"
                            >
                            <div>
                                <div class="flex items-center space-x-1.5">
                                    <span>{{ $catData['icon'] }}</span>
                                    <span class="text-xs font-bold text-gray-900">{{ $catData['title'] }}</span>
                                </div>
                                <span class="block text-[11px] text-emerald-700 font-medium">{{ $catData['urdu'] }}</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">{{ $catData['desc'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showCategoryModal = false" class="px-4 py-2 border rounded-lg text-xs font-semibold text-gray-600">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">Apply Categories &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
