<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->title }} &bull; {{ setting('firm_name', config('app.name', 'TaxFilling')) }}</title>
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 font-black text-xl text-slate-900">
                <span class="text-blue-600">🏛️</span>
                <span>{{ setting('firm_name', config('app.name', 'TaxFilling')) }}</span>
            </a>

            <!-- Header Menu Items -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-semibold text-slate-600">
                <a href="{{ url('/') }}" class="hover:text-blue-600 transition">Home</a>
                <a href="{{ route('services.index') }}" class="hover:text-blue-600 transition">Services Catalog</a>
                @foreach(header_menu() as $mItem)
                    <a href="{{ $mItem->url }}" target="{{ $mItem->target }}" class="hover:text-blue-600 transition">
                        {{ $mItem->title }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center space-x-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-1 w-full">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-8 sm:p-12">
            <div class="border-b border-slate-100 pb-6 mb-8">
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">{{ $page->title }}</h1>
                <p class="text-xs text-slate-400 mt-2 font-mono">Last updated {{ $page->updated_at->format('F d, Y') }}</p>
            </div>

            <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm space-y-4">
                {!! \Illuminate\Support\Str::markdown($page->content ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-slate-200">{{ setting('firm_name', 'Tax Consultants & Corporate Advisors') }}</p>
                    <p class="mt-1 text-slate-500">&copy; {{ date('Y') }} All rights reserved.</p>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-white transition">Home</a>
                    <a href="{{ route('services.index') }}" class="hover:text-white transition">Services</a>
                    @foreach(footer_menu() as $fItem)
                        <a href="{{ $fItem->url }}" target="{{ $fItem->target }}" class="hover:text-white transition">
                            {{ $fItem->title }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
