@props([
    'label' => null,
    'name' => null,
    'placeholder' => '-- Select an option --',
    'required' => false,
    'disabled' => false,
    'helper' => null,
])

@php
    $hasError = $name && $errors->has($name);
    $selectId = $attributes->get('id') ?? ($name ?? 'select-' . uniqid());
@endphp

<div class="w-full space-y-1.5">
    @if($label)
        <label for="{{ $selectId }}" class="block text-xs font-semibold text-gray-700 tracking-wide uppercase">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-lg shadow-sm">
        <select
            id="{{ $selectId }}"
            @if($name) name="{{ $name }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'w-full rounded-lg border text-sm transition-all focus:outline-none focus:ring-2 disabled:bg-gray-50 disabled:text-gray-500 px-3.5 py-2.5 bg-white ' .
                ($hasError
                    ? 'border-rose-300 text-rose-900 focus:border-rose-500 focus:ring-rose-200 bg-rose-50/30'
                    : 'border-gray-200 text-gray-900 focus:border-emerald-500 focus:ring-emerald-100 hover:border-gray-300')
            ]) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
    </div>

    @if($hasError)
        <p class="text-xs text-rose-600 font-medium flex items-center space-x-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $errors->first($name) }}</span>
        </p>
    @elseif($helper)
        <p class="text-xs text-gray-500">{{ $helper }}</p>
    @endif
</div>
