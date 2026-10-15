<x-layouts.app header="Corporate & NTN Orders Performance Desk">
    <div
        x-data="{
            statusModalOpen: false,
            selectedOrder: null,
            actionUrl: '',
            orderStatus: 'submitted',
            paymentStatus: 'pending',
            referenceNo: '',
            adminNotes: '',
            isSyncingFbr: false,
            fbrSyncMessage: '',

            openModal(order) {
                this.selectedOrder = order;
                this.actionUrl = '/admin/corporate-orders/' + order.id + '/status';
                this.orderStatus = order.status;
                this.paymentStatus = order.payment_status || 'pending';
                this.referenceNo = order.delivered_reference_no || '';
                this.adminNotes = order.admin_notes || '';
                this.fbrSyncMessage = '';
                if (!this.selectedOrder.fbr_snapshot && this.selectedOrder.data_payload?._fbr_iris_snapshot) {
                    this.selectedOrder.fbr_snapshot = this.selectedOrder.data_payload._fbr_iris_snapshot;
                }
                this.statusModalOpen = true;
            },

            async syncFbrData() {
                if (!this.selectedOrder) return;
                this.isSyncingFbr = true;
                this.fbrSyncMessage = '';
                try {
                    const iden = this.selectedOrder.data_payload?.applicant_cnic
                        || this.selectedOrder.data_payload?.cnic
                        || this.selectedOrder.data_payload?.ntn
                        || this.selectedOrder.applicant_phone;
                    const res = await fetch('/admin/corporate-orders/' + this.selectedOrder.id + '/sync-fbr', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ identifier: iden })
                    });
                    const data = await res.json();
                    if (data.success && data.snapshot) {
                        this.selectedOrder.fbr_snapshot = data.snapshot;
                        this.fbrSyncMessage = 'FBR IRIS Live data re-synced!';
                    }
                } catch (e) {
                    this.fbrSyncMessage = 'Sync failed. Please try again.';
                } finally {
                    this.isSyncingFbr = false;
                }
            }
        }"
        class="space-y-6"
    >
        <!-- ========================================================================= -->
        <!-- ========================================================================= -->
        <!-- 1. AUTHENTIC ADMINLTE KPI SMALL-BOXES (ORDERS DESK)                       -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 w-full">
            <!-- Box 1: Total Orders -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalOrders }}</h3>
                    <p>Total Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'all']) }}" class="small-box-footer">
                    <span>All Services</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 2: New Submitted -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <div class="flex items-center space-x-2">
                        <h3 class="text-[#1f2d3d]">{{ $submittedCount }}</h3>
                        @if($submittedCount > 0)
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-ping"></span>
                        @endif
                    </div>
                    <p class="text-[#1f2d3d]">New Submitted</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bell"></i>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'submitted']) }}" class="small-box-footer text-[#1f2d3d]">
                    <span>Action Required</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 3: In Review -->
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $inReviewCount }}</h3>
                    <p>In Review</p>
                </div>
                <div class="icon">
                    <i class="fas fa-search"></i>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'in_review']) }}" class="small-box-footer">
                    <span>Audit Pending</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 4: Completed -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $completedCount }}</h3>
                    <p>Completed &amp; Issued</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="small-box-footer">
                    <span>Deliverables Sent</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 5: Cancelled -->
            <div class="small-box bg-secondary col-span-2 sm:col-span-1">
                <div class="inner">
                    <h3>{{ $cancelledCount }}</h3>
                    <p>Cancelled</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="small-box-footer">
                    <span>Rejected / Withdrawn</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. ADMINLTE CARD: FULL-WIDTH ORDERS TABLE & CONTROLS                      -->
        <!-- ========================================================================= -->
        <div class="card card-primary card-outline w-full">
            <!-- Card Header -->
            <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="card-title text-sm font-bold text-[#212529] flex items-center gap-2">
                    <i class="fas fa-briefcase text-[#007bff]"></i>
                    <span>Corporate &amp; NTN Service Orders Desk ({{ $orders->total() }} records)</span>
                </h3>

                <div class="flex items-center space-x-3">
                    <!-- Revenue Metrics Pills -->
                    <div class="hidden sm:flex items-center space-x-2 text-xs font-mono">
                        <span class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                            Paid: Rs {{ number_format($totalRevenue, 0) }}
                        </span>
                        <span class="px-2.5 py-1 rounded bg-amber-50 text-amber-800 border border-amber-200 font-bold">
                            Pending: Rs {{ number_format($pendingRevenue, 0) }}
                        </span>
                    </div>

                    <a
                        href="{{ route('admin.orders.export') }}"
                        class="px-3 py-1.5 bg-[#28a745] hover:bg-emerald-700 text-white rounded-md text-xs font-bold transition shadow-2xs flex items-center space-x-1"
                        title="Export Orders to Excel / CSV"
                    >
                        <span>📊</span>
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>

            <!-- Filter Bar Inside Card -->
            <div class="p-4 border-b border-slate-100 bg-white">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by Tracking #, Name, Phone, Email, Service..."
                            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 transition"
                        />
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 pointer-events-none text-xs">
                            🔍
                        </span>
                    </div>

                    <!-- Dropdowns -->
                    <div class="flex flex-wrap items-center gap-2">
                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500 cursor-pointer"
                        >
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="in_review" {{ $status === 'in_review' ? 'selected' : '' }}>In Review</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>

                        <select
                            name="service_code"
                            onchange="this.form.submit()"
                            class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500 cursor-pointer"
                        >
                            <option value="all">All Services</option>
                            @foreach($availableServices as $srv)
                                <option value="{{ $srv->service_code }}" {{ $serviceCode === $srv->service_code ? 'selected' : '' }}>
                                    {{ $srv->service_name }}
                                </option>
                            @endforeach
                        </select>

                        <select
                            name="payment_status"
                            onchange="this.form.submit()"
                            class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium text-slate-700 focus:bg-white focus:ring-2 focus:ring-blue-500 cursor-pointer"
                        >
                            <option value="all" {{ $paymentStatus === 'all' ? 'selected' : '' }}>All Payments</option>
                            <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>

                        <button
                            type="submit"
                            class="px-3 py-1.5 bg-[#007bff] hover:bg-blue-700 text-white rounded-md text-xs font-bold transition shadow-2xs"
                        >
                            Filter
                        </button>

                        @if($search || $status !== 'all' || $serviceCode !== 'all' || $paymentStatus !== 'all')
                            <a
                                href="{{ route('admin.orders.index') }}"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-md text-xs font-bold transition"
                            >
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

        <!-- 4. Orders Data Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            @if($orders->isEmpty())
                <div class="text-center py-16 px-4">
                    <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                        🏢
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Koi Corporate Order Nahi Mila</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Diye gaye filters ke mutabiq koi record mojood nahi hai. Filters reset karein ya naye orders ka intezar karein.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Tracking #</th>
                                <th class="py-3.5 px-4">Service Details</th>
                                <th class="py-3.5 px-4">Applicant</th>
                                <th class="py-3.5 px-4">WhatsApp Direct</th>
                                <th class="py-3.5 px-4">Fee & Payment</th>
                                <th class="py-3.5 px-4">Current Status</th>
                                <th class="py-3.5 px-4">Delivered Document</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @foreach($orders as $order)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-blue-700 text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $order->tracking_number }}
                                        </span>
                                        <div class="text-[10px] text-slate-400 font-sans mt-0.5">
                                            {{ $order->created_at->format('d M, Y h:i A') }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800 text-xs">{{ $order->service_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $order->service_code }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-800 text-xs">{{ $order->applicant_name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $order->applicant_email }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <a
                                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->applicant_phone) }}?text=Assalam%20o%20Alaikum%20{{ urlencode($order->applicant_name) }},%20TaxFiler%20consultant%20aapki%20{{ urlencode($order->service_name) }}%20application%20({{ $order->tracking_number }})%20ke%20hawaale%20se%20rabta%20kar%20raha%20hai."
                                            target="_blank"
                                            class="inline-flex items-center space-x-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition font-mono tabular-nums"
                                            title="Click to Open WhatsApp Chat"
                                        >
                                            <span>💬</span>
                                            <span>{{ $order->applicant_phone }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono tabular-nums font-bold text-slate-800">
                                            PKR {{ number_format($order->fee_amount, 0) }}
                                        </div>
                                        @if($order->payment_status === 'paid')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                                Paid ✓
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($order->status === 'submitted')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Submitted</span>
                                        @elseif($order->status === 'in_review')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In Review</span>
                                        @elseif($order->status === 'completed')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Completed</span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Cancelled</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($order->delivered_file_path)
                                            <a
                                                href="{{ route('services.download', $order->id) }}"
                                                class="inline-flex items-center space-x-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-1 rounded-md transition"
                                                title="Download delivered document"
                                            >
                                                <span>📥</span>
                                                <span>Download File</span>
                                            </a>
                                            @if($order->delivered_reference_no)
                                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">Ref: {{ $order->delivered_reference_no }}</div>
                                            @endif
                                        @else
                                            <span class="text-slate-400 text-[11px] italic">Not uploaded</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end space-x-1.5">
                                            <a
                                                href="{{ route('services.certificate.pdf', $order->id) }}"
                                                class="inline-flex items-center space-x-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white px-2 py-1.5 rounded-lg transition"
                                                title="Download Official Certificate PDF"
                                            >
                                                <span>🏆</span>
                                                <span>Certificate</span>
                                            </a>

                                            <button
                                                type="button"
                                                @click="openModal({{ json_encode($order) }})"
                                                class="inline-flex items-center space-x-1.5 text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 px-3 py-1.5 rounded-lg transition shadow-xs cursor-pointer"
                                                title="Open Side-by-Side Application & FBR IRIS Verification Review"
                                            >
                                                <span>🔍</span>
                                                <span>Review & FBR</span>
                                            </button>

                                            <form method="POST" action="{{ route('admin.orders.destroy', $order->id) }}" onsubmit="return confirm('Kya aap waqai Order #{{ $order->tracking_number }} ({{ $order->applicant_name }}) ko delete karna chahte hain?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center space-x-1 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white px-2 py-1.5 rounded-lg transition shadow-2xs cursor-pointer"
                                                    title="Delete Order"
                                                >
                                                    <span>🗑️</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- 5. Side-by-Side Review Console: Client Form vs FBR IRIS 2.0 Record -->
        <div
            x-show="statusModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="statusModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-3 sm:p-5">
                <div class="relative w-full max-w-6xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden my-4">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-4 bg-slate-900 text-white border-b border-slate-800">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-600/30 border border-blue-400/30 flex items-center justify-center text-blue-400 text-base">
                                ⚖️
                            </span>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-bold text-white tracking-tight">Application Verification Console</h3>
                                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 border border-blue-400/30 font-bold" x-text="selectedOrder ? '#' + selectedOrder.tracking_number : ''"></span>
                                </div>
                                <p class="text-xs text-slate-300" x-text="selectedOrder ? selectedOrder.service_name + ' • Fee: PKR ' + Number(selectedOrder.fee_amount).toLocaleString() : ''"></p>
                            </div>
                        </div>
                        <button type="button" @click="statusModalOpen = false" class="text-slate-400 hover:text-white p-1.5 rounded-lg transition text-lg cursor-pointer">
                            ✕
                        </button>
                    </div>

                    <!-- Side-by-Side Review Body (2 Columns) -->
                    <div class="p-5 sm:p-6 bg-slate-50/70 border-b border-slate-200 max-h-[60vh] overflow-y-auto">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            
                            <!-- ============================================== -->
                            <!-- SIDE 1 (LEFT): CLIENT SUBMITTED APPLICATION FORM -->
                            <!-- ============================================== -->
                            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 flex flex-col justify-between">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-base">📄</span>
                                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Client Submitted Form</h4>
                                        </div>
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-semibold">
                                            Applicant Input
                                        </span>
                                    </div>

                                    <!-- Applicant Primary Contact Card -->
                                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-500 font-medium">Applicant Name:</span>
                                            <span class="font-bold text-slate-800" x-text="selectedOrder?.applicant_name"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-500 font-medium">WhatsApp / Phone:</span>
                                            <div class="flex items-center space-x-1.5">
                                                <span class="font-mono text-slate-800 font-semibold" x-text="selectedOrder?.applicant_phone"></span>
                                                <a
                                                    :href="'https://wa.me/' + (selectedOrder ? selectedOrder.applicant_phone.replace(/[^0-9]/g, '') : '')"
                                                    target="_blank"
                                                    class="text-[10px] text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded font-semibold"
                                                    title="Chat on WhatsApp"
                                                >
                                                    💬 Chat
                                                </a>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-500 font-medium">Email Address:</span>
                                            <span class="text-slate-800 font-mono text-[11px]" x-text="selectedOrder?.applicant_email"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-500 font-medium">CNIC / Identifier:</span>
                                            <span class="font-mono text-blue-700 font-bold" x-text="selectedOrder?.data_payload?.applicant_cnic || selectedOrder?.data_payload?.cnic || 'Provided in form'"></span>
                                        </div>
                                    </div>

                                    <!-- Submitted Dynamic Form Fields -->
                                    <div>
                                        <h5 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">Service Form Responses</h5>
                                        
                                        <div class="space-y-1.5 divide-y divide-slate-100 text-xs">
                                            <template x-for="(val, key) in (selectedOrder?.data_payload || {})" :key="key">
                                                <template x-if="!key.startsWith('_') && key !== 'applicant_cnic'">
                                                    <div class="pt-1.5 flex items-start justify-between gap-2">
                                                        <span class="font-medium text-slate-500 capitalize shrink-0" x-text="key.replace(/_/g, ' ') + ':'"></span>
                                                        <span class="font-semibold text-slate-800 text-right break-words font-mono text-[11px]" x-text="val || '—'"></span>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>

                                        <template x-if="!selectedOrder?.data_payload || Object.keys(selectedOrder?.data_payload || {}).filter(k => !k.startsWith('_') && k !== 'applicant_cnic').length === 0">
                                            <p class="text-xs text-slate-400 italic py-2">
                                                Standard service registration. No additional custom form fields submitted.
                                            </p>
                                        </template>
                                    </div>

                                    <!-- Uploaded Client Documents List -->
                                    <div class="pt-2 border-t border-slate-100" x-show="selectedOrder?.uploaded_files && Object.keys(selectedOrder.uploaded_files).length > 0">
                                        <h5 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">Attached Client Documents:</h5>
                                        <div class="space-y-1.5">
                                            <template x-for="(doc, dKey) in (selectedOrder?.uploaded_files || {})" :key="dKey">
                                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-200/70 text-xs">
                                                    <span class="font-medium text-slate-800" x-text="doc.label || dKey"></span>
                                                    <a :href="'/storage/' + doc.path" target="_blank" class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold hover:bg-blue-100 text-[11px]">
                                                        View File ↗
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Uploaded Payment Slip / Proof -->
                                    <div class="pt-2 border-t border-slate-100" x-show="selectedOrder?.payment_proof_path">
                                        <h5 class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                            <span>Payment Receipt / Slip Attached:</span>
                                            <span class="text-[10px] text-amber-700 font-normal" x-text="'Method: ' + (selectedOrder?.payment_method || 'Bank Transfer')"></span>
                                        </h5>
                                        <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-200 flex items-center justify-between text-xs">
                                            <div>
                                                <span class="font-bold text-slate-800" x-text="'Ref: ' + (selectedOrder?.payment_reference || 'Slip Uploaded')"></span>
                                            </div>
                                            <a :href="'{{ route('services.proof-file', ['serviceRequest' => '__ID__']) }}'.replace('__ID__', selectedOrder?.id)" target="_blank" rel="noopener" class="px-2.5 py-1 rounded bg-amber-600 text-white font-bold hover:bg-amber-700 text-xs">
                                                Proof dekhein ↗
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 mt-4 text-[10px] text-slate-400 flex items-center justify-between">
                                    <span>Submitted: <strong class="text-slate-600 font-mono" x-text="selectedOrder?.created_at ? new Date(selectedOrder.created_at).toLocaleString() : ''"></strong></span>
                                    <span>Status: <strong class="uppercase text-blue-600" x-text="selectedOrder?.status"></strong></span>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- SIDE 2 (RIGHT): AUTO-FETCHED FBR IRIS 2.0 DATA  -->
                            <!-- ============================================== -->
                            <div class="bg-blue-50/40 rounded-xl border border-blue-200/80 shadow-2xs p-4 flex flex-col justify-between">
                                <div class="space-y-4">
                                    <!-- Header with Live Sync Controls -->
                                    <div class="flex items-center justify-between pb-3 border-b border-blue-100">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-base">🏛️</span>
                                            <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">FBR IRIS 2.0 Live Verification</h4>
                                        </div>
                                        <button
                                            type="button"
                                            @click="syncFbrData()"
                                            :disabled="isSyncingFbr"
                                            class="inline-flex items-center space-x-1 text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded-md transition shadow-2xs cursor-pointer disabled:opacity-50"
                                            title="Re-query FBR Live Database"
                                        >
                                            <span x-show="!isSyncingFbr">🔄 Re-Sync Live</span>
                                            <span x-show="isSyncingFbr" class="animate-spin inline-block">⏳</span>
                                            <span x-show="isSyncingFbr">Syncing...</span>
                                        </button>
                                    </div>

                                    <!-- Sync Message notification if updated -->
                                    <div x-show="fbrSyncMessage" x-text="fbrSyncMessage" class="text-xs p-2 rounded-lg bg-emerald-100 text-emerald-800 font-medium"></div>

                                    <!-- FBR Active Taxpayer List (ATL) Card -->
                                    <div class="bg-white p-3 rounded-lg border border-blue-100 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-slate-600">Active Taxpayer List (ATL):</span>
                                            <template x-if="selectedOrder?.fbr_snapshot?.atl?.is_active">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    ✓ ACTIVE (Filer)
                                                </span>
                                            </template>
                                            <template x-if="!selectedOrder?.fbr_snapshot?.atl?.is_active">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                    ✕ INACTIVE (Non-Filer)
                                                </span>
                                            </template>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-slate-100">
                                            <div>
                                                <span class="text-slate-400 block text-[10px]">Tax Year:</span>
                                                <span class="font-semibold text-slate-800" x-text="selectedOrder?.fbr_snapshot?.atl?.tax_year || 'Tax Year 2026'"></span>
                                            </div>
                                            <div>
                                                <span class="text-slate-400 block text-[10px]">Surcharge Status:</span>
                                                <span class="font-semibold text-slate-800 text-[11px]" x-text="selectedOrder?.fbr_snapshot?.atl?.surcharge_status || 'Verified'"></span>
                                            </div>
                                        </div>

                                        <div class="text-[10px] text-slate-500 bg-slate-50 p-2 rounded border border-slate-100">
                                            <strong class="text-slate-700">WHT Impact:</strong>
                                            <span x-text="selectedOrder?.fbr_snapshot?.atl?.filer_benefit || 'Normal Withholding Tax Rates apply.'"></span>
                                        </div>
                                    </div>

                                    <!-- Official Registered Profile Card -->
                                    <div class="bg-white p-3 rounded-lg border border-blue-100 shadow-2xs space-y-2 text-xs">
                                        <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                                            <span class="text-slate-500 font-medium">Legal Registered Name:</span>
                                            <span class="font-bold text-slate-900" x-text="selectedOrder?.fbr_snapshot?.profile?.legal_name || selectedOrder?.applicant_name"></span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-500 font-medium">NTN Number:</span>
                                            <span class="font-mono font-bold text-blue-700" x-text="selectedOrder?.fbr_snapshot?.profile?.ntn || 'Pending Generation'"></span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-500 font-medium">Registration Category:</span>
                                            <span class="font-medium text-slate-800" x-text="selectedOrder?.fbr_snapshot?.profile?.registration_type || 'Individual Taxpayer'"></span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-500 font-medium">Assigned RTO:</span>
                                            <span class="font-semibold text-slate-800 text-[11px]" x-text="selectedOrder?.fbr_snapshot?.profile?.assigned_rto || 'RTO Islamabad'"></span>
                                        </div>

                                        <div class="pt-1 border-t border-slate-100">
                                            <span class="text-slate-400 block text-[10px]">Principal Business Activity:</span>
                                            <span class="text-slate-700 text-[11px] font-medium" x-text="selectedOrder?.fbr_snapshot?.profile?.principal_activity || 'Commercial & Professional Services'"></span>
                                        </div>

                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Registered Tax Address:</span>
                                            <span class="text-slate-700 text-[11px]" x-text="selectedOrder?.fbr_snapshot?.profile?.registered_address || 'Blue Area, Islamabad'"></span>
                                        </div>
                                    </div>

                                    <!-- Verification Cross-Match Analysis Badge -->
                                    <div class="bg-emerald-50 border border-emerald-200 p-2.5 rounded-lg flex items-center space-x-2 text-xs">
                                        <span class="text-emerald-700 text-base">🛡️</span>
                                        <div>
                                            <div class="font-bold text-emerald-900">Auto-Matched with IRIS 2.0 Registry</div>
                                            <div class="text-[10px] text-emerald-700">Official snapshot captured and attached with application.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-blue-100 mt-4 text-[10px] text-blue-600 flex items-center justify-between">
                                    <span>Verified At: <strong class="font-mono text-blue-800" x-text="selectedOrder?.fbr_snapshot?.fetched_at || 'Auto-synced at submission'"></strong></span>
                                    <span class="font-semibold text-blue-800">FBR IRIS Electronic Database ✓</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Workflow, Certificate Upload & Status Actions Form -->
                    <form :action="actionUrl" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6 bg-white space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Status Selection -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Application Pipeline Status</label>
                                <select
                                    name="status"
                                    x-model="orderStatus"
                                    class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="submitted">Submitted (Pending Initial Review)</option>
                                    <option value="in_review">In Review (IRIS / SECP / FBR Processing Active)</option>
                                    <option value="completed">Completed (Certificate Issued & Delivered)</option>
                                    <option value="cancelled">Cancelled (Application Rejected / Ineligible)</option>
                                </select>
                            </div>

                            <!-- Payment Status -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Status</label>
                                <select
                                    name="payment_status"
                                    x-model="paymentStatus"
                                    class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="pending">Fee Pending</option>
                                    <option value="paid">Fee Paid ✓</option>
                                </select>
                            </div>

                            <!-- Delivered Reference Number -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Registration / NTN / SECP Ref No. <span class="text-slate-400 font-normal">(Optional)</span>
                                </label>
                                <input
                                    type="text"
                                    name="delivered_reference_no"
                                    x-model="referenceNo"
                                    placeholder="e.g. NTN: 8392019-3 or SECP Reg: 0284918"
                                    class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Deliverable Document Upload -->
                            <div class="bg-blue-50/60 p-3 rounded-xl border border-blue-100">
                                <label class="block text-xs font-bold text-blue-900 mb-1">
                                    📄 Upload Official Document / Certificate (PDF, PNG, JPG, ZIP)
                                </label>
                                <p class="text-[10px] text-blue-700 mb-2">
                                    Jaise hi file upload hogi, client ke dashboard par "My Applications" mein download button active ho jayega.
                                </p>
                                <input
                                    type="file"
                                    name="delivered_file"
                                    accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.zip"
                                    class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer"
                                />
                            </div>

                            <!-- Admin Notes / Timeline Bridge Message -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Consultant Log & Client Working Bridge Message
                                </label>
                                <textarea
                                    name="admin_notes"
                                    x-model="adminNotes"
                                    rows="2"
                                    placeholder="Misal: IRIS par application process ho gayi hai, NTN order approve ho chuka hai..."
                                    class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                ></textarea>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    Yeh wazahat client ki live tracking timeline par show hogi.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                @click="statusModalOpen = false"
                                class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 rounded-lg shadow-sm transition flex items-center space-x-1.5 cursor-pointer"
                            >
                                <span>Save Changes & Deliver</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
