@props(['priority'])

@php
$colors = [
    'critical' => 'bg-red-100 text-red-800 ring-red-600/20',
    'high' => 'bg-orange-100 text-orange-800 ring-orange-600/20',
    'medium' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
    'low' => 'bg-green-100 text-green-800 ring-green-600/20',
];
$color = $colors[$priority] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">
    {{ ucfirst($priority) }}
</span>
