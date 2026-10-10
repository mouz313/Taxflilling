@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'trend' => null,
    'trendType' => 'up',
    'color' => 'emerald',
])

@php
    $colorMaps = [
        'blue' => ['bg' => 'bg-blue-50 text-blue-600 border-blue-100', 'badge' => 'text-blue-700 bg-blue-50'],
        'emerald' => ['bg' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'badge' => 'text-emerald-700 bg-emerald-50'],
        'sky' => ['bg' => 'bg-sky-50 text-sky-600 border-sky-100', 'badge' => 'text-sky-700 bg-sky-50'],
        'amber' => ['bg' => 'bg-amber-50 text-amber-600 border-amber-100', 'badge' => 'text-amber-700 bg-amber-50'],
        'rose' => ['bg' => 'bg-rose-50 text-rose-600 border-rose-100', 'badge' => 'text-rose-700 bg-rose-50'],
        'purple' => ['bg' => 'bg-purple-50 text-purple-600 border-purple-100', 'badge' => 'text-purple-700 bg-purple-50'],
    ];

    $namedIcons = [
        'users' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
        'review' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>',
        'ready' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        'filed' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
    ];

    $resolvedIcon = $namedIcons[$icon] ?? $icon;
    $palette = $colorMaps[$color] ?? $colorMaps['emerald'];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs hover:shadow-xs transition">
    <div class="flex items-center justify-between">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $title }}</p>
        @if($resolvedIcon)
            <div class="w-10 h-10 rounded-lg flex items-center justify-center border {{ $palette['bg'] }}">
                {!! $resolvedIcon !!}
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-baseline justify-between">
        <h4 class="text-2xl font-semibold text-slate-800 tracking-tight tabular-nums">{{ $value }}</h4>

        @if($trend)
            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full {{ $palette['badge'] }}">
                {{ $trend }}
            </span>
        @endif
    </div>

    @if($subtitle)
        <p class="mt-1 text-xs text-slate-400 font-medium">{{ $subtitle }}</p>
    @endif
</div>
