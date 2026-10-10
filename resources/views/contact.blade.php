<x-layouts.guest title="Contact Us & Tax Consultation - TaxFiler.pk">
    <div class="max-w-5xl mx-auto space-y-10 py-4">
        <!-- Header -->
        <div class="text-center space-y-3">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                <span>📍</span>
                <span>Get In Touch</span>
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Connect with Certified Tax & Corporate Advocates
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto">
                Whether you need assistance with an FBR tax audit, SECP company registration, or salaried tax returns, our legal team is ready to assist.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Info Cards -->
            <div class="lg:col-span-5 space-y-4">
                <!-- Direct WhatsApp Banner Card -->
                <div class="bg-slate-900 rounded-2xl p-6 text-white border border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-[#25D366] flex items-center justify-center text-xl flex-shrink-0">
                            💬
                        </div>
                        <div>
                            <span class="text-[11px] uppercase tracking-wider text-emerald-400 font-bold">Fastest Response</span>
                            <h3 class="text-base font-bold text-white">Instant WhatsApp Helpline</h3>
                        </div>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Message our tax desk directly for real-time document review, NTN inquiries, and fee quotes.
                    </p>
                    <a
                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $consultancy->whatsapp ?? '923001234567') }}?text=Assalam%20o%20Alaikum,%20mujhe%20tax%20filing%20aur%20corporate%20services%20ke%20hawaale%20se%20rabta%20karna%20hai."
                        target="_blank"
                        class="block w-full text-center py-3 bg-[#25D366] hover:bg-[#1ebd59] text-slate-950 font-bold rounded-xl text-xs transition shadow-sm cursor-pointer"
                    >
                        Chat with Advocate on WhatsApp &rarr;
                    </a>
                </div>

                <!-- Office Locations Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Office Details</h4>

                    <div class="space-y-4 text-xs text-slate-600">
                        <div class="flex items-start space-x-3">
                            <span class="text-lg">🏢</span>
                            <div>
                                <strong class="text-slate-900 block font-semibold">Head Office:</strong>
                                <span>{{ $consultancy->address ?? 'Suite 402, 4th Floor, Business Avenue, Main Shahrah-e-Faisal, Karachi' }}</span>
                            </div>
                        </div>

                        @if(!empty($consultancy->secondary_address))
                            <div class="flex items-start space-x-3">
                                <span class="text-lg">🏛️</span>
                                <div>
                                    <strong class="text-slate-900 block font-semibold">Regional Desk:</strong>
                                    <span>{{ $consultancy->secondary_address }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-start space-x-3">
                            <span class="text-lg">📞</span>
                            <div>
                                <strong class="text-slate-900 block font-semibold">Phone Helpline:</strong>
                                <span class="font-mono text-slate-800 font-bold">{{ $consultancy->phone ?? '+92 300 1234567' }}</span>
                                @if(!empty($consultancy->secondary_phone))
                                    / <span>{{ $consultancy->secondary_phone }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <span class="text-lg">✉️</span>
                            <div>
                                <strong class="text-slate-900 block font-semibold">Official Email:</strong>
                                <span class="text-blue-600 font-mono">{{ $consultancy->email ?? 'support@taxfiler.pk' }}</span>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <span class="text-lg">⏱️</span>
                            <div>
                                <strong class="text-slate-900 block font-semibold">Working Hours:</strong>
                                <span>{{ $consultancy->office_hours ?? 'Monday to Saturday: 9:00 AM – 7:00 PM' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Interactive Inquiry & Callback Form -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800">Request a Free Callback & Consultation</h3>
                    <p class="text-xs text-slate-500 mt-1 mb-6">Apni details darj karein. Hamara certified tax consultant 30 minute mein aapse rabta karega.</p>

                    <form method="POST" action="{{ route('services.callback') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Your Full Name *</label>
                                <input
                                    type="text"
                                    name="name"
                                    required
                                    placeholder="e.g. Usman Ali"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-200 outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp / Phone Number *</label>
                                <input
                                    type="text"
                                    name="phone"
                                    required
                                    placeholder="0300-1234567"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-200 font-mono outline-none"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Service Interested In</label>
                                <select
                                    name="service_name"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 outline-none bg-white text-slate-700"
                                >
                                    <option value="Salaried Income Tax Return">Salaried Income Tax Return (PKR 3,000)</option>
                                    <option value="Individual NTN Registration">Individual NTN Registration (PKR 1,500)</option>
                                    <option value="Business Tax Return & Audit">Business Tax Return & Audit</option>
                                    <option value="SECP Private Limited Company">SECP Private Limited Company</option>
                                    <option value="Sales Tax STRN (GST / PRA)">Sales Tax STRN (GST / PRA)</option>
                                    <option value="USA LLC & Corporate Setup">USA Delaware LLC & EIN</option>
                                    <option value="Other Corporate Inquiry">Other Legal Inquiry</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Preferred Time for Call</label>
                                <select
                                    name="preferred_time"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 outline-none bg-white text-slate-700"
                                >
                                    <option value="morning">Morning (9 AM - 12 PM)</option>
                                    <option value="afternoon" selected>Afternoon (12 PM - 4 PM)</option>
                                    <option value="evening">Evening (4 PM - 7 PM)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Question or Specific Request (Optional)</label>
                            <textarea
                                name="notes"
                                rows="3"
                                placeholder="Aapka tax ya corporate masla yahan likhein taake consultant poori tayyari ke sath call kare..."
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-200 outline-none resize-none"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-600/20 cursor-pointer flex items-center justify-center space-x-2"
                        >
                            <span>Submit Request for Callback</span>
                            <span>&rarr;</span>
                        </button>
                        <p class="text-[11px] text-slate-400 text-center">🔒 Di gayi tamam information 100% confidential rakhi jati hai.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
