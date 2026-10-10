<x-layouts.app header="Initiate New Tax Return">
    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('returns.store') }}">
            @csrf

            <x-ui.card
                title="Tax Return Details"
                description="Client aur mutaliqa Tax Year muntakhab karein"
            >
                <div class="space-y-4">
                    <x-ui.select
                        label="Client Name & CNIC"
                        name="client_id"
                        required="true"
                    >
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ (old('client_id', $selectedClientId) == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} (CNIC: {{ $c->cnic }})
                            </option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select
                        label="FBR Tax Year"
                        name="tax_year_id"
                        required="true"
                    >
                        @foreach($taxYears as $ty)
                            <option value="{{ $ty->id }}" {{ old('tax_year_id') == $ty->id ? 'selected' : '' }}>
                                {{ $ty->name }} (Deadline: {{ $ty->filing_deadline->format('d M Y') }})
                            </option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select
                        label="Filing Type"
                        name="filing_type"
                        required="true"
                    >
                        <option value="original" selected>Original Return (First Time)</option>
                        <option value="amendment">Revision / Amendment (Section 114(6))</option>
                    </x-ui.select>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('returns.index') }}">
                        <x-ui.button variant="secondary">
                            Cancel
                        </x-ui.button>
                    </a>
                    <x-ui.button type="submit" variant="primary">
                        Initialize Return Workspace &rarr;
                    </x-ui.button>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
