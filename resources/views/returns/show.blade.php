<x-layouts.app :header="'Return Dossier: ' . $return->client->name . ' (' . $return->taxYear->name . ')'">
    <div class="space-y-6">
        <!-- Top Status & Dossier Header -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-slate-900 text-white font-bold flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-3">
                        <h2 class="text-xl font-bold text-gray-900">{{ $return->client->name }}</h2>
                        <x-ui.badge :status="$return->status" />
                        @if($return->is_locked)
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                Locked (Filed)
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 mt-1 text-xs text-gray-500 font-mono">
                        <span>CNIC: <strong>{{ $return->client->cnic }}</strong></span>
                        <span>Tax Year: <strong>{{ $return->taxYear->name }}</strong></span>
                        <span>Type: <strong class="uppercase">{{ $return->filing_type }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Actions & Pipeline Status Changer -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('returns.wizard', $return) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 rounded-lg transition" title="12-Category Interactive Wizard">
                    <span class="mr-1">🧮</span> Filing Wizard
                </a>

                @if(! $return->is_locked)
                    @if($return->status === 'draft')
                        <form method="POST" action="{{ route('returns.status', $return) }}">
                            @csrf
                            <input type="hidden" name="status" value="in_review">
                            <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm transition">
                                🔍 Send to Review
                            </button>
                        </form>
                    @elseif($return->status === 'in_review')
                        <form method="POST" action="{{ route('returns.status', $return) }}">
                            @csrf
                            <input type="hidden" name="status" value="ready_to_file">
                            <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                                ✓ Mark Ready to File
                            </button>
                        </form>
                    @elseif($return->status === 'ready_to_file')
                        <button
                            type="button"
                            @click="$dispatch('open-modal', 'file-modal')"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition"
                        >
                            🚀 Mark Filed on IRIS
                        </button>
                    @endif
                @else
                    <div class="text-right">
                        <span class="block text-xs font-semibold text-emerald-700">FBR IRIS Filed Slip</span>
                        <span class="text-xs font-mono bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-emerald-800">
                            {{ $return->iris_acknowledgement_no }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Automated Tax Calculation Slabs Summary Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Income</span>
                <p class="text-lg font-bold text-gray-900 mt-1 font-mono">PKR {{ number_format($return->total_income, 0) }}</p>
                <span class="text-[10px] text-gray-400">All Sources Gross</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Taxable Income</span>
                <p class="text-lg font-bold text-gray-900 mt-1 font-mono">PKR {{ number_format($return->taxable_income, 0) }}</p>
                <span class="text-[10px] text-gray-400">Gross minus exemptions</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Computed Tax</span>
                <p class="text-lg font-bold text-indigo-700 mt-1 font-mono">PKR {{ number_format($return->computed_tax, 0) }}</p>
                <span class="text-[10px] text-indigo-500">Auto Slabs Calculation</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Advance / Withholding</span>
                <p class="text-lg font-bold text-gray-900 mt-1 font-mono">PKR {{ number_format($return->tax_deducted_advance, 0) }}</p>
                <span class="text-[10px] text-gray-400">Salary + Banks + Utilities</span>
            </div>

            <div class="bg-white p-4 rounded-xl border {{ $return->isPayable() ? 'border-rose-200 bg-rose-50/30' : ($return->isRefundable() ? 'border-teal-200 bg-teal-50/30' : 'border-gray-100') }} shadow-sm">
                <span class="text-[11px] font-semibold uppercase tracking-wider {{ $return->isPayable() ? 'text-rose-600' : ($return->isRefundable() ? 'text-teal-600' : 'text-gray-400') }}">
                    {{ $return->isPayable() ? 'Net Tax Payable' : ($return->isRefundable() ? 'Net Tax Refund' : 'Tax Balance') }}
                </span>
                <p class="text-lg font-bold mt-1 font-mono {{ $return->isPayable() ? 'text-rose-700' : ($return->isRefundable() ? 'text-teal-700' : 'text-gray-800') }}">
                    PKR {{ number_format(abs($return->net_tax_payable_or_refundable), 0) }}
                </p>
                <span class="text-[10px] {{ $return->isPayable() ? 'text-rose-500' : 'text-teal-500' }}">
                    {{ $return->isPayable() ? 'PSID / CPR Required' : 'Claimable in FBR' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Income Items & IRIS Codes -->
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card
                    title="Income Items & FBR Box Code Mapping Sheet"
                    description="Consultant ke IRIS par copy-paste ke liye exact box codes aur amounts"
                >
                    <x-ui.table
                        :headers="['IRIS Code', 'Source / Employer', 'Gross Income', 'Exempt', 'Tax Deducted', 'Action']"
                        :empty="$return->incomeItems->isEmpty()"
                        emptyMessage="Koi income item shamil nahi kiya gaya. Niche diye gaye form se pehli salary entry add karein!"
                    >
                        @foreach($return->incomeItems as $item)
                            <tr>
                                <x-ui.td class="font-mono text-xs font-bold text-indigo-700">
                                    {{ $item->iris_box_code ?? '7001' }}
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $item->source_type) }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->employer_or_institution ?? $item->description }}</div>
                                </x-ui.td>
                                <x-ui.td class="font-mono text-xs">
                                    PKR {{ number_format($item->gross_amount, 0) }}
                                </x-ui.td>
                                <x-ui.td class="font-mono text-xs text-gray-400">
                                    PKR {{ number_format($item->exempt_amount, 0) }}
                                </x-ui.td>
                                <x-ui.td class="font-mono text-xs text-emerald-700 font-semibold">
                                    PKR {{ number_format($item->tax_deducted, 0) }}
                                </x-ui.td>
                                <x-ui.td>
                                    @if(! $return->is_locked)
                                        <form method="POST" action="{{ route('income-items.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Entry delete karni hai?')" class="text-xs text-rose-500 hover:text-rose-700 font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">Locked</span>
                                    @endif
                                </x-ui.td>
                            </tr>
                        @endforeach
                    </x-ui.table>

                    @if(! $return->is_locked)
                        <!-- Add Income Item Quick Form -->
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3">+ Add New Income / Salary Entry</h4>
                            <form method="POST" action="{{ route('returns.income', $return) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @csrf
                                <x-ui.select name="source_type" label="Income Source" required="true">
                                    <option value="salary" selected>Salary (Code 7001 - Sec 149)</option>
                                    <option value="bank_profit">Bank Profit (Code 7013 - Sec 151)</option>
                                    <option value="property_rental">Property Rent (Code 7005 - Sec 155)</option>
                                    <option value="business">Business / Services (Code 7010)</option>
                                    <option value="other">Other Income</option>
                                </x-ui.select>

                                <x-ui.input
                                    label="IRIS Box Code"
                                    name="iris_box_code"
                                    value="7001"
                                    placeholder="e.g. 7001"
                                    required="true"
                                />

                                <x-ui.input
                                    label="Employer / Bank Name"
                                    name="employer_or_institution"
                                    placeholder="e.g. Systems Ltd"
                                />

                                <x-ui.input
                                    label="Annual Gross Amount (PKR)"
                                    name="gross_amount"
                                    type="number"
                                    placeholder="1800000"
                                    required="true"
                                />

                                <x-ui.input
                                    label="Exempt Amount (PKR)"
                                    name="exempt_amount"
                                    type="number"
                                    value="0"
                                />

                                <x-ui.input
                                    label="Tax Deducted / Advance (PKR)"
                                    name="tax_deducted"
                                    type="number"
                                    placeholder="75000"
                                />

                                <div class="sm:col-span-3 flex justify-end pt-2">
                                    <x-ui.button type="submit" variant="primary" size="sm">
                                        Save Entry & Recalculate Tax &rarr;
                                    </x-ui.button>
                                </div>
                            </form>
                        </div>
                    @endif
                </x-ui.card>
            </div>

            <!-- Right Col: Wealth Reconciliation & Audit Trail -->
            <div class="space-y-6">
                <!-- Wealth Statement (Sec 116) -->
                @php
                    $wealth = $return->wealthReconciliation;
                    $diff = $wealth ? (float) $wealth->unreconciled_difference : 0;
                    $isReconciled = abs($diff) < 1.0;
                @endphp
                <x-ui.card
                    title="Wealth Statement Reconciliation"
                    description="Section 116 Formula: Difference must be 0 for valid filing"
                >
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">Opening Wealth:</span>
                            <span class="font-mono font-semibold">PKR {{ number_format($wealth?->opening_wealth ?? 0, 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">(+) Net Inflows (Income):</span>
                            <span class="font-mono font-semibold text-emerald-700">PKR {{ number_format($wealth?->net_inflows ?? 0, 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">(-) Living Expenses:</span>
                            <span class="font-mono font-semibold text-rose-700">PKR {{ number_format($wealth?->living_expenses ?? 0, 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">(-) Other Outflows / Assets:</span>
                            <span class="font-mono font-semibold text-rose-700">PKR {{ number_format($wealth?->other_outflows ?? 0, 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">(=) Closing Wealth:</span>
                            <span class="font-mono font-semibold">PKR {{ number_format($wealth?->closing_wealth ?? 0, 0) }}</span>
                        </div>

                        <!-- Unreconciled Status -->
                        <div class="p-3 rounded-xl border mt-2 {{ $isReconciled ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-bold">Unreconciled Difference:</span>
                                <span class="font-mono font-extrabold text-sm">
                                    PKR {{ number_format($diff, 0) }}
                                </span>
                            </div>
                            <p class="text-[10px] mt-1 {{ $isReconciled ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $isReconciled ? '✓ Wealth Fully Reconciled (FBR Compliant)' : '⚠ Difference must be zero before filing' }}
                            </p>
                        </div>
                    </div>

                    @if(! $return->is_locked)
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <button
                                type="button"
                                @click="$dispatch('open-modal', 'wealth-modal')"
                                class="w-full text-center py-2 text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition"
                            >
                                Edit Wealth Statement Figures &rarr;
                            </button>
                        </div>
                    @endif
                </x-ui.card>

                <!-- Status Transition Audit Log -->
                <x-ui.card title="Activity & Audit Trail">
                    <div class="space-y-3">
                        @forelse($return->statusLogs as $log)
                            <div class="text-xs border-l-2 border-emerald-500 pl-3 py-1">
                                <p class="font-bold text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $log->from_status)) }} &rarr; {{ ucfirst(str_replace('_', ' ', $log->to_status)) }}
                                </p>
                                <span class="text-[10px] text-gray-400">
                                    By {{ $log->changedBy->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}
                                </span>
                                @if($log->notes)
                                    <p class="text-gray-500 italic mt-0.5">{{ $log->notes }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-gray-400">Pehle se koi log maujood nahi.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>

    <!-- Modal for Marking as Filed on IRIS -->
    <x-ui.modal name="file-modal" title="Mark Return as Filed on IRIS">
        <form method="POST" action="{{ route('returns.status', $return) }}">
            @csrf
            <input type="hidden" name="status" value="filed">

            <div class="space-y-4">
                <div class="p-3 bg-teal-50 border border-teal-200 rounded-xl text-xs text-teal-800">
                    Consultant ne IRIS par return file kar diya hai. FBR Acknowledgement 114(1) receipt number darj karein taake return lock ho sake.
                </div>

                <x-ui.input
                    label="FBR IRIS Acknowledgement No. (114(1) Receipt)"
                    name="iris_acknowledgement_no"
                    placeholder="e.g. 100000000123456"
                    required="true"
                />

                <x-ui.input
                    label="CPR / Challan Number (If Tax Paid)"
                    name="iris_cpr_no"
                    placeholder="e.g. IT-2024-XXXX"
                />

                <x-ui.input
                    label="Consultant Notes"
                    name="notes"
                    placeholder="Successfully submitted on FBR IRIS portal"
                />
            </div>

            <div class="mt-6 flex items-center justify-end space-x-2">
                <x-ui.button type="button" variant="secondary" size="sm" @click="$dispatch('close-modal', 'file-modal')">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" size="sm" class="bg-teal-600 hover:bg-teal-700">
                    Confirm & Lock Return
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <!-- Modal for Wealth Statement Reconciliation Update -->
    <x-ui.modal name="wealth-modal" title="Update Wealth Statement (Section 116)">
        <form method="POST" action="{{ route('returns.wealth', $return) }}">
            @csrf

            <div class="space-y-3">
                <x-ui.input
                    label="Opening Wealth (Previous Year Closing)"
                    name="opening_wealth"
                    type="number"
                    value="{{ $wealth?->opening_wealth ?? 0 }}"
                    required="true"
                />

                <x-ui.input
                    label="Net Inflows (Total Income / Foreign Remittance)"
                    name="net_inflows"
                    type="number"
                    value="{{ $wealth?->net_inflows ?? $return->total_income }}"
                    required="true"
                />

                <x-ui.input
                    label="Personal & Living Expenses"
                    name="living_expenses"
                    type="number"
                    value="{{ $wealth?->living_expenses ?? 0 }}"
                    required="true"
                />

                <x-ui.input
                    label="Other Outflows / Gifts / Losses"
                    name="other_outflows"
                    type="number"
                    value="{{ $wealth?->other_outflows ?? 0 }}"
                    required="true"
                />

                <x-ui.input
                    label="Closing Wealth (Current Net Assets)"
                    name="closing_wealth"
                    type="number"
                    value="{{ $wealth?->closing_wealth ?? 0 }}"
                    required="true"
                />
            </div>

            <div class="mt-6 flex items-center justify-end space-x-2">
                <x-ui.button type="button" variant="secondary" size="sm" @click="$dispatch('close-modal', 'wealth-modal')">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" size="sm">
                    Save Reconciliation
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-layouts.app>
