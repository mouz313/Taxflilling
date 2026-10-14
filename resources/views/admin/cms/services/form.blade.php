<x-layouts.app>
    <x-slot:header>
        CMS &bull; {{ $service->exists ? 'Edit Service' : 'Add New Service' }}
    </x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumbs / Top Bar -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <a href="{{ route('admin.cms.services.index') }}" class="hover:text-blue-600 font-semibold transition">&larr; Back to Services Catalog</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-bold">{{ $service->exists ? 'Edit: ' . $service->heading : 'Create New Service' }}</span>
            </div>
            @if($service->exists)
                <a
                    href="{{ route('services.index', ['tab' => $service->category]) }}"
                    target="_blank"
                    class="text-xs text-blue-600 hover:underline flex items-center space-x-1"
                >
                    <span>View in Live Marketplace</span>
                    <span class="text-[10px] font-mono">↗</span>
                </a>
            @endif
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-1">
                <strong class="font-bold block">Please correct the following errors:</strong>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $service->exists ? route('admin.cms.services.update', $service) : route('admin.cms.services.store') }}"
            class="space-y-6"
        >
            @csrf
            @if($service->exists)
                @method('PUT')
            @endif

            <!-- Section 1: Basic Service Information -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Basic Service Identification</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Title, Roman Urdu translation, aur Marketplace category set karein.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Heading -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Service Title / Heading *</label>
                        <input
                            type="text"
                            name="heading"
                            value="{{ old('heading', $service->heading) }}"
                            required
                            placeholder="e.g. Individual NTN Registration"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Urdu Heading -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Roman Urdu / Urdu Subtitle</label>
                        <input
                            type="text"
                            name="urdu"
                            value="{{ old('urdu', $service->urdu) }}"
                            placeholder="e.g. Zati Shanakhti NTN Registration"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Marketplace Category *</label>
                        <select
                            name="category"
                            required
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                        >
                            @if(isset($serviceCategories) && $serviceCategories->count() > 0)
                                @foreach($serviceCategories as $sc)
                                    <option value="{{ $sc->slug }}" {{ old('category', $service->category) === $sc->slug || old('service_category_id', $service->service_category_id) == $sc->id ? 'selected' : '' }}>
                                        {{ $sc->icon }} {{ $sc->name }}
                                    </option>
                                @endforeach
                            @else
                                @foreach($categories as $catKey => $catData)
                                    <option value="{{ $catKey }}" {{ old('category', $service->category) === $catKey ? 'selected' : '' }}>
                                        {{ $catData['icon'] }} {{ $catData['name'] }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Icon -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Icon (Emoji) *</label>
                        <input
                            type="text"
                            name="icon"
                            value="{{ old('icon', $service->icon ?: '💼') }}"
                            placeholder="e.g. 🆔, 💼, 📜, 🏢, 🇺🇸"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono"
                        >
                        <span class="text-[10px] text-slate-400 mt-1 block">Emoji ya short icon paste karein.</span>
                    </div>

                    <!-- Unique Code -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">System Code</label>
                        <input
                            type="text"
                            name="code"
                            value="{{ old('code', $service->code) }}"
                            {{ $service->exists ? 'readonly' : '' }}
                            placeholder="Auto-generated if empty (e.g. ntn_individual)"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-mono {{ $service->exists ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : '' }}"
                        >
                        <span class="text-[10px] text-slate-400 mt-1 block">Unique identifier for database tracking.</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Pricing, Turnaround & Badges -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Pricing & Delivery Speed</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Fee structure, turnaround time, aur display toggles set karein.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Fee -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Service Fee Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-xs text-slate-400 font-bold">PKR</span>
                            <input
                                type="number"
                                name="fee"
                                step="100"
                                min="0"
                                value="{{ old('fee', $service->fee) }}"
                                required
                                placeholder="1500"
                                class="w-full text-xs pl-12 pr-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500 tabular-nums font-bold"
                            >
                        </div>
                    </div>

                    <!-- Currency Label -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Currency Label</label>
                        <input
                            type="text"
                            name="currency"
                            value="{{ old('currency', $service->currency ?: 'Rs.') }}"
                            placeholder="Rs."
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>

                    <!-- Turnaround -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Turnaround Time *</label>
                        <input
                            type="text"
                            name="turnaround"
                            value="{{ old('turnaround', $service->turnaround ?: '1 - 2 Working Days') }}"
                            required
                            placeholder="e.g. 1 - 2 Working Days"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500"
                        >
                    </div>
                </div>

                <!-- Sort Order & Toggles -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Sort Order</label>
                        <input
                            type="number"
                            name="sort_order"
                            value="{{ old('sort_order', $service->sort_order ?: 1) }}"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 tabular-nums"
                        >
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input
                                type="checkbox"
                                name="popular"
                                value="1"
                                {{ old('popular', $service->popular) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500"
                            >
                            <span>★ Mark as Popular / Highlighted</span>
                        </label>
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 text-emerald-600 rounded-sm border-slate-300 focus:ring-emerald-500"
                            >
                            <span>✅ Visible & Active on Website</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 3: Description & Dynamic Document Requirements Builder -->
            @php
                $normalizedReqs = [];
                $rawReqs = old('requirements', $service->requirements ?? []);
                if (is_array($rawReqs)) {
                    foreach ($rawReqs as $r) {
                        if (is_array($r)) {
                            $normalizedReqs[] = [
                                'label' => $r['label'] ?? '',
                                'type' => $r['type'] ?? 'file',
                                'file_types' => !empty($r['file_types']) ? (array)$r['file_types'] : ['jpg', 'jpeg', 'png', 'pdf'],
                                'required' => isset($r['required']) ? (bool)$r['required'] : true,
                            ];
                        } elseif (is_string($r) && trim($r) !== '') {
                            $isText = preg_match('/phone|email|name|number|address/i', $r);
                            $normalizedReqs[] = [
                                'label' => $r,
                                'type' => $isText ? 'text' : 'file',
                                'file_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
                                'required' => true,
                            ];
                        }
                    }
                }
                if (empty($normalizedReqs)) {
                    $normalizedReqs = [
                        [
                            'label' => 'Color copy of CNIC',
                            'type' => 'file',
                            'file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
                            'required' => true,
                        ],
                    ];
                }
            @endphp

            <div 
                x-data="{
                    reqs: {{ Js::from($normalizedReqs) }},
                    availableExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xlsx'],
                    addReq() {
                        this.reqs.push({
                            label: '',
                            type: 'file',
                            file_types: ['jpg', 'jpeg', 'png', 'pdf'],
                            required: true
                        });
                    },
                    removeReq(index) {
                        if (this.reqs.length > 1) {
                            this.reqs.splice(index, 1);
                        } else {
                            this.reqs[0] = { label: '', type: 'file', file_types: ['jpg', 'jpeg', 'png', 'pdf'], required: true };
                        }
                    },
                    toggleExt(index, ext) {
                        const types = this.reqs[index].file_types;
                        const pos = types.indexOf(ext);
                        if (pos > -1) {
                            if (types.length > 1) types.splice(pos, 1);
                        } else {
                            types.push(ext);
                        }
                    }
                }"
                class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5"
            >
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">3. Description & Client Requirements Builder</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Admin can configure text questions or file uploads with allowed extensions (PDF, PNG, JPG, DOC etc).</p>
                    </div>
                    <button 
                        type="button" 
                        @click="addReq()"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 transition border border-blue-200"
                    >
                        <span>+ Add Requirement</span>
                    </button>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Service Description / Overview</label>
                    <textarea
                        name="desc"
                        rows="3"
                        placeholder="Explain what this service provides in simple Roman Urdu or English..."
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >{{ old('desc', $service->desc) }}</textarea>
                </div>

                <!-- Interactive Requirements List -->
                <div class="space-y-3 pt-2">
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Configured Requirements & Fields for Client
                    </label>

                    <template x-for="(req, index) in reqs" :key="index">
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 transition space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <!-- Label -->
                                    <div class="sm:col-span-7">
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                            Requirement Label / Question <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            :name="'requirements[' + index + '][label]'"
                                            x-model="req.label"
                                            required
                                            placeholder="e.g. Color copy of CNIC, Rent agreement, Phone number"
                                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 bg-white focus:outline-hidden focus:border-blue-500 font-medium"
                                        >
                                    </div>

                                    <!-- Field Type -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Field Type</label>
                                        <select
                                            :name="'requirements[' + index + '][type]'"
                                            x-model="req.type"
                                            class="w-full text-xs px-2.5 py-2 rounded-lg border border-slate-300 bg-white focus:outline-hidden focus:border-blue-500 font-semibold"
                                        >
                                            <option value="file">📁 File Upload</option>
                                            <option value="text">✏️ Text Input</option>
                                            <option value="textarea">📝 Long Textarea</option>
                                            <option value="number">🔢 Number / CNIC</option>
                                        </select>
                                    </div>

                                    <!-- Required Toggle -->
                                    <div class="sm:col-span-2 flex items-center pt-5">
                                        <label class="inline-flex items-center space-x-1.5 text-xs font-semibold text-slate-700 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :name="'requirements[' + index + '][required]'"
                                                value="1"
                                                x-model="req.required"
                                                class="w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500"
                                            >
                                            <span>Required</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Delete Row Button -->
                                <button
                                    type="button"
                                    @click="removeReq(index)"
                                    title="Remove requirement"
                                    class="text-slate-400 hover:text-rose-600 p-1.5 transition rounded-lg hover:bg-rose-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Allowed File Types (When Type == 'file') -->
                            <div x-show="req.type === 'file'" class="pt-2 border-t border-slate-200/80 flex flex-wrap items-center gap-2 text-xs">
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mr-1">Allowed Extensions:</span>
                                <template x-for="ext in availableExtensions" :key="ext">
                                    <label class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md border text-[11px] font-mono cursor-pointer transition"
                                        :class="req.file_types.includes(ext) ? 'bg-blue-50 border-blue-300 text-blue-700 font-bold' : 'bg-white border-slate-200 text-slate-400'">
                                        <input
                                            type="checkbox"
                                            :name="'requirements[' + index + '][file_types][]'"
                                            :value="ext"
                                            :checked="req.file_types.includes(ext)"
                                            @change="toggleExt(index, ext)"
                                            class="hidden"
                                        >
                                        <span x-text="'.' + ext"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-2">
                    <button 
                        type="button" 
                        @click="addReq()"
                        class="w-full py-2.5 border-2 border-dashed border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:text-blue-600 hover:border-blue-300 transition flex items-center justify-center space-x-2"
                    >
                        <span>+ Add Another Requirement / Field</span>
                    </button>
                </div>
            </div>

            <!-- Submit Action Footer -->
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <a
                    href="{{ route('admin.cms.services.index') }}"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                >
                    Cancel
                </a>

                <div class="flex items-center space-x-3">
                    <button
                        type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                    >
                        {{ $service->exists ? 'Save Changes' : 'Create Service' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
