@props([
    'headers' => [],
    'empty' => false,
    'emptyMessage' => 'Koi record nahi mila (No records found).',
    'emptyIcon' => null,
])

<div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-700">
            @if(count($headers) > 0)
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-medium text-slate-500 tracking-wider">
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col" class="px-5 py-3">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-slate-100">
                @if($empty)
                    <tr>
                        <td colspan="{{ max(count($headers), 1) }}" class="px-5 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-medium text-slate-500">{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    @if(isset($footer))
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500">
            {{ $footer }}
        </div>
    @endif
</div>
