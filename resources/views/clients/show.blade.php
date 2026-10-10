<x-layouts.app :header="'Client: ' . $client->name">
    <div class="space-y-6">
        <!-- Client Profile Header -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-lg flex items-center justify-center flex-shrink-0">
                    {{ substr($client->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center space-x-3">
                        <h2 class="text-xl font-bold text-gray-900">{{ $client->name }}</h2>
                        <x-ui.badge :status="$client->status" />
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-gray-500 font-mono">
                        <span>CNIC: <strong>{{ $client->cnic }}</strong></span>
                        <span>Phone: <strong>{{ $client->phone }}</strong></span>
                        @if($client->ntn)
                            <span>NTN: <strong>{{ $client->ntn }}</strong></span>
                        @else
                            <span class="text-amber-600">NTN: Not Registered</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2.5">
                <a href="{{ route('clients.edit', $client) }}">
                    <x-ui.button variant="secondary" size="sm">
                        Edit Profile
                    </x-ui.button>
                </a>
                <a href="{{ route('returns.create', ['client_id' => $client->id]) }}">
                    <x-ui.button variant="primary" size="sm">
                        + Start Tax Return
                    </x-ui.button>
                </a>
            </div>
        </div>

        <!-- Returns History for this Client -->
        <x-ui.card
            title="Tax Returns Dossier"
            description="Client ke har tax saal ka return, income items, aur filing status"
        >
            <x-slot:actions>
                <a href="{{ route('returns.create', ['client_id' => $client->id]) }}">
                    <x-ui.button variant="outline" size="sm">
                        + New Tax Year Return
                    </x-ui.button>
                </a>
            </x-slot:actions>

            <x-ui.table
                :headers="['Tax Year', 'Total Income', 'Taxable Income', 'Advance Tax', 'Payable / Refund', 'Status', 'IRIS 114(1) Code', 'Action']"
                :empty="$client->taxReturns->isEmpty()"
                emptyMessage="Is client ka abhi koi return shamil nahi kiya gaya."
            >
                @foreach($client->taxReturns as $ret)
                    <tr>
                        <x-ui.td class="font-bold text-gray-900">
                            {{ $ret->taxYear->name }}
                        </x-ui.td>
                        <x-ui.td>
                            PKR {{ number_format($ret->total_income, 0) }}
                        </x-ui.td>
                        <x-ui.td>
                            PKR {{ number_format($ret->taxable_income, 0) }}
                        </x-ui.td>
                        <x-ui.td>
                            PKR {{ number_format($ret->tax_deducted_advance, 0) }}
                        </x-ui.td>
                        <x-ui.td>
                            @if($ret->net_tax_payable_or_refundable > 0)
                                <span class="text-xs font-semibold text-rose-600">Payable: PKR {{ number_format($ret->net_tax_payable_or_refundable, 0) }}</span>
                            @elseif($ret->net_tax_payable_or_refundable < 0)
                                <span class="text-xs font-semibold text-teal-600">Refund: PKR {{ number_format(abs($ret->net_tax_payable_or_refundable), 0) }}</span>
                            @else
                                <span class="text-xs text-gray-400">PKR 0</span>
                            @endif
                        </x-ui.td>
                        <x-ui.td>
                            <x-ui.badge :status="$ret->status" />
                        </x-ui.td>
                        <x-ui.td class="font-mono text-xs">
                            {{ $ret->iris_acknowledgement_no ?? 'Pending' }}
                        </x-ui.td>
                        <x-ui.td>
                            <a href="{{ route('returns.show', $ret) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800">
                                Open Return &rarr;
                            </a>
                        </x-ui.td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        @if($client->internal_notes)
            <x-ui.card title="Internal Consultancy Notes (Confidential)">
                <p class="text-sm text-gray-700 bg-amber-50/50 p-4 rounded-xl border border-amber-100 whitespace-pre-wrap">
                    {{ $client->internal_notes }}
                </p>
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
