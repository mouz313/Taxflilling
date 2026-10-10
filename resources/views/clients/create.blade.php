<x-layouts.app header="Register New Client">
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
            @csrf

            <x-ui.card
                title="Client Ki Bunyadi Maloomat (Basic Information)"
                description="Client ka CNIC aur contact details darj karein. CNIC database mein encrypt ho kar mehfooz rahega."
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Full Name (Shanakhti Card Ke Mutabiq)"
                        name="name"
                        placeholder="e.g. Muhammad Ali"
                        required="true"
                    />

                    <x-ui.input
                        label="Father / Husband Name"
                        name="father_name"
                        placeholder="e.g. Tariq Mehmood"
                    />

                    <x-ui.input
                        label="CNIC Number (13 Digits)"
                        name="cnic"
                        placeholder="42101-1234567-1"
                        required="true"
                        helper="Format: 42101-1234567-1 (Unique and Encrypted)"
                    />

                    <x-ui.input
                        label="Mobile Number (Active / WhatsApp)"
                        name="phone"
                        placeholder="03001234567"
                        required="true"
                        helper="IRIS alerts aur updates bhejne ke liye"
                    />

                    <x-ui.input
                        label="Email Address"
                        name="email"
                        type="email"
                        placeholder="ali@example.com"
                    />

                    <x-ui.input
                        label="NTN Number (Agar pehle se bana ho)"
                        name="ntn"
                        placeholder="7891234-5"
                        helper="Khali chor dein agar NTN registration bhi karni hai"
                    />
                </div>

                <div class="mt-4">
                    <x-ui.input
                        label="Residential Address"
                        name="address"
                        placeholder="House #, Street, City"
                    />
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase mb-1.5">
                        Internal Notes (Sirf Consultancy Ke Liye)
                    </label>
                    <textarea
                        name="internal_notes"
                        rows="2"
                        class="w-full rounded-lg border border-gray-200 text-sm p-3 focus:outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500 hover:border-gray-300"
                        placeholder="Client ke hawale se koi khususi hidayat ya record..."
                    >{{ old('internal_notes') }}</textarea>
                </div>
            </x-ui.card>

            <!-- Optional Client Portal Login -->
            <x-ui.card
                title="Client Login Account (Ikhtiyari / Optional)"
                description="Agar aap client ko apna portal login dena chahte hain taake woh khud documents upload kar sake"
            >
                <div x-data="{ enablePortal: {{ old('create_login_account') ? 'true' : 'false' }} }" class="space-y-4">
                    <label class="flex items-center space-x-2.5 cursor-pointer">
                        <input
                            type="checkbox"
                            name="create_login_account"
                            value="1"
                            x-model="enablePortal"
                            class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500"
                        >
                        <span class="text-sm font-semibold text-gray-800">Client ke liye Web/App Login Account banayein</span>
                    </label>

                    <div x-show="enablePortal" x-cloak class="pt-2">
                        <x-ui.input
                            label="Temporary Password"
                            name="account_password"
                            type="text"
                            value="client123"
                            helper="Yeh password client ko SMS/WhatsApp par bhej dein"
                        />
                    </div>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <a href="{{ route('clients.index') }}">
                    <x-ui.button variant="secondary">
                        Cancel
                    </x-ui.button>
                </a>
                <x-ui.button type="submit" variant="primary">
                    Save Client & Continue
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
