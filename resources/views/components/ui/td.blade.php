@props([
    'align' => 'left', // left, center, right
])

@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align] ?? 'text-left';
@endphp

<td {{ $attributes->merge(['class' => "px-6 py-4 text-sm text-gray-700 whitespace-nowrap {$alignClasses}"]) }}>
    {{ $slot }}
</td>
