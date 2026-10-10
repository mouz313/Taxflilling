<x-layouts.app header="Company Settings & Fee Management">
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-6">
            @csrf

            <x-ui.card
                title="Consultancy Profile"
                description="Apni tax firm ki maloomat darj karein jo clients ke documents aur invoices par aayengi"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Firm / Consultancy Name"
                        name="name"
                        value="{{ $consultancy->name }}"
                        required="true"
                    />

                    <x-ui.input
                        label="FBR NTN Registration No."
                        name="ntn_registration_no"
                        value="{{ $consultancy->ntn_registration_no }}"
                    />

                    <x-ui.input
                        label="Official Phone / WhatsApp"
                        name="phone"
                        value="{{ $consultancy->phone }}"
                    />

                    <x-ui.input
                        label="Official Email"
                        name="email"
                        type="email"
                        value="{{ $consultancy->email }}"
                    />
                </div>

                <div class="mt-4">
                    <x-ui.input
                        label="Office Address"
                        name="address"
                        value="{{ $consultancy->address }}"
                    />
                </div>
            </x-ui.card>

            <x-ui.card
                title="Default Fee Structure (PKR)"
                description="Naye clients aur returns ke liye bunyadi fee rates"
            >
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input
                        label="Tax Return Filing Fee"
                        name="default_return_fee"
                        type="number"
                        value="{{ $consultancy->default_return_fee }}"
                        required="true"
                    />

                    <x-ui.input
                        label="NTN Registration Fee"
                        name="default_ntn_fee"
                        type="number"
                        value="{{ $consultancy->default_ntn_fee }}"
                        required="true"
                    />

                    <x-ui.input
                        label="Wealth Statement Fee"
                        name="default_wealth_statement_fee"
                        type="number"
                        value="{{ $consultancy->default_wealth_statement_fee }}"
                        required="true"
                    />
                </div>
            </x-ui.card>

            <div class="flex justify-end">
                <x-ui.button type="submit" variant="primary">
                    Save Consultancy Settings &rarr;
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
