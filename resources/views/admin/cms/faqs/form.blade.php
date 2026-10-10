<x-layouts.app>
    <x-slot:header>
        CMS &bull; {{ $faq->exists ? 'Edit FAQ' : 'Add New FAQ' }}
    </x-slot:header>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <a href="{{ route('admin.cms.faqs.index') }}" class="hover:text-blue-600 font-semibold transition">&larr; Back to FAQs Manager</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-bold">{{ $faq->exists ? 'Edit FAQ #' . $faq->id : 'Add New FAQ' }}</span>
            </div>
            <a href="{{ route('faq') }}" target="_blank" class="text-xs text-blue-600 hover:underline flex items-center space-x-1">
                <span>View Public FAQ</span>
                <span class="text-[10px] font-mono">↗</span>
            </a>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-1">
                <strong class="font-bold block">Please fix the following:</strong>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $faq->exists ? route('admin.cms.faqs.update', $faq) : route('admin.cms.faqs.store') }}"
            class="space-y-6"
        >
            @csrf
            @if($faq->exists)
                @method('PUT')
            @endif

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <!-- Category & Sort Order -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Topic Category *</label>
                        <select
                            name="category"
                            required
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 bg-white"
                        >
                            @foreach($categories as $catKey => $catLabel)
                                @if($catKey !== 'all')
                                    <option value="{{ $catKey }}" {{ old('category', $faq->category) === $catKey ? 'selected' : '' }}>
                                        {{ $catLabel }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Sort Order</label>
                        <input
                            type="number"
                            name="sort_order"
                            value="{{ old('sort_order', $faq->sort_order ?: 1) }}"
                            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 tabular-nums"
                        >
                    </div>
                </div>

                <!-- Question (English / Main) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Question (English or Roman Urdu) *</label>
                    <input
                        type="text"
                        name="question"
                        value="{{ old('question', $faq->question) }}"
                        required
                        placeholder="e.g. Filer banne ke bunyadi faiday kya hain?"
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-semibold"
                    >
                </div>

                <!-- Urdu Sub-Question -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Short Urdu Subtitle / Note (Optional)</label>
                    <input
                        type="text"
                        name="urdu_question"
                        value="{{ old('urdu_question', $faq->urdu_question) }}"
                        placeholder="e.g. Filer vs Non-Filer Faiday aur Nuqsanat"
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 text-blue-700"
                    >
                </div>

                <!-- Answer HTML -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Answer / Explanation (Supports HTML) *</label>
                    <textarea
                        name="answer"
                        rows="8"
                        required
                        placeholder="Wazahat likhein. Aap <p>, <strong>, <ul>, <li> tags istemal kar sakte hain..."
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono"
                    >{{ old('answer', $faq->answer) }}</textarea>
                    <span class="text-[10px] text-slate-400 mt-1 block">Simple paragraph ya HTML formatted text likhein.</span>
                </div>

                <!-- Active Toggle -->
                <div class="pt-2">
                    <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $faq->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 text-emerald-600 rounded-sm border-slate-300 focus:ring-emerald-500"
                        >
                        <span>✅ Published on Live Website</span>
                    </label>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <a
                    href="{{ route('admin.cms.faqs.index') }}"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                >
                    {{ $faq->exists ? 'Update FAQ' : 'Save FAQ' }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
