@props([
    'title' => null,
    'description' => null,
    'actions' => null,
    'footer' => null,
    'noPadding' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden']) }}>
    @if($title || $actions)
        <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                @if($title)
                    <h3 class="text-lg font-semibold text-slate-800">{{ $title }}</h3>
                @endif
                @if($description)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $description }}</p>
                @endif
            </div>

            @if($actions)
                <div class="flex items-center space-x-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $noPadding ? '' : 'p-5' }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
            {{ $footer }}
        </div>
    @endif
</div>
