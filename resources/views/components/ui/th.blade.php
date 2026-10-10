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

<th scope="col" {{ $attributes->merge(['class' => "px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 {$alignClasses}"]) }}>
    {{ $slot }}
</th>
