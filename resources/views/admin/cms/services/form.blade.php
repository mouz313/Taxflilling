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
                            @foreach($categories as $catKey => $catData)
                                <option value="{{ $catKey }}" {{ old('category', $service->category) === $catKey ? 'selected' : '' }}>
                                    {{ $catData['icon'] }} {{ $catData['name'] }}
                                </option>
                            @endforeach
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

            <!-- Section 3: Description & Document Requirements -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">3. Description & Client Documents</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Service ki tafseel aur client se required documents list.</p>
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

                <!-- Required Documents (One per line) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Required Client Documents (One per line)</label>
                    <textarea
                        name="requirements_text"
                        rows="4"
                        placeholder="Color copy of CNIC (Front & Back)&#10;Paid electricity bill premises copy&#10;Active mobile number registered on CNIC"
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono"
                    >{{ old('requirements_text', is_array($service->requirements) ? implode("\n", $service->requirements) : '') }}</textarea>
                    <span class="text-[10px] text-slate-400 mt-1 block">Har naye document requirement ko nayi line (Enter) par likhein.</span>
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
