<x-layouts.guest title="Sign In - TaxFiler.pk">
    <div class="max-w-sm mx-auto w-full space-y-6">
        <!-- Header & Company Logo (Spec 4.2 & 5.1) -->
        <div class="text-center">
            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white mx-auto shadow-sm mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-slate-800">Welcome back</h1>
            <p class="text-xs text-slate-500 mt-1">Sign in to access your tax portal</p>
        </div>

        <!-- Centered Card -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPass: false, submitting: false }" @submit="submitting = true">
                @csrf

                <!-- Email or Phone -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email or Mobile</label>
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

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Password</label>
                        <a href="#" class="text-xs text-blue-600 hover:underline">Forgot?</a>
                    </div>
                    <div class="relative">
                        <input
                            :type="showPass ? 'text' : 'password'"
                            name="password"
                            required
                            placeholder="••••••••"
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

                <!-- Remember Me -->
                <div class="flex items-center">
                    <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span>Remember me</span>
                    </label>
                </div>

                <!-- Sign in Button (Spec 3.1 & 5.1) -->
                <div>
                    <x-ui.button
                        variant="primary"
                        type="submit"
                        size="md"
                        class="w-full"
                        ::disabled="submitting"
                    >
                        <span x-show="!submitting">Sign in</span>
                        <span x-show="submitting" x-cloak class="flex items-center justify-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Signing in...</span>
                        </span>
                    </x-ui.button>
                </div>

                <!-- Register Link -->
                <div class="text-center pt-2">
                    <p class="text-xs text-slate-500">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:underline">
                            Create an account
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Secure Note (Spec 4.2 & 5.1) -->
        <div class="text-center space-y-1">
            <p class="text-xs text-slate-500 flex items-center justify-center space-x-1.5">
                <span>🔒</span>
                <span>Aap ka data encrypted hai</span>
            </p>
            <p class="text-[11px] text-slate-400 font-mono">
                Demo: admin@apextax.pk / password123
            </p>
        </div>
    </div>
</x-layouts.guest>
