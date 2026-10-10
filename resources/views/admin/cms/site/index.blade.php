<x-layouts.app>
    <x-slot:header>
        CMS &bull; Site Profile &amp; Contact Info
    </x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Top Title Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div>
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">
                    <span>⚙️ Content Management System</span>
                    <span>&bull;</span>
                    <span>Firm Information</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Public Website Contact &amp; Office Details</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Yahan se aap website ke footer, contact page, WhatsApp helpline, aur office addresses ko dynamically update kar sakte hain.
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a
                    href="{{ route('contact') }}"
                    target="_blank"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition"
                >
                    <span>🌐 Live Contact Page</span>
                    <span class="text-[10px] font-mono text-slate-400">↗</span>
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center space-x-2">
                    <span>✅</span>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-1">
                <strong class="font-bold block">Please fix the following:</strong>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.cms.site.update') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Firm Identity & Digital Helplines -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Brand Identity &amp; Helplines</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Firm ka legal title, WhatsApp helpline aur public customer support numbers.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Firm Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Company / Firm Name *</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $consultancy->name) }}"
                            required
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                    </div>

                    <!-- NTN Number -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Firm NTN Registration #</label>
                        <input
                            type="text"
                            name="ntn_registration_no"
                            value="{{ old('ntn_registration_no', $consultancy->ntn_registration_no) }}"
                            placeholder="e.g. 9876543-2"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-mono"
                        >
                    </div>

                    <!-- Direct WhatsApp Helpline -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp Helpline Number (Without + or spaces) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-xs text-emerald-600 font-bold">💬</span>
                            <input
                                type="text"
                                name="whatsapp"
                                value="{{ old('whatsapp', $consultancy->whatsapp ?: '923001234567') }}"
                                required
                                placeholder="923001234567"
                                class="w-full text-xs pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 font-mono"
                            >
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">Format: 92300XXXXXXX (Pakistan country code 92 ke sath).</span>
                    </div>

                    <!-- Phone Helpline -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Public Phone Helpline *</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $consultancy->phone) }}"
                            required
                            placeholder="+92 300 1234567"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>

                    <!-- Secondary Phone / Landline -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Secondary Phone / Landline</label>
                        <input
                            type="text"
                            name="secondary_phone"
                            value="{{ old('secondary_phone', $consultancy->secondary_phone) }}"
                            placeholder="(021) 3456-7890"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>

                    <!-- Support Email -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Official Support Email *</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $consultancy->email) }}"
                            required
                            placeholder="support@taxfiler.pk"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Section 2: Physical Office Addresses & Working Hours -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Physical Office Addresses &amp; Hours</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Head office aur regional branches ke address aur working hours.</p>
                </div>

                <div class="space-y-4">
                    <!-- Head Office (Karachi) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Head Office Address *</label>
                        <textarea
                            name="address"
                            rows="2"
                            required
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >{{ old('address', $consultancy->address) }}</textarea>
                    </div>

                    <!-- Regional Branch (Islamabad) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Regional Branch / Secondary Office Address</label>
                        <textarea
                            name="secondary_address"
                            rows="2"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >{{ old('secondary_address', $consultancy->secondary_address) }}</textarea>
                    </div>

                    <!-- Office Hours -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Business Timings &amp; Working Days</label>
                        <input
                            type="text"
                            name="office_hours"
                            value="{{ old('office_hours', $consultancy->office_hours) }}"
                            placeholder="Monday - Saturday (9:00 AM - 7:00 PM PKT)"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Section 3: Default Tax Practice Fees -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">3. Standard Practice Base Fees</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Default return filing aur NTN processing fees.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Salary Tax Return Fee (PKR) *</label>
                        <input
                            type="number"
                            name="default_return_fee"
                            value="{{ old('default_return_fee', $consultancy->default_return_fee) }}"
                            required
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 tabular-nums font-bold"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Individual NTN Fee (PKR) *</label>
                        <input
                            type="number"
                            name="default_ntn_fee"
                            value="{{ old('default_ntn_fee', $consultancy->default_ntn_fee) }}"
                            required
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 tabular-nums font-bold"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Wealth Statement Fee (PKR) *</label>
                        <input
                            type="number"
                            name="default_wealth_statement_fee"
                            value="{{ old('default_wealth_statement_fee', $consultancy->default_wealth_statement_fee) }}"
                            required
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 tabular-nums font-bold"
                        >
                    </div>
                </div>
            </div>

            <!-- Submit Action Footer -->
            <div class="flex items-center justify-end bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <button
                    type="submit"
                    class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                >
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
