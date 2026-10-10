<x-layouts.guest title="Application Submitted & Payment - TaxFiler.pk">
    <div class="max-w-2xl mx-auto py-6 sm:py-10">
        <div class="bg-white rounded-3xl border border-gray-100 p-8 sm:p-10 shadow-sm text-center">
            <!-- Green Success Check -->
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
                ✓
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                Mubarak Ho! Application Received
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-2 max-w-md mx-auto">
                Aapki corporate service ki darhwast hamare tax consultant console ko kamiyabi se mosool ho chuki hai.
            </p>

            <!-- Tracking Card -->
            <div class="mt-6 p-5 bg-blue-50/70 border border-blue-200/60 rounded-2xl text-left flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Your Tracking Number:</span>
                    <div class="text-xl font-black text-blue-900 font-mono mt-0.5">
                        {{ $trackingNumber ?? 'SR-PENDING' }}
                    </div>
                    @if($serviceRequest)
                        <div class="text-xs text-blue-700 mt-1 font-medium">
                            Service: <strong>{{ $serviceRequest->service_name }}</strong> • Fee: <strong>PKR {{ number_format($serviceRequest->fee_amount, 0) }}</strong>
                        </div>
                    @endif
                </div>

                <div class="text-xs text-gray-500 bg-white px-3 py-1.5 rounded-xl border border-blue-200 font-mono flex-shrink-0">
                    Status: <strong class="text-amber-600 capitalize">{{ $serviceRequest ? str_replace('_', ' ', $serviceRequest->status) : 'Submitted' }}</strong>
                </div>
            </div>

            <!-- Payment Checkout Card (Phase 1 Integration) -->
            @if($serviceRequest)
                <div class="mt-6 text-left" x-data="{ payMethod: '1bill', transRef: '' }">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 shadow-2xs">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Digital Fee Payment</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Service charges for registration & official filing</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-slate-400">Total Payable:</span>
                                <div class="text-base font-extrabold text-slate-900 font-mono">PKR {{ number_format($serviceRequest->fee_amount, 0) }}</div>
                            </div>
                        </div>

                        @if($serviceRequest->payment_status === 'paid')
                            <!-- Payment Verified Banner -->
                            <div class="mt-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center space-x-3 text-emerald-800">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center font-bold text-sm text-emerald-700">✓</div>
                                <div>
                                    <h4 class="text-xs font-bold">Payment Verified & Paid</h4>
                                    <p class="text-[11px] font-mono text-emerald-700">Ref: {{ $serviceRequest->payment_reference ?? 'N/A' }} via {{ ucfirst($serviceRequest->payment_method ?? 'Online') }}</p>
                                </div>
                            </div>
                        @else
                            <!-- Payment Form & Options -->
                            <form method="POST" action="{{ route('services.pay', $serviceRequest) }}" class="mt-4 space-y-4">
                                @csrf

                                <!-- Payment Method Tabs -->
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        type="button"
                                        @click="payMethod = '1bill'"
                                        :class="payMethod === '1bill' ? 'border-blue-600 bg-blue-50/60 text-blue-700 font-bold' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                        class="p-2.5 rounded-xl border text-xs text-center transition flex flex-col items-center justify-center space-y-1"
                                    >
                                        <span class="text-base">💳</span>
                                        <span>1Bill (1Link)</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="payMethod = 'jazzcash'"
                                        :class="payMethod === 'jazzcash' ? 'border-blue-600 bg-blue-50/60 text-blue-700 font-bold' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                        class="p-2.5 rounded-xl border text-xs text-center transition flex flex-col items-center justify-center space-y-1"
                                    >
                                        <span class="text-base">📱</span>
                                        <span>JazzCash</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="payMethod = 'bank_transfer'"
                                        :class="payMethod === 'bank_transfer' ? 'border-blue-600 bg-blue-50/60 text-blue-700 font-bold' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                        class="p-2.5 rounded-xl border text-xs text-center transition flex flex-col items-center justify-center space-y-1"
                                    >
                                        <span class="text-base">🏛️</span>
                                        <span>Bank Transfer</span>
                                    </button>
                                </div>

                                <input type="hidden" name="payment_method" :value="payMethod">

                                <!-- 1Bill PSID Details -->
                                <div x-show="payMethod === '1bill'" class="p-3 bg-white border border-slate-200 rounded-xl space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">1Bill Consumer Number:</span>
                                        <strong class="font-mono text-slate-800 text-sm">100088{{ str_pad($serviceRequest->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                    </div>
                                    <p class="text-[11px] text-slate-400">
                                        Apne bank app (HBL, Meezan, Alfalah, Nayapay) mein <strong>1Bill Invoices</strong> search karein aur payment karein.
                                    </p>
                                </div>

                                <!-- JazzCash / Mobile Details -->
                                <div x-show="payMethod === 'jazzcash'" class="p-3 bg-white border border-slate-200 rounded-xl space-y-2 text-xs">
                                    <label class="block text-slate-600 font-semibold mb-1">JazzCash Account Number / TID:</label>
                                    <input
                                        type="text"
                                        name="payment_reference"
                                        placeholder="e.g. 03001234567 or TID 9281923"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg font-mono text-xs outline-none focus:border-blue-500"
                                    />
                                </div>

                                <!-- Bank Transfer Details -->
                                <div x-show="payMethod === 'bank_transfer'" class="p-3 bg-white border border-slate-200 rounded-xl space-y-2 text-xs">
                                    <div class="text-slate-600">
                                        <p><strong>Bank:</strong> Meezan Bank Limited</p>
                                        <p><strong>Title:</strong> TaxFiler Advisory Pvt Ltd</p>
                                        <p class="font-mono"><strong>IBAN:</strong> PK89MEZN0001020304050607</p>
                                    </div>
                                    <div>
                                        <label class="block text-slate-600 font-semibold mt-2 mb-1">IBFT Reference Number:</label>
                                        <input
                                            type="text"
                                            name="payment_reference"
                                            placeholder="e.g. IBFT-83921092"
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg font-mono text-xs outline-none focus:border-blue-500"
                                        />
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center space-x-2"
                                >
                                    <span>✓ Pay & Confirm Fee (PKR {{ number_format($serviceRequest->fee_amount, 0) }})</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- WhatsApp Instant Share Button (Phase 2 Integration) -->
            @if($serviceRequest)
                <div class="mt-4 text-center">
                    <a
                        href="https://wa.me/923001234567?text={{ urlencode("Assalam o Alaikum, maine {$serviceRequest->service_name} ki application submit ki hai. Mera Tracking ID {$serviceRequest->tracking_number} hai.") }}"
                        target="_blank"
                        class="inline-flex items-center space-x-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-4 py-2 rounded-xl transition shadow-2xs"
                    >
                        <span>💬</span>
                        <span>Send Tracking ID on WhatsApp</span>
                    </a>
                </div>
            @endif

            <!-- 4-Step Lifecycle Timeline -->
            <div class="mt-8 pt-6 border-t border-gray-100 text-left">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Application Processing Roadmap:</h3>
                
                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-blue-200">
                    <!-- Step 1 -->
                    <div class="relative flex items-start space-x-3">
                        <div class="absolute -left-6 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">✓</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900">1. Online Application Submitted</h4>
                            <p class="text-[11px] text-gray-500">Applicant data and documents securely logged in portal.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative flex items-start space-x-3">
                        <div class="absolute -left-6 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold animate-pulse">●</div>
                        <div>
                            <h4 class="text-xs font-bold text-amber-700">2. Tax Lawyer & Consultant Review</h4>
                            <p class="text-[11px] text-gray-500">Hamari compliance team aapke data ki preliminary verification karegi.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative flex items-start space-x-3">
                        <div class="absolute -left-6 w-4 h-4 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-[10px] font-bold">3</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-500">3. FBR / SECP Official Filing</h4>
                            <p class="text-[11px] text-gray-400">Relevant government authority portal par official submission.</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative flex items-start space-x-3">
                        <div class="absolute -left-6 w-4 h-4 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-[10px] font-bold">4</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-500">4. Certificate / NTN Delivery</h4>
                            <p class="text-[11px] text-gray-400">Final verified PDF slip aapke WhatsApp aur portal par deliver hogi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Footer -->
            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a
                    href="{{ route('services.index') }}"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold text-xs transition"
                >
                    &larr; View More Services
                </a>

                <a
                    href="{{ route('track', ['query' => $trackingNumber]) }}"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition"
                >
                    Live Tracker Page &rarr;
                </a>
            </div>
        </div>
    </div>
</x-layouts.guest>
