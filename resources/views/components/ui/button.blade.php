@props([
    'variant' => 'primary', // primary, secondary, danger, ghost
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'loading' => false,
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none cursor-pointer';

    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 shadow-sm focus:ring-blue-500',
        'secondary' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 active:bg-slate-100 focus:ring-blue-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 shadow-sm focus:ring-red-500',
        'ghost' => 'text-blue-600 hover:bg-blue-50 active:bg-blue-100 focus:ring-blue-500',
        'outline' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 active:bg-slate-100 focus:ring-blue-500',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @if($loading) disabled wire:loading.attr="disabled" @endif
>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    @elseif($icon)
        <span class="flex-shrink-0">{!! $icon !!}</span>
    @endif
    <span>{{ $slot }}</span>
</button>
