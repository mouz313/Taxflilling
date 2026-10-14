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
        class="w-full space-y-4"
    >
        <!-- 1. Authentic AdminLTE Small-Boxes Row -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Box 1: Total Leads -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalCallbacks }}</h3>
                    <p>Total Inquiries</p>
                </div>
                <div class="icon">
                    <i class="fas fa-headset"></i>
                </div>
                <a href="{{ route('admin.callbacks.index', ['status' => 'all']) }}" class="small-box-footer">
                    <span>All Leads</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 2: Pending Calls -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <div class="flex items-center space-x-2">
                        <h3>{{ $pendingCount }}</h3>
                        @if($pendingCount > 0)
                            <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                        @endif
                    </div>
                    <p>Pending Calls</p>
                </div>
                <div class="icon">
                    <i class="fas fa-phone-volume"></i>
                </div>
                <a href="{{ route('admin.callbacks.index', ['status' => 'pending']) }}" class="small-box-footer">
                    <span>Urgent Attention</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 3: Contacted -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $contactedCount }}</h3>
                    <p>In Discussion</p>
                </div>
                <div class="icon">
                    <i class="fas fa-comments"></i>
                </div>
                <a href="{{ route('admin.callbacks.index', ['status' => 'contacted']) }}" class="small-box-footer">
                    <span>Follow-up</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 4: Converted -->
            <div class="small-box bg-success">
                <div class="inner">
                    <div class="flex items-baseline space-x-2">
                        <h3>{{ $convertedCount }}</h3>
                        <span class="text-xs bg-black/20 px-1.5 py-0.5 rounded font-mono font-bold">{{ $conversionRate }}%</span>
                    </div>
                    <p>Converted Filers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('admin.callbacks.index', ['status' => 'converted']) }}" class="small-box-footer">
                    <span>Active Clients</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 5: Closed -->
            <div class="small-box bg-secondary col-span-2 sm:col-span-1">
                <div class="inner">
                    <h3>{{ $closedCount }}</h3>
                    <p>Closed Leads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-archive"></i>
                </div>
                <a href="{{ route('admin.callbacks.index', ['status' => 'closed']) }}" class="small-box-footer">
                    <span>Archived</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- 2. Main AdminLTE Card: Data Table & Filters -->
        <div class="card card-outline card-primary w-full">
            <div class="card-header flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <h3 class="card-title text-sm font-bold text-[#212529] flex items-center gap-2">
                    <i class="fas fa-phone-alt text-[#007bff]"></i>
                    <span>Direct Call Requests &amp; Lead Inquiries Desk</span>
                </h3>

                <!-- Card Tools -->
                <div class="card-tools flex flex-wrap items-center gap-2">
                    <!-- Status Filter Buttons -->
                    <div class="inline-flex rounded-md shadow-2xs" role="group">
                        <a
                            href="{{ route('admin.callbacks.index', ['status' => 'all', 'search' => $search]) }}"
                            class="btn btn-xs {{ $status === 'all' ? 'btn-primary font-bold' : 'btn-default' }}"
                        >
                            All ({{ $totalCallbacks }})
                        </a>
                        <a
                            href="{{ route('admin.callbacks.index', ['status' => 'pending', 'search' => $search]) }}"
                            class="btn btn-xs {{ $status === 'pending' ? 'btn-danger font-bold' : 'btn-default' }}"
                        >
                            Pending ({{ $pendingCount }})
                        </a>
                        <a
                            href="{{ route('admin.callbacks.index', ['status' => 'contacted', 'search' => $search]) }}"
                            class="btn btn-xs {{ $status === 'contacted' ? 'btn-warning font-bold' : 'btn-default' }}"
                        >
                            Contacted
                        </a>
                        <a
                            href="{{ route('admin.callbacks.index', ['status' => 'converted', 'search' => $search]) }}"
                            class="btn btn-xs {{ $status === 'converted' ? 'btn-success font-bold' : 'btn-default' }}"
                        >
                            Converted
                        </a>
                        <a
                            href="{{ route('admin.callbacks.index', ['status' => 'closed', 'search' => $search]) }}"
                            class="btn btn-xs {{ $status === 'closed' ? 'btn-secondary font-bold' : 'btn-default' }}"
                        >
                            Closed
                        </a>
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.callbacks.index') }}" class="flex items-center gap-1">
                        @if($status !== 'all')
                            <input type="hidden" name="status" value="{{ $status }}">
                        @endif
                        <div class="relative">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search client name, phone..."
                                class="form-control form-control-sm text-xs w-44 sm:w-56 pl-7"
                            />
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-[#adb5bd] text-xs"></i>
                        </div>
                        <button type="submit" class="btn btn-xs btn-primary font-semibold">
                            Filter
                        </button>
                        @if(!empty($search))
                            <a href="{{ route('admin.callbacks.index', ['status' => $status]) }}" class="text-xs text-[#dc3545] hover:underline px-1">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                @if($callbacks->isEmpty())
                    <div class="text-center py-16 px-4">
                        <div class="w-12 h-12 bg-[#f8f9fa] text-[#adb5bd] rounded-full flex items-center justify-center mx-auto mb-2 text-xl">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h4 class="text-sm font-bold text-[#495057]">Koi Call Request Nahi Aayi</h4>
                        <p class="text-xs text-[#6c757d] mt-1 max-w-sm mx-auto">
                            Jab prospective clients website par call request submit karenge to live leads yahan display hongi.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-hover table-striped w-full text-xs text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th>Client Name &amp; Time</th>
                                    <th>Instant WhatsApp Contact</th>
                                    <th>Service Interested</th>
                                    <th>Preferred Time</th>
                                    <th>Inquiry / Message</th>
                                    <th class="text-center">Status</th>
                                    <th>Consultant Log</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($callbacks as $cb)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-[#212529]">{{ $cb->name }}</div>
                                            <div class="text-[10px] text-[#6c757d] font-normal">
                                                <i class="far fa-clock mr-1"></i>{{ $cb->created_at->format('d M, Y h:i A') }}
                                            </div>
                                        </td>
                                        <td>
                                            <a
                                                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cb->phone) }}?text=Assalam%20o%20Alaikum%20{{ urlencode($cb->name) }},%20TaxFiler%20consultancy%20ki%20taraf%20se%20rabta%20kar%20rahe%20hain.%20Aapne%20call%20ki%20request%20ki%20thi."
                                                target="_blank"
                                                class="btn btn-xs btn-success font-semibold font-mono"
                                                title="Open WhatsApp Chat"
                                            >
                                                <i class="fab fa-whatsapp mr-1"></i>
                                                <span>{{ $cb->phone }}</span>
                                            </a>
                                            @if($cb->email)
                                                <div class="text-[10px] text-[#6c757d] mt-0.5">{{ $cb->email }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info font-medium">
                                                {{ $cb->service_name ?? 'General Consultation' }}
                                            </span>
                                        </td>
                                        <td class="text-[#495057]">
                                            {{ $cb->preferred_time ?? 'Anytime' }}
                                        </td>
                                        <td class="max-w-xs truncate text-[#495057]" title="{{ $cb->notes ?? $cb->message }}">
                                            {{ $cb->notes ?? $cb->message ?? '-' }}
                                        </td>
                                        <td class="text-center">
                                            @if($cb->status === 'pending')
                                                <span class="badge badge-danger">Pending Call</span>
                                            @elseif($cb->status === 'contacted')
                                                <span class="badge badge-warning">Contacted</span>
                                            @elseif($cb->status === 'converted')
                                                <span class="badge badge-success">Converted ✓</span>
                                            @elseif($cb->status === 'closed')
                                                <span class="badge badge-secondary">Closed</span>
                                            @else
                                                <span class="badge badge-info">{{ ucfirst($cb->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="max-w-xs text-[#6c757d] text-[11px] italic truncate">
                                            {{ $cb->admin_response_notes ?? 'Koi remark nahi likha gaya' }}
                                        </td>
                                        <td class="text-right">
                                            <button
                                                type="button"
                                                @click="openModal({{ json_encode($cb) }})"
                                                class="btn btn-xs btn-primary font-semibold"
                                            >
                                                <i class="fas fa-edit mr-1"></i>
                                                <span>Log / Update</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if($callbacks->hasPages())
                <div class="card-footer clearfix bg-white border-t border-[#dee2e6]">
                    {{ $callbacks->links() }}
                </div>
            @endif
        </div>

        <!-- 3. Status Transition Modal (AdminLTE Dialog Style) -->
        <div
            x-show="statusModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-black/50 transition-opacity" @click="statusModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-white rounded shadow-xl border border-[#dee2e6] overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                    <!-- Modal Header -->
                    <div class="bg-[#007bff] text-white px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-phone"></i>
                            <h4 class="text-sm font-bold m-0">Update Lead &amp; Call Status</h4>
                        </div>
                        <button type="button" @click="statusModalOpen = false" class="text-white hover:text-white/80 text-lg font-bold">
                            &times;
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form :action="actionUrl" method="POST" class="p-4 space-y-3">
                        @csrf

                        <div class="p-2.5 bg-[#f8f9fa] border border-[#dee2e6] rounded text-xs text-[#495057]">
                            <span class="font-bold block" x-text="selectedCallback ? selectedCallback.name : ''"></span>
                            <span class="text-[#6c757d] font-mono" x-text="selectedCallback ? selectedCallback.phone : ''"></span>
                        </div>

                        <!-- Status Selection -->
                        <div>
                            <label class="block text-xs font-bold text-[#495057] mb-1">Inquiry Status</label>
                            <select
                                name="status"
                                x-model="callbackStatus"
                                class="form-control text-xs"
                            >
                                <option value="pending">Pending (Call Karna Baaqi Hai)</option>
                                <option value="contacted">Contacted (Call Ki Gayi, Follow-up Chalu Hai)</option>
                                <option value="converted">Converted (Client Ban Gaya - Tax Return/NTN Start Ho Gaya)</option>
                                <option value="closed">Closed (Not Interested / Resolved)</option>
                            </select>
                        </div>

                        <!-- Response Notes -->
                        <div>
                            <label class="block text-xs font-bold text-[#495057] mb-1">
                                Consultant Discussion Notes &amp; Remarks
                            </label>
                            <textarea
                                name="admin_response_notes"
                                x-model="responseNotes"
                                rows="3"
                                placeholder="Misal: Client se WhatsApp par baat hui, NTN documents mangwa liye hain..."
                                class="form-control text-xs"
                            ></textarea>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-[#dee2e6]">
                            <button
                                type="button"
                                @click="statusModalOpen = false"
                                class="btn btn-default text-xs"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary text-xs font-bold"
                            >
                                <i class="fas fa-save mr-1"></i> Save Call Log
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
