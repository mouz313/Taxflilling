<x-layouts.guest title="Create Account - TaxFiler.pk">
    <div class="max-w-md mx-auto w-full space-y-6">
        <!-- Header & Logo (Spec 4.2) -->
        <div class="text-center">
            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white mx-auto shadow-sm mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-slate-800">Create your account</h1>
            <p class="text-xs text-slate-500 mt-1">Get started with Pakistan’s easiest tax filing platform</p>
        </div>

        <!-- Centered Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-sm">
            <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{
                showPass: false,
                filerType: '{{ request('filer_type', 'fresh_filer') }}',
                submitting: false
            }" @submit="submitting = true">
                @csrf

                @if(request('service'))
                    <input type="hidden" name="service" value="{{ request('service') }}">
                @endif

                <!-- Full Name (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Full Name (As per CNIC)</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="e.g. Muhammad Usman"
                        class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                    />
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Address *</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="name@example.com"
                        class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                    />
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile Number -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mobile Number (WhatsApp) *</label>
                    <div class="flex rounded-lg border {{ $errors->has('phone') ? 'border-red-500' : 'border-slate-300' }} focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200 overflow-hidden">
                        <span class="inline-flex items-center px-3 bg-slate-50 border-r border-slate-300 text-xs font-semibold text-slate-600 select-none">
                            🇵🇰 +92
                        </span>
                        <input
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            required
                            placeholder="3001234567"
                            class="w-full px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none"
                        />
                    </div>
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CNIC -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">CNIC Number *</label>
                    <input
                        type="text"
                        name="cnic"
                        value="{{ old('cnic') }}"
                        required
                        maxlength="15"
                        placeholder="42101-1234567-1"
                        class="w-full rounded-lg border {{ $errors->has('cnic') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2.5 text-sm font-mono text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                    />
                    @error('cnic')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password *</label>
                        <input
                            :type="showPass ? 'text' : 'password'"
                            name="password"
                            required
                            placeholder="Min 8 characters"
                            class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirm *</label>
                        <input
                            :type="showPass ? 'text' : 'password'"
                            name="password_confirmation"
                            required
                            placeholder="Re-enter password"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        />
                    </div>
                </div>
                @error('password')
                    <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                @enderror

                <!-- Filer Category Toggle -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Filer Category</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label
                            @click="filerType = 'fresh_filer'"
                            :class="filerType === 'fresh_filer' ? 'border-blue-600 bg-blue-50/60 text-blue-900 font-semibold' : 'border-slate-200 text-slate-600'"
                            class="border rounded-lg p-2.5 text-center cursor-pointer transition flex items-center justify-center space-x-1.5 select-none"
                        >
                            <input type="radio" name="filer_type" value="fresh_filer" x-model="filerType" class="hidden">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>Fresh Filer (1st time)</span>
                        </label>

                        <label
                            @click="filerType = 'already_filer'"
                            :class="filerType === 'already_filer' ? 'border-blue-600 bg-blue-50/60 text-blue-900 font-semibold' : 'border-slate-200 text-slate-600'"
                            class="border rounded-lg p-2.5 text-center cursor-pointer transition flex items-center justify-center space-x-1.5 select-none"
                        >
                            <input type="radio" name="filer_type" value="already_filer" x-model="filerType" class="hidden">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Already Filer</span>
                        </label>
                    </div>
                </div>

                <!-- Terms Agreement -->
                <div class="pt-1">
                    <label class="flex items-start space-x-2 cursor-pointer text-xs text-slate-600 select-none">
                        <input type="checkbox" required checked class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            I agree to the <a href="#" class="text-blue-600 font-medium hover:underline">Terms of Service</a> & <a href="#" class="text-blue-600 font-medium hover:underline">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <x-ui.button
                        variant="primary"
                        type="submit"
                        size="md"
                        class="w-full"
                        ::disabled="submitting"
                    >
                        <span x-show="!submitting">Create Account &rarr;</span>
                        <span x-show="submitting" x-cloak class="flex items-center justify-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Creating account...</span>
                        </span>
                    </x-ui.button>
                </div>

                <!-- Sign in link -->
                <div class="text-center pt-2">
                    <p class="text-xs text-slate-500">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline">
                            Sign in
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Secure Note -->
        <div class="text-center">
            <p class="text-xs text-slate-500 flex items-center justify-center space-x-1.5">
                <span>🔒</span>
                <span>Aap ka data encrypted hai</span>
            </p>
        </div>
    </div>
</x-layouts.guest>
