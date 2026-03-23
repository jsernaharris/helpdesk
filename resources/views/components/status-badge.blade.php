@props(['status'])

@php
$colors = [
    'new' => 'bg-blue-100 text-blue-800',
    'open' => 'bg-yellow-100 text-yellow-800',
    'pending' => 'bg-orange-100 text-orange-800',
    'on_hold' => 'bg-gray-100 text-gray-800',
    'resolved' => 'bg-green-100 text-green-800',
    'closed' => 'bg-gray-100 text-gray-600',
    'cancelled' => 'bg-red-100 text-red-800',
    // Change request statuses
    'draft' => 'bg-gray-100 text-gray-800',
    'submitted' => 'bg-blue-100 text-blue-800',
    'under_review' => 'bg-yellow-100 text-yellow-800',
    'approved' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800',
    'implementing' => 'bg-purple-100 text-purple-800',
    'completed' => 'bg-green-100 text-green-800',
    'failed' => 'bg-red-100 text-red-800',
    // Problem statuses
    'investigating' => 'bg-yellow-100 text-yellow-800',
    'root_cause_identified' => 'bg-orange-100 text-orange-800',
];
$color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">
    {{ str_replace('_', ' ', ucfirst($status)) }}
</span>
