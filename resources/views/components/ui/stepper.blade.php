@props([
    'steps' => [], // array of step names or ['label' => '...', 'desc' => '...']
    'current' => 1,
    'total' => null,
])

@php
    $totalSteps = $total ?? count($steps);
    $percentage = round(($current / max($totalSteps, 1)) * 100);
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <!-- Mobile: Step X of Y + thin progress bar -->
    <div class="sm:hidden space-y-2">
        <div class="flex items-center justify-between text-xs font-medium text-slate-600">
            <span>Step {{ $current }} of {{ $totalSteps }}: <strong class="text-slate-800">{{ $steps[$current - 1] ?? '' }}</strong></span>
            <span class="tabular-nums font-semibold text-blue-600">{{ $percentage }}%</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
        </div>
    </div>

    <!-- Desktop: Horizontal Steps (number circle + label) -->
    <div class="hidden sm:flex items-center justify-between w-full">
        @foreach($steps as $index => $label)
            @php
                $stepNumber = $index + 1;
                $isCurrent = $stepNumber === $current;
                $isCompleted = $stepNumber < $current;
                $isUpcoming = $stepNumber > $current;
            @endphp

            <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                <div class="flex items-center space-x-2">
                    <!-- Circle -->
                    <div @class([
                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold select-none flex-shrink-0 transition-colors',
                        'bg-blue-600 text-white ring-4 ring-blue-100' => $isCurrent,
                        'bg-emerald-600 text-white' => $isCompleted,
                        'bg-slate-100 text-slate-500 border border-slate-300' => $isUpcoming,
                    ])>
                        @if($isCompleted)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <span>{{ $stepNumber }}</span>
                        @endif
                    </div>

                    <!-- Label -->
                    <span @class([
                        'text-xs font-medium transition-colors whitespace-nowrap',
                        'text-blue-600 font-semibold' => $isCurrent,
                        'text-slate-700' => $isCompleted,
                        'text-slate-400' => $isUpcoming,
                    ])>
                        {{ $label }}
                    </span>
                </div>

                <!-- Connecting line -->
                @if(! $loop->last)
                    <div class="flex-1 mx-3 h-0.5 bg-slate-200">
                        <div class="h-0.5 {{ $isCompleted ? 'bg-emerald-600' : 'bg-transparent' }} transition-all"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
