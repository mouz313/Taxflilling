@php
    $isCompany = auth()->user()->role === 'company';

    $icons = [
        'home'      => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'doc'       => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'users'     => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'briefcase' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'phone'     => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
    ];

    // [label, route, active-pattern, icon]
    $main = $isCompany ? [
        ['Dashboard',    'dashboard',              'dashboard',              'home'],
        ['Returns',      'returns.index',          'returns.*',              'doc'],
        ['Clients',      'clients.index',          'clients.*',              'users'],
        ['NTN & Orders', 'admin.orders.index',     'admin.orders.*',         'briefcase'],
        ['Leads',        'admin.callbacks.index',  'admin.callbacks.*',      'phone'],
        ['Settings',     'settings.company',       'settings.*',             'home'],
    ] : [
        ['Dashboard',           'dashboard',           'dashboard',            'home'],
        ['My Tax Returns',      'client.returns',      'client.returns*',      'doc'],
        ['My Applications',     'client.applications', 'client.applications*', 'briefcase'],
        ['Corporate Services',  'services.index',      'services.*',           'users'],
    ];

    $website = [
        ['Categories',        'admin.cms.categories.index', 'admin.cms.categories.*'],
        ['Services',          'admin.cms.services.index',   'admin.cms.services.*'],
        ['Pages',             'admin.cms.pages.index',      'admin.cms.pages.*'],
        ['Menu builder',      'admin.cms.menus.index',      'admin.cms.menus.*'],
        ['FAQs',              'admin.cms.faqs.index',       'admin.cms.faqs.*'],
        ['Site content',      'admin.cms.site.index',       'admin.cms.site.*'],
        ['SEO & Marketing',   'admin.cms.seo.index',        'admin.cms.seo.*'],
        ['FBR tools (demo)',  'admin.fbr.index',            'admin.fbr.*'],
    ];

    $link = fn ($active) => 'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-all duration-200 '
        . ($active ? 'bg-white text-[#1E5C4B] shadow-sm font-black' : 'text-emerald-100/75 hover:bg-white/10 hover:text-white');
@endphp

<nav class="space-y-1.5">
    @foreach($main as [$label, $route, $pattern, $icon])
        <a href="{{ route($route) }}" class="{{ $link(request()->routeIs($pattern)) }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $icons[$icon] }}" />
            </svg>
            {{ $label }}
        </a>
    @endforeach
</nav>

@if($isCompany)
    <div class="mt-6 pt-4 border-t border-emerald-800/40" x-data="{ open: {{ request()->routeIs('admin.cms.*', 'admin.fbr.*') ? 'true' : 'false' }} }">
        <button type="button" @click="open = !open"
                class="flex w-full items-center justify-between px-3 text-[11px] font-bold uppercase tracking-wider text-emerald-300/80 hover:text-white">
            <span>Website &amp; tools</span>
            <span x-text="open ? '−' : '+'" class="text-xs"></span>
        </button>
        <nav x-show="open" x-cloak class="mt-2 space-y-1">
            @foreach($website as [$label, $route, $pattern])
                <a href="{{ route($route) }}" class="{{ $link(request()->routeIs($pattern)) }}">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
@endif
