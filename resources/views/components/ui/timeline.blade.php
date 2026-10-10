@props([
    'items' => [], // array of ['title' => '...', 'date' => '...', 'note' => '...', 'state' => 'current|completed|upcoming']
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @foreach($items as $item)
        @php
            $state = $item['state'] ?? 'upcoming'; // completed, current, upcoming
        @endphp
        <div class="relative flex items-start space-x-3">
            @if(! $loop->last)
                <div class="absolute top-4 left-2 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></div>
            @endif

            <!-- Dot -->
            <div @class([
                'relative flex h-4 w-4 items-center justify-center rounded-full ring-4 flex-shrink-0 mt-0.5',
                'bg-emerald-600 ring-emerald-50 text-white' => $state === 'completed',
                'bg-blue-600 ring-blue-100 text-white' => $state === 'current',
                'bg-slate-300 ring-slate-100' => $state === 'upcoming',
            ])>
                @if($state === 'completed')
                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                @endif
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between text-xs">
                    <span @class([
                        'font-medium',
                        'text-slate-800' => $state === 'completed',
                        'text-blue-600 font-semibold' => $state === 'current',
                        'text-slate-500' => $state === 'upcoming',
                    ])>
                        {{ $item['title'] ?? '' }}
                    </span>
                    @if(!empty($item['date']))
                        <time class="text-slate-400 font-normal tabular-nums">{{ $item['date'] }}</time>
                    @endif
                </div>
                @if(!empty($item['note']))
                    <p class="text-xs text-slate-500 mt-0.5">{{ $item['note'] }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
