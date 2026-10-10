<x-layouts.app header="Call Requests & Lead Inquiries Desk">
    <div
        x-data="{
            statusModalOpen: false,
            selectedCallback: null,
            actionUrl: '',
            callbackStatus: 'pending',
            responseNotes: '',

            openModal(cb) {
                this.selectedCallback = cb;
                this.actionUrl = '/admin/call-requests/' + cb.id + '/status';
                this.callbackStatus = cb.status;
                this.responseNotes = cb.admin_response_notes || '';
                this.statusModalOpen = true;
            }
        }"
        class="space-y-6"
    >
        <!-- 1. Executive Performance Bar & Identity -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 text-white shadow-md flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-400/30">
                        📞 Lead Inquiries Desk
                    </span>
                    <span class="text-xs text-slate-400 font-mono">/admin/call-requests</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Call Requests & Client Inquiries Performance Desk</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Website visitors aur prospective clients jinoh ne call back request ki hai, unhe foran attend karein aur client conversion monitor karein.
                </p>
            </div>

            <!-- Conversion Rate Card -->
            <div class="bg-slate-900/80 p-3.5 rounded-xl border border-slate-800 shrink-0 text-center sm:text-right">
                <div class="text-[10px] uppercase font-bold text-slate-400">Conversion Rate</div>
                <div class="text-2xl font-bold text-emerald-400 font-mono tabular-nums">{{ $conversionRate }}%</div>
                <div class="text-[10px] text-slate-400">Inquiries Converted to Clients</div>
            </div>
        </div>

        <!-- 2. KPI Performance Stat Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <a href="{{ route('admin.callbacks.index', ['status' => 'all']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-400 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Inquiries</div>
                <div class="text-2xl font-bold text-slate-800 mt-1 font-mono tabular-nums">{{ $totalCallbacks }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">All received requests</div>
            </a>

            <a href="{{ route('admin.callbacks.index', ['status' => 'pending']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-rose-400 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Pending Calls</span>
                    @if($pendingCount > 0)
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </div>
                <div class="text-2xl font-bold text-rose-600 mt-1 font-mono tabular-nums">{{ $pendingCount }}</div>
                <div class="text-[11px] text-rose-600/80 mt-0.5 font-medium">Urgent attention needed</div>
            </a>

            <a href="{{ route('admin.callbacks.index', ['status' => 'contacted']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-400 transition">
                <div class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Contacted</div>
                <div class="text-2xl font-bold text-blue-600 mt-1 font-mono tabular-nums">{{ $contactedCount }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Called / Discussion in progress</div>
            </a>

            <a href="{{ route('admin.callbacks.index', ['status' => 'converted']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-emerald-400 transition">
                <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Converted Clients</div>
                <div class="text-2xl font-bold text-emerald-600 mt-1 font-mono tabular-nums">{{ $convertedCount }}</div>
                <div class="text-[11px] text-emerald-600/80 mt-0.5 font-medium">Became active filers ✓</div>
            </a>

            <a href="{{ route('admin.callbacks.index', ['status' => 'closed']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-400 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Closed / Not Interested</div>
                <div class="text-2xl font-bold text-slate-600 mt-1 font-mono tabular-nums">{{ $closedCount }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Resolved inquiries</div>
            </a>
        </div>

        <!-- 3. Filter & Search Controls -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.callbacks.index') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by Name, Phone, Email, Service..."
                        class="w-full text-xs rounded-lg border border-slate-200 pl-8 pr-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
                </div>

                <!-- Status Filter Chips -->
                <div class="flex items-center space-x-1.5 bg-slate-50 p-1 rounded-lg border border-slate-200">
                    <a
                        href="{{ route('admin.callbacks.index', ['status' => 'all', 'search' => $search]) }}"
                        class="px-2.5 py-1 text-xs rounded-md transition {{ $status === 'all' ? 'bg-white text-slate-800 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium' }}"
                    >
                        All ({{ $totalCallbacks }})
                    </a>
                    <a
                        href="{{ route('admin.callbacks.index', ['status' => 'pending', 'search' => $search]) }}"
                        class="px-2.5 py-1 text-xs rounded-md transition {{ $status === 'pending' ? 'bg-white text-rose-700 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium' }}"
                    >
                        Pending ({{ $pendingCount }})
                    </a>
                    <a
                        href="{{ route('admin.callbacks.index', ['status' => 'contacted', 'search' => $search]) }}"
                        class="px-2.5 py-1 text-xs rounded-md transition {{ $status === 'contacted' ? 'bg-white text-blue-700 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium' }}"
                    >
                        Contacted
                    </a>
                    <a
                        href="{{ route('admin.callbacks.index', ['status' => 'converted', 'search' => $search]) }}"
                        class="px-2.5 py-1 text-xs rounded-md transition {{ $status === 'converted' ? 'bg-white text-emerald-700 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium' }}"
                    >
                        Converted
                    </a>
                    <a
                        href="{{ route('admin.callbacks.index', ['status' => 'closed', 'search' => $search]) }}"
                        class="px-2.5 py-1 text-xs rounded-md transition {{ $status === 'closed' ? 'bg-white text-slate-700 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium' }}"
                    >
                        Closed
                    </a>
                </div>
            </form>
        </div>

        <!-- 4. Callbacks Data Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            @if($callbacks->isEmpty())
                <div class="text-center py-16 px-4">
                    <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                        📞
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Koi Call Request Nahi Aayi</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Jab website visitors call back request submit karenge to instant notification aur lead yahan nazar aayegi.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Client Name</th>
                                <th class="py-3.5 px-4">Instant Contact</th>
                                <th class="py-3.5 px-4">Service Interested</th>
                                <th class="py-3.5 px-4">Preferred Time</th>
                                <th class="py-3.5 px-4">Inquiry / Message</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Consultant Log</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @foreach($callbacks as $cb)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-800">
                                        {{ $cb->name }}
                                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">
                                            {{ $cb->created_at->format('d M, Y h:i A') }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <a
                                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cb->phone) }}?text=Assalam%20o%20Alaikum%20{{ urlencode($cb->name) }},%20TaxFiler%20consultancy%20ki%20taraf%20se%20rabta%20kar%20rahe%20hain.%20Aapne%20call%20ki%20request%20ki%20thi."
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg hover:bg-emerald-100 transition font-mono tabular-nums"
                                            title="Click to Open WhatsApp Chat"
                                        >
                                            <span>💬</span>
                                            <span>{{ $cb->phone }}</span>
                                        </a>
                                        @if($cb->email)
                                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $cb->email }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $cb->service_name ?? 'General Consultation' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-slate-600">
                                        {{ $cb->preferred_time ?? 'Anytime' }}
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs truncate text-slate-600" title="{{ $cb->notes ?? $cb->message }}">
                                        {{ $cb->notes ?? $cb->message ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($cb->status === 'pending')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 animate-pulse">Pending Call</span>
                                        @elseif($cb->status === 'contacted')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Contacted</span>
                                        @elseif($cb->status === 'converted')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Converted ✓</span>
                                        @elseif($cb->status === 'closed')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">Closed</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">{{ ucfirst($cb->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs text-slate-500 text-[11px] italic">
                                        {{ $cb->admin_response_notes ?? 'Koi remark nahi likha gaya' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <button
                                            type="button"
                                            @click="openModal({{ json_encode($cb) }})"
                                            class="inline-flex items-center space-x-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white px-2.5 py-1.5 rounded-lg transition"
                                        >
                                            <span>📝</span>
                                            <span>Update Call Log</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($callbacks->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $callbacks->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- 5. Status Transition Modal -->
        <div
            x-show="statusModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="statusModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 p-6 overflow-hidden">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Update Lead &amp; Call Status</h3>
                            <p class="text-xs text-slate-500" x-text="selectedCallback ? selectedCallback.name + ' • ' + selectedCallback.phone : ''"></p>
                        </div>
                        <button type="button" @click="statusModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            ✕
                        </button>
                    </div>

                    <form :action="actionUrl" method="POST" class="space-y-4">
                        @csrf

                        <!-- Status Selection -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Inquiry Status</label>
                            <select
                                name="status"
                                x-model="callbackStatus"
                                class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="pending">Pending (Call Karna Baaqi Hai)</option>
                                <option value="contacted">Contacted (Call Ki Gayi, Follow-up Chalu Hai)</option>
                                <option value="converted">Converted (Client Ban Gaya - Tax Return/NTN Start Ho Gaya)</option>
                                <option value="closed">Closed (Not Interested / Resolved)</option>
                            </select>
                        </div>

                        <!-- Response Notes -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Consultant Discussion Notes & Remarks
                            </label>
                            <textarea
                                name="admin_response_notes"
                                x-model="responseNotes"
                                rows="3"
                                placeholder="Misal: Client se WhatsApp par baat hui, NTN documents mangwa liye hain..."
                                class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                @click="statusModalOpen = false"
                                class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2 text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 rounded-lg shadow-sm transition"
                            >
                                Save Call Log
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
