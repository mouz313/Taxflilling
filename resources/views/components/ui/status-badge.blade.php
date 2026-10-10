@props([
    'status' => null,
])

<x-ui.badge :status="$status" {{ $attributes }}>
    {{ $slot }}
</x-ui.badge>
