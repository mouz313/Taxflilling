@props([
    'status' => null,
    'size' => 'md',
    'dot' => false,
])

@php
    // Map return lifecycle status directly to Section 2.2 spec
    $statusMap = [
        'draft' => ['class' => 'bg-slate-100 text-slate-700', 'label' => 'Draft'],
        'docs_pending' => ['class' => 'bg-amber-100 text-amber-800', 'label' => 'Docs Pending'],
        'in_review' => ['class' => 'bg-blue-100 text-blue-800', 'label' => 'In Review'],
        'query' => ['class' => 'bg-orange-100 text-orange-800', 'label' => 'Query'],
        'ready_to_file' => ['class' => 'bg-indigo-100 text-indigo-800', 'label' => 'Ready to File'],
        'filed' => ['class' => 'bg-emerald-100 text-emerald-800', 'label' => 'Filed'],
        'closed' => ['class' => 'bg-slate-200 text-slate-600', 'label' => 'Closed'],

        // Additional supporting states
        'active' => ['class' => 'bg-emerald-100 text-emerald-800', 'label' => 'Active'],
        'inactive' => ['class' => 'bg-slate-100 text-slate-700', 'label' => 'Inactive'],
        'paid' => ['class' => 'bg-emerald-100 text-emerald-800', 'label' => 'Paid'],
        'pending' => ['class' => 'bg-amber-100 text-amber-800', 'label' => 'Pending'],
        'salaried' => ['class' => 'bg-blue-100 text-blue-800', 'label' => 'Salaried'],
        'business' => ['class' => 'bg-indigo-100 text-indigo-800', 'label' => 'Business'],
    ];

    $matched = $statusMap[$status] ?? [
        'class' => 'bg-slate-100 text-slate-700',
        'label' => ucfirst(str_replace('_', ' ', $status ?? 'Unknown'))
    ];

    $label = $slot->isNotEmpty() ? $slot : $matched['label'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$matched['class']}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70 mr-1.5"></span>
    @endif
    <span>{{ $label }}</span>
</span>
