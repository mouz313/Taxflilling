<x-layouts.guest title="Forgot Password - TaxFiler.pk">
    <div class="max-w-sm mx-auto w-full space-y-6">
        <!-- Header -->
        <div class="text-center">
            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white mx-auto shadow-sm mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-slate-800">Forgot Password</h1>
            <p class="text-xs text-slate-500 mt-1">Enter your registered email or phone to reset your password</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800 flex items-start space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <!-- Email or Phone -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email or Mobile Number</label>
                    <input
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="name@example.com or 03001234567"
                        class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                    />
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
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
                        <span x-show="!submitting">Send Reset Link</span>
                        <span x-show="submitting" x-cloak class="flex items-center justify-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Sending link...</span>
                        </span>
                    </x-ui.button>
                </div>

                <!-- Back to Login -->
                <div class="text-center pt-2">
                    <p class="text-xs text-slate-500">
                        Remember your password?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline">
                            Sign in
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
