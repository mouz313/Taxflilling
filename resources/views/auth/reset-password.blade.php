<x-layouts.guest title="Reset Password - TaxFiler.pk">
    <div class="max-w-sm mx-auto w-full space-y-6">
        <!-- Header -->
        <div class="text-center">
            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white mx-auto shadow-sm mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-slate-800">Set New Password</h1>
            <p class="text-xs text-slate-500 mt-1">Please enter your new password below</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4" x-data="{ showPass: false, submitting: false }" @submit="submitting = true">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                    />
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                    <div class="relative">
                        <input
                            :type="showPass ? 'text' : 'password'"
                            name="password"
                            required
                            placeholder="At least 8 characters"
                            class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-300' }} px-3 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        />
                        <button
                            type="button"
                            @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                        >
                            <span class="text-xs" x-text="showPass ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                    <input
                        :type="showPass ? 'text' : 'password'"
                        name="password_confirmation"
                        required
                        placeholder="Re-enter password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                    />
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
                        <span x-show="!submitting">Update Password</span>
                        <span x-show="submitting" x-cloak class="flex items-center justify-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Updating...</span>
                        </span>
                    </x-ui.button>
                </div>

                <!-- Back to Login -->
                <div class="text-center pt-2">
                    <p class="text-xs text-slate-500">
                        Back to
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline">
                            Sign in
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
