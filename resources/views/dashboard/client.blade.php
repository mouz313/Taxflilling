<x-layouts.app header="My Tax & Corporate Desk">
    <div class="space-y-6">
        @if(! $client)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-amber-900">
                <h3 class="font-semibold text-sm">Profile Incomplete</h3>
                <p class="text-xs mt-1 text-amber-800">Aapki client profile abhi register nahi hui hai. Barah-e-karam apne tax consultant se rabta karein.</p>
            </div>
        @else
            <!-- Header Greeting (Spec 5.2) -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-200">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">Assalam o Alaikum, {{ auth()->user()->name }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Tax Year 2026–2027 Filing & Status Portal</p>
                </div>
                <div class="flex items-center space-x-3 text-xs text-slate-500">
                    <span class="bg-white border border-slate-200 rounded-lg px-3 py-1.5 font-mono shadow-2xs">
                        CNIC: <strong class="text-slate-800">{{ $client->cnic }}</strong>
                    </span>
                    @if($client->ntn)
                        <span class="hidden md:inline-block bg-white border border-slate-200 rounded-lg px-3 py-1.5 font-mono shadow-2xs">
                            NTN: <strong class="text-slate-800">{{ $client->ntn }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            @if($myReturns->isNotEmpty())
                @php
                    $latestReturn = $myReturns->first();
                    $stages = [
                        ['id' => 'draft', 'label' => 'Draft'],
                        ['id' => 'docs_pending', 'label' => 'Docs'],
                        ['id' => 'in_review', 'label' => 'Review'],
                        ['id' => 'ready_to_file', 'label' => 'Ready'],
                        ['id' => 'filed', 'label' => 'Filed'],
                    ];
                    $currentStatus = $latestReturn->status;
                    $statusOrder = ['draft' => 0, 'docs_pending' => 1, 'in_review' => 2, 'query' => 2, 'ready_to_file' => 3, 'filed' => 4, 'closed' => 4];
                    $currentStageIndex = $statusOrder[$currentStatus] ?? 0;

                    $nextNotes = [
                        'draft' => 'Tax details mukammal karein aur review ke liye submit karein.',
                        'docs_pending' => 'Zaroori tax documents (Salary slip ya bank statement) upload karein.',
                        'in_review' => 'Consultant aap ka return review kar raha hai.',
                        'query' => 'Consultant ne sawal poocha hai. Documents/clarification check karein.',
                        'ready_to_file' => 'Return FBR IRIS portal par submit hone ke liye tayyar hai.',
                        'filed' => 'Mubarak ho! FBR IRIS par aapka return kamiyabi se file ho gaya hai.',
                        'closed' => 'Yeh return mukammal aur band ho chuka hai.',
                    ];
                    $nextMessage = $nextNotes[$currentStatus] ?? 'Consultant aap ka return process kar raha hai.';
                @endphp

                <!-- Active Return Card with Visual Timeline (Spec 5.2) -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                📑
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-semibold text-slate-800">
                                    Tax Year {{ $latestReturn->taxYear->name }}
                                </h2>
                                <p class="text-xs text-slate-500">
                                    Filing Type: <span class="capitalize font-medium text-slate-700">{{ $latestReturn->filing_type }}</span>
                                    | Created: <span class="tabular-nums">{{ $latestReturn->created_at->format('d M Y') }}</span>
                                </p>
                            </div>
                        </div>

                        <div>
                            <x-ui.status-badge :status="$latestReturn->status" />
                        </div>
                    </div>

                    <!-- Timeline (●──●──●──○──○) -->
                    <div class="py-2">
                        <div class="flex items-center justify-between relative">
                            <!-- Background connecting track -->
                            <div class="absolute top-3.5 left-4 right-4 h-0.5 bg-slate-200 -z-0"></div>

                            @foreach($stages as $idx => $st)
                                @php
                                    $isDone = $idx < $currentStageIndex;
                                    $isCurrent = $idx === $currentStageIndex;
                                @endphp
                                <div class="flex flex-col items-center relative z-10">
                                    <div @class([
                                        'w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition',
                                        'bg-emerald-600 text-white ring-4 ring-emerald-50' => $isDone,
                                        'bg-blue-600 text-white ring-4 ring-blue-100' => $isCurrent,
                                        'bg-white border-2 border-slate-300 text-slate-400' => ! $isDone && ! $isCurrent,
                                    ])>
                                        @if($isDone)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @elseif($isCurrent)
                                            <span class="w-2 h-2 rounded-full bg-white"></span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        @endif
                                    </div>
                                    <span @class([
                                        'text-[11px] mt-1.5 font-medium tracking-tight',
                                        'text-emerald-700' => $isDone,
                                        'text-blue-600 font-semibold' => $isCurrent,
                                        'text-slate-400' => ! $isDone && ! $isCurrent,
                                    ])>
                                        {{ $st['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Next Step Callout & Action Buttons -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-start space-x-2.5">
                            <span class="text-blue-600 text-base">ℹ️</span>
                            <div>
                                <p class="text-xs font-semibold text-slate-800">
                                    Next: {{ $nextMessage }}
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Taxable Income: <span class="font-mono tabular-nums font-semibold text-slate-700">PKR {{ number_format($latestReturn->taxable_income, 0) }}</span>
                                    | Net Payable/Refund: <span class="font-mono tabular-nums font-semibold {{ $latestReturn->isPayable() ? 'text-red-600' : 'text-emerald-600' }}">PKR {{ number_format(abs($latestReturn->net_tax_payable_or_refundable), 0) }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <a href="{{ route('returns.wizard', $latestReturn) }}">
                                <x-ui.button variant="secondary" size="sm">
                                    Tax Breakdown
                                </x-ui.button>
                            </a>
                            <a href="{{ route('wizard.filing', $latestReturn) }}">
                                <x-ui.button variant="primary" size="sm">
                                    View Return &rarr;
                                </x-ui.button>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pending Actions (Spec 5.2) -->
                @if($latestReturn->status === 'draft' || $latestReturn->status === 'docs_pending' || $latestReturn->status === 'query')
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <h3 class="text-sm font-semibold text-slate-800">Pending Actions</h3>
                            <span class="text-xs text-amber-700 font-medium bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                Action Required
                            </span>
                        </div>

                        <div class="space-y-2">
                            @if($latestReturn->status === 'docs_pending')
                                <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50/50 border border-amber-200/80">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-amber-600 font-bold">!</span>
                                        <span class="text-xs text-slate-800 font-medium">Salary slip & Bank statement upload karein</span>
                                    </div>
                                    <a href="{{ route('wizard.filing', $latestReturn) }}">
                                        <x-ui.button variant="secondary" size="xs">
                                            Upload
                                        </x-ui.button>
                                    </a>
                                </div>
                            @endif

                            @if($latestReturn->status === 'query')
                                <div class="flex items-center justify-between p-3 rounded-lg bg-orange-50/50 border border-orange-200/80">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-orange-600 font-bold">?</span>
                                        <span class="text-xs text-slate-800 font-medium">Query: Consultant clarification required</span>
                                    </div>
                                    <a href="{{ route('returns.wizard', $latestReturn) }}">
                                        <x-ui.button variant="secondary" size="xs">
                                            View Query
                                        </x-ui.button>
                                    </a>
                                </div>
                            @endif

                            @if($latestReturn->status === 'draft')
                                <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50/50 border border-blue-200/80">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-blue-600 font-bold">📝</span>
                                        <span class="text-xs text-slate-800 font-medium">Annual income aur deduction figures verify karein</span>
                                    </div>
                                    <a href="{{ route('wizard.filing', $latestReturn) }}">
                                        <x-ui.button variant="secondary" size="xs">
                                            Continue
                                        </x-ui.button>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Returns History & FBR Slips (Spec 5.2) -->
                <x-ui.card title="Tax Returns & FBR Slips" description="Aapke tamaam tax saalon ke returns ka record">
                    <x-ui.table
                        :headers="['Tax Year', 'Total Income', 'Tax Status', 'Filing Status', 'FBR 114(1) Receipt', 'Action']"
                        :empty="$myReturns->isEmpty()"
                        emptyMessage="Aapka koi tax return abhi system mein darj nahi hai."
                    >
                        @foreach($myReturns as $ret)
                            <tr class="hover:bg-slate-50 transition">
                                <x-ui.td class="font-semibold text-slate-800">
                                    {{ $ret->taxYear->name }}
                                </x-ui.td>
                                <x-ui.td class="font-mono tabular-nums">
                                    PKR {{ number_format($ret->total_income, 0) }}
                                </x-ui.td>
                                <x-ui.td>
                                    @if($ret->net_tax_payable_or_refundable > 0)
                                        <span class="text-xs font-semibold text-red-600 font-mono tabular-nums">Payable: PKR {{ number_format($ret->net_tax_payable_or_refundable, 0) }}</span>
                                    @elseif($ret->net_tax_payable_or_refundable < 0)
                                        <span class="text-xs font-semibold text-emerald-600 font-mono tabular-nums">Refund: PKR {{ number_format(abs($ret->net_tax_payable_or_refundable), 0) }}</span>
                                    @else
                                        <span class="text-xs text-slate-500 font-mono">Nil Tax</span>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>
                                    <x-ui.status-badge :status="$ret->status" />
                                </x-ui.td>
                                <x-ui.td>
                                    @if($ret->iris_acknowledgement_no)
                                        <span class="text-xs font-mono tabular-nums bg-emerald-50 text-emerald-800 font-medium px-2.5 py-1 rounded-full border border-emerald-200">
                                            ✓ {{ $ret->iris_acknowledgement_no }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Under Process</span>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('wizard.filing', $ret) }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                            Open &rarr;
                                        </a>
                                    </div>
                                </x-ui.td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </x-ui.card>
            @else
                <!-- Empty State (Spec 5.2: "Tax return shuru karein" big CTA) -->
                <div class="bg-white border border-slate-200 rounded-xl p-10 text-center shadow-sm max-w-md mx-auto my-8">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        📋
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800">Abhi koi tax return shuru nahi kiya</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                        6 aasan marhalon mein apna FBR income tax return tayyar karein aur submit karein.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('services.index') }}">
                            <x-ui.button variant="primary" class="w-full">
                                Tax return shuru karein &rarr;
                            </x-ui.button>
                        </a>
                    </div>
                </div>
            @endif

            <!-- ========================================================================= -->
            <!-- MY CORPORATE & NTN SERVICE APPLICATIONS (Spec: My Applications)           -->
            <!-- ========================================================================= -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4" x-data="{ appSearch: '' }">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xl">💼</span>
                            <h2 class="text-base sm:text-lg font-bold text-slate-800">My Applications (Meri Corporate &amp; NTN Applications)</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 tabular-nums">
                                {{ $myServiceRequests->count() }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Aapki jama karwayi gayi NTN, Sales Tax, Company registration, aur doosri services ka live status aur 9-digit application tracking number.
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Real-time search inside My Applications -->
                        <div class="relative">
                            <input
                                type="text"
                                x-model="appSearch"
                                placeholder="Search by name, app #..."
                                class="w-48 sm:w-56 text-xs pl-7 pr-3 py-1.5 rounded-lg border border-slate-200 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                            <span class="absolute left-2 top-2 text-xs text-slate-400">🔍</span>
                        </div>

                        <a
                            href="{{ route('services.index') }}"
                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1"
                        >
                            <span>➕ Apply New</span>
                        </a>
                    </div>
                </div>

                @if($myServiceRequests->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                    <th class="py-3 px-4">Application #</th>
                                    <th class="py-3 px-4">Service Details</th>
                                    <th class="py-3 px-4">Submitted Date</th>
                                    <th class="py-3 px-4">Fee &amp; Payment</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4">Official Document</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($myServiceRequests as $app)
                                    <tr
                                        x-show="!appSearch || '{{ strtolower($app->tracking_number . ' ' . $app->service_name . ' ' . $app->status) }}'.includes(appSearch.toLowerCase())"
                                        class="hover:bg-slate-50/80 transition"
                                    >
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-slate-100 text-blue-700 border border-slate-200 tracking-wider">
                                                {{ $app->tracking_number }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-900">{{ $app->service_name }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">Applicant: {{ $app->applicant_name }}</div>
                                            @if($app->admin_notes)
                                                <div class="mt-1 text-[11px] text-blue-700 bg-blue-50/80 rounded px-2 py-0.5 border border-blue-100 inline-block">
                                                    💬 <strong>Consultant Note:</strong> {{ $app->admin_notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600 tabular-nums">
                                            {{ $app->created_at->format('d M Y') }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-800 tabular-nums">
                                                PKR {{ number_format($app->fee_amount, 0) }}
                                            </div>
                                            @if($app->payment_status === 'paid')
                                                <span class="inline-flex items-center text-[10px] text-emerald-700 font-bold">
                                                    ✓ Paid
                                                </span>
                                            @else
                                                <span class="inline-flex items-center text-[10px] text-amber-600 font-semibold">
                                                    ⏳ Unpaid
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($app->status === 'submitted')
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                                    Submitted
                                                </span>
                                            @elseif($app->status === 'in_review')
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                                    In Review
                                                </span>
                                            @elseif($app->status === 'completed')
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                    Completed ✓
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                                    {{ ucfirst($app->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($app->delivered_file_path)
                                                <a
                                                    href="{{ route('services.download', $app->id) }}"
                                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition"
                                                    title="Download issued certificate / document"
                                                >
                                                    <span>📥</span>
                                                    <span>Download Certificate</span>
                                                </a>
                                                @if($app->delivered_reference_no)
                                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                                        Ref: {{ $app->delivered_reference_no }}
                                                    </div>
                                                @endif
                                            @elseif($app->status === 'completed')
                                                <span class="text-xs text-emerald-700 font-semibold">Processed</span>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">Processing in IRIS/SECP</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a
                                                href="{{ route('track', ['query' => $app->tracking_number]) }}"
                                                class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
                                            >
                                                <span>Track Live</span>
                                                <span class="text-[10px]">&rarr;</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-slate-400 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <span class="text-2xl block mb-1">📑</span>
                        <p class="text-xs font-semibold text-slate-600">Abhi tak koi corporate application submit nahi hui</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">NTN, Sales Tax, SECP Company, ya Trademark apply karne ke liye 'Apply New' button dabayein.</p>
                        <a
                            href="{{ route('services.index') }}"
                            class="inline-block mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs"
                        >
                            Explore Business Services &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Other Services Available -->
            <x-ui.card title="Doosri Corporate &amp; FBR Services" description="NTN, Sales Tax, SECP Company, aur Trademark registration services">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach(array_slice(\App\Models\ServiceRequest::getCatalog(), 2, 4) as $service)
                        <div class="p-4 rounded-xl border border-slate-200 bg-white hover:border-blue-300 hover:shadow-xs transition flex flex-col justify-between">
                            <div>
                                <span class="text-2xl">{{ $service['icon'] }}</span>
                                <h4 class="text-xs font-semibold text-slate-800 mt-2">{{ $service['name'] ?? $service['heading'] }}</h4>
                                <span class="text-[11px] text-slate-500 font-mono tabular-nums block mt-1">Fee: PKR {{ number_format($service['fee'], 0) }}</span>
                            </div>
                            <div class="mt-3 pt-2 border-t border-slate-100">
                                <a href="{{ route('services.index') }}" class="text-[11px] font-medium text-blue-600 hover:text-blue-700">
                                    Apply Now &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
