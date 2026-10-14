@props([
    'title' => null,
    'description' => null,
])
@php
    $firmName = setting('firm_name') ?? 'Apex Tax Advisory';
    $firmWhatsapp = setting('firm_whatsapp') ?? '923001234567';
    $pageTitle = $title ? "{$title} - {$firmName}" : "{$firmName} | Certified Tax & Corporate Services Pakistan";
    $metaDesc = $description ?? 'Certified Tax Filing, NTN Registration, FBR IRIS Active Taxpayer List (ATL) status, and SECP Company Incorporation in Pakistan.';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Marketing Tracking & Verification Tags -->
    <x-marketing-head />

    <!-- Google Fonts: Inter / Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col justify-between selection:bg-blue-600 selection:text-white">
    <x-ui.toast />
    <x-marketing-body />

    <!-- Top Global Header (Full Width) -->
    <header class="w-full bg-white border-b border-slate-200 shadow-2xs sticky top-0 z-40">
        <div class="w-full px-4 sm:px-8 lg:px-12 py-3.5 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2.5 flex-shrink-0 group">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <span class="text-lg font-extrabold tracking-tight text-slate-900 group-hover:text-blue-600 transition">{{ $firmName }}</span>
                    <span class="block text-[9px] font-bold uppercase tracking-wider text-blue-600">Tax & Corporate Advisory</span>
                </div>
            </a>

            <!-- Center Navigation Links (Dynamic from CMS Menus) -->
            <nav class="hidden md:flex items-center space-x-6 text-xs font-semibold text-slate-600">
                @php
                    $guestNav = header_menu();
                @endphp
                @if($guestNav->isNotEmpty())
                    @foreach($guestNav as $gNav)
                        <a href="{{ $gNav->url }}" target="{{ $gNav->target }}" class="hover:text-blue-600 transition {{ request()->url() === url($gNav->url) ? 'text-blue-600 font-bold' : '' }}">
                            {{ $gNav->title }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('services.index') }}" class="hover:text-blue-600 transition {{ request()->routeIs('services.*') ? 'text-blue-600 font-bold' : '' }}">Business Services</a>
                    <a href="{{ route('track') }}" class="hover:text-blue-600 transition {{ request()->routeIs('track') ? 'text-blue-600 font-bold' : '' }}">Track Application</a>
                    <a href="{{ route('faq') }}" class="hover:text-blue-600 transition {{ request()->routeIs('faq') ? 'text-blue-600 font-bold' : '' }}">Guides & FAQ</a>
                    <a href="{{ route('contact') }}" class="hover:text-blue-600 transition {{ request()->routeIs('contact') ? 'text-blue-600 font-bold' : '' }}">Contact</a>
                @endif
            </nav>

            <!-- Right Actions & WhatsApp -->
            <div class="flex items-center space-x-3 text-xs font-medium text-slate-600 flex-shrink-0">
                <a
                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $firmWhatsapp) }}?text={{ urlencode('Assalam-o-Alaikum, mujhe tax consultation ke hawaale se rabta karna hai.') }}"
                    target="_blank"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition shadow-2xs"
                    title="WhatsApp Consultation"
                >
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.791.82 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.766-5.767-5.766zm3.385 8.165c-.144.405-.837.774-1.17.823-.312.043-.681.085-2.227-.552-1.776-.732-2.871-2.544-2.958-2.661-.087-.116-.717-.954-.717-1.821 0-.866.452-1.294.613-1.468.161-.173.351-.216.468-.216.116 0 .233.002.333.007.106.006.248-.04.389.297.144.348.491 1.199.534 1.286.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.179.182-.077.357.102.174.453.748.972 1.211.669.597 1.233.782 1.408.869.174.087.276.072.378-.043.102-.116.435-.507.551-.681.116-.174.233-.145.391-.087s1.015.478 1.189.565c.174.087.29.13.333.203.043.072.043.419-.101.824zM12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.2-1.31c1.42.79 3.05 1.31 4.8 1.31 5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
                    </svg>
                    <span>WhatsApp</span>
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="text-blue-600 font-bold hover:underline">Dashboard &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-blue-600 font-bold transition">Sign In</a>
                    <a href="{{ route('register') }}" class="px-3.5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-xs">
                        Start Filing
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Page Content -->
    <main class="flex-1 flex flex-col justify-center items-center py-6 sm:py-10 px-4 sm:px-6">
        <div class="w-full max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    <!-- Clean Footer (Full Width with Dynamic CMS Links) -->
    <footer class="w-full py-6 text-center text-xs text-slate-500 border-t border-slate-200 bg-white">
        <div class="w-full px-4 sm:px-8 lg:px-12 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} {{ $firmName }}. Authorized Tax & Corporate Advisory Platform (FBR IRIS 2.0 Integrated).</p>
            <div class="flex flex-wrap items-center justify-center space-x-5 text-slate-600 font-medium">
                @php
                    $guestFoot = footer_menu();
                @endphp
                @if($guestFoot->isNotEmpty())
                    @foreach($guestFoot as $fNav)
                        <a href="{{ $fNav->url }}" target="{{ $fNav->target }}" class="hover:text-blue-600 transition">
                            {{ $fNav->title }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('track') }}" class="hover:text-blue-600 transition">Track Application</a>
                    <a href="{{ route('faq') }}" class="hover:text-blue-600 transition">Tax Guides & FAQ</a>
                    <a href="{{ route('contact') }}" class="hover:text-blue-600 transition">Contact Us</a>
                @endif
                <span class="text-slate-400">🔒 256-bit SSL Encrypted</span>
            </div>
        </div>
    </footer>
</body>
</html>
