<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? ($header ?? 'Dashboard') }} - {{ config('app.name', 'TaxFiler.pk') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, sans-serif; }
    </style>

    @stack('styles')
</head>
<body
    class="min-h-full bg-slate-50 text-slate-800 antialiased flex"
    x-data="{
        sidebarOpen: false,
        userDropdownOpen: false
    }"
>
    <!-- Global Toast Component -->
    <x-ui.toast />

    <!-- ========================================================================= -->
    <!-- 1. MOBILE SIDEBAR BACKDROP & DRAWER                                        -->
    <!-- ========================================================================= -->
    <div
        x-show="sidebarOpen"
        x-cloak
        class="fixed inset-0 z-50 lg:hidden"
        style="display: none;"
    >
        <!-- Backdrop Overlay -->
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
            @click="sidebarOpen = false"
        ></div>

        <!-- Slide-over Drawer -->
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="relative flex-1 flex flex-col max-w-xs w-full bg-slate-900 text-slate-200 h-full shadow-2xl"
        >
            <!-- Drawer Brand & Close Button -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800/80 bg-slate-950/40">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white tracking-tight">TaxFiler<span class="text-blue-500">.pk</span></span>
                </a>
                <button
                    @click="sidebarOpen = false"
                    type="button"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Drawer Nav Links -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
                <x-nav.links />
            </div>

            <!-- Drawer Bottom User Profile -->
            @auth
                <div class="p-4 border-t border-slate-800/80 bg-slate-950/40 flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0">
                            {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 capitalize">{{ auth()->user()->role ?? 'User' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. DESKTOP PERMANENT SIDEBAR (Fixed/Sticky w-64)                           -->
    <!-- ========================================================================= -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 lg:z-30 bg-slate-900 border-r border-slate-800 text-slate-300">
        <!-- Sidebar Brand Header -->
        <div class="h-16 px-6 border-b border-slate-800/80 flex items-center justify-between bg-slate-950/40">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <span class="text-base font-bold text-white tracking-tight">TaxFiler<span class="text-blue-500">.pk</span></span>
                    <span class="block text-[9px] uppercase font-bold text-blue-400 tracking-wider">{{ auth()->user()?->isCompany() ? 'Practice Portal' : 'Client Portal' }}</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links Group -->
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
            <x-nav.links />
        </div>

        <!-- Sidebar User Card (Bottom) -->
        @auth
            <div class="p-3.5 border-t border-slate-800/80 bg-slate-950/40 flex items-center justify-between">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="relative flex-shrink-0">
                        <div class="w-9 h-9 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                            {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-slate-900"></span>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-400 capitalize">{{ auth()->user()->role === 'company' ? 'Tax Consultant' : 'Filer' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        title="Sign Out"
                        class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <!-- ========================================================================= -->
    <!-- 3. MAIN WORKSPACE COLUMN (Header + Content Canvas + Footer)               -->
    <!-- ========================================================================= -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
        <!-- Proper Dashboard Top Header -->
        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-20 shadow-2xs">
            <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                <!-- Left: Mobile Toggle & Page Title / Breadcrumb -->
                <div class="flex items-center space-x-3.5 min-w-0">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>

                    <div class="min-w-0">
                        <div class="flex items-center space-x-2 text-xs text-slate-400">
                            <span class="hover:text-slate-600 transition">TaxFiler Portal</span>
                            <span>&rsaquo;</span>
                            <span class="font-medium text-slate-700 truncate">{{ $header ?? 'Dashboard' }}</span>
                        </div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight truncate hidden sm:block">
                            {{ $header ?? (auth()->user()?->isCompany() ? 'Consultant Operations' : 'Client Portal') }}
                        </h1>
                    </div>
                </div>

                <!-- Right Header Actions (Fiscal Pill, IRIS status, WhatsApp & User) -->
                <div class="flex items-center space-x-2.5 sm:space-x-3 flex-shrink-0">
                    <!-- Tax Year 2026-27 Badge -->
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Tax Year 2026–27
                    </span>

                    <!-- IRIS 2.0 Status -->
                    <span class="hidden md:inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                        🏛️ IRIS 2.0 Connected
                    </span>

                    <!-- Direct WhatsApp Support Helpline -->
                    <a
                        href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20TaxFiler%20dashboard%20ke%20hawaale%20se%20support%20chahiye."
                        target="_blank"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:border-[#25D366] hover:bg-emerald-50/50 hover:text-emerald-900 transition shadow-2xs"
                        title="Instant WhatsApp Support"
                    >
                        <svg class="w-3.5 h-3.5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.791.82 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.766-5.767-5.766zm3.385 8.165c-.144.405-.837.774-1.17.823-.312.043-.681.085-2.227-.552-1.776-.732-2.871-2.544-2.958-2.661-.087-.116-.717-.954-.717-1.821 0-.866.452-1.294.613-1.468.161-.173.351-.216.468-.216.116 0 .233.002.333.007.106.006.248-.04.389.297.144.348.491 1.199.534 1.286.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.179.182-.077.357.102.174.453.748.972 1.211.669.597 1.233.782 1.408.869.174.087.276.072.378-.043.102-.116.435-.507.551-.681.116-.174.233-.145.391-.087s1.015.478 1.189.565c.174.087.29.13.333.203.043.072.043.419-.101.824zM12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.2-1.31c1.42.79 3.05 1.31 4.8 1.31 5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                        <span class="hidden sm:inline">WhatsApp</span>
                    </a>

                    <!-- User Profile Dropdown Menu -->
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button
                                @click="open = !open"
                                @click.outside="open = false"
                                type="button"
                                class="flex items-center space-x-2 p-1 rounded-lg hover:bg-slate-100 transition"
                            >
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                                </div>
                                <span class="hidden xl:inline text-xs font-semibold text-slate-700 max-w-[120px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50 text-xs"
                                style="display: none;"
                            >
                                <div class="px-3.5 py-2 border-b border-slate-100">
                                    <p class="font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                </div>

                                @if(auth()->user()->role === 'company')
                                    <a href="{{ route('settings.company') }}" class="flex items-center px-3.5 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                        ⚙️ Firm Settings & Fees
                                    </a>
                                    <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-between px-3.5 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                        <span>🌐 View Public Site</span>
                                        <span class="text-[10px] text-slate-400 font-mono">↗</span>
                                    </a>
                                @else
                                    <a href="{{ route('track') }}" class="flex items-center px-3.5 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                        🔍 Track Applications
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center px-3.5 py-2 text-rose-600 hover:bg-rose-50">
                                        🚪 Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto pb-24 sm:pb-12">
            {{ $slot }}
        </main>

        <!-- Proper Dashboard Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-6 lg:px-8 mt-auto text-xs text-slate-500">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-slate-800">TaxFiler<span class="text-blue-600">.pk</span></span>
                    <span>&copy; {{ date('Y') }}. Pakistan Tax & Corporate Advisory Console.</span>
                </div>

                <div class="flex items-center space-x-1.5 text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>All Systems Operational</span>
                    <span>·</span>
                    <span>FBR IRIS 2.0 Live Sync</span>
                    <span>·</span>
                    <span>256-bit Encrypted Vault</span>
                </div>

                <div class="flex items-center space-x-4 text-slate-600 font-medium">
                    <a href="{{ route('track') }}" class="hover:text-blue-600 transition">Track Application</a>
                    <a href="{{ route('faq') }}" class="hover:text-blue-600 transition">Tax FAQs</a>
                    <a href="{{ route('contact') }}" class="hover:text-blue-600 transition">Help & WhatsApp</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Bottom Navigation Bar (For Quick 1-Thumb Switching on Handsets) -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-30 flex items-center justify-around py-2 shadow-lg">
        <a
            href="{{ route('dashboard') }}"
            class="flex flex-col items-center text-[10px] font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}"
        >
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Home</span>
        </a>

        @if(auth()->check() && auth()->user()->role === 'company')
            <a
                href="{{ route('returns.index') }}"
                class="flex flex-col items-center text-[10px] font-medium {{ request()->routeIs('returns.*') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Returns</span>
            </a>
            <a
                href="{{ route('clients.index') }}"
                class="flex flex-col items-center text-[10px] font-medium {{ request()->routeIs('clients.*') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Clients</span>
            </a>
        @else
            <a
                href="{{ route('client.returns') }}"
                class="flex flex-col items-center text-[10px] font-medium {{ request()->routeIs('client.returns*') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Returns</span>
            </a>
            <a
                href="{{ route('services.index') }}"
                class="flex flex-col items-center text-[10px] font-medium {{ request()->routeIs('services.*') ? 'text-blue-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Services</span>
            </a>
        @endif

        <button
            type="button"
            @click="sidebarOpen = true"
            class="flex flex-col items-center text-[10px] font-medium text-slate-500 hover:text-blue-600"
        >
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span>Menu</span>
        </button>
    </nav>

    @stack('scripts')
</body>
</html>
