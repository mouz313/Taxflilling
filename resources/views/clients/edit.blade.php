<x-layouts.app :header="'Edit Client: ' . $client->name">
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-ui.card
                title="Client Ki Maloomat Edit Karein"
                description="Client ka CNIC locked hai aur change nahi kiya ja sakta."
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input
                        label="Full Name"
                        name="name"
                        value="{{ $client->name }}"
                        required="true"
                    />

                    <x-ui.input
                        label="Father / Husband Name"
                        name="father_name"
                        value="{{ $client->father_name }}"
                    />

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase mb-1.5">
                            CNIC Number (Locked)
                        </label>
                        <input
                            type="text"
                            value="{{ $client->cnic }}"
                            disabled
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 text-gray-500 text-sm px-3.5 py-2.5 font-mono cursor-not-allowed"
                        />
                        <p class="text-[11px] text-gray-400 mt-1">Security aur FBR record integrity ke liye CNIC locked hai.</p>
                    </div>

                    <x-ui.input
                        label="Mobile Number"
                        name="phone"
                        value="{{ $client->phone }}"
                        required="true"
                    />

                    <x-ui.input
                        label="Email Address"
                        name="email"
                        type="email"
                        value="{{ $client->email }}"
                    />

                    <x-ui.input
                        label="NTN Number"
                        name="ntn"
                        value="{{ $client->ntn }}"
                    />

                    <x-ui.select
                        label="Status"
                        name="status"
                        required="true"
                    >
                        <option value="active" {{ $client->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending_ntn" {{ $client->status === 'pending_ntn' ? 'selected' : '' }}>Pending NTN</option>
                        <option value="inactive" {{ $client->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </x-ui.select>
                </div>

                <div class="mt-4">
                    <x-ui.input
                        label="Address"
                        name="address"
                        value="{{ $client->address }}"
                    />
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase mb-1.5">
                        Internal Notes
                    </label>
                    <textarea
                        name="internal_notes"
                        rows="2"
                        class="w-full rounded-lg border border-gray-200 text-sm p-3 focus:outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500"
                    >{{ old('internal_notes', $client->internal_notes) }}</textarea>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('clients.show', $client) }}">
                        <x-ui.button variant="secondary">
                            Cancel
                        </x-ui.button>
                    </a>
                    <x-ui.button type="submit" variant="primary">
                        Update Client Profile &rarr;
                    </x-ui.button>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
