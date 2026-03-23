@props(['ticket'])

@php
$now = now();
$responseBreached = $ticket->sla_response_breached;
$resolutionBreached = $ticket->sla_resolution_breached;
$responseOk = $ticket->first_responded_at || !$ticket->sla_response_due_at;
$resolutionOk = $ticket->resolved_at || !$ticket->sla_resolution_due_at;

if ($responseBreached || $resolutionBreached) {
    $color = 'text-red-600';
    $label = 'SLA Breached';
} elseif (!$responseOk && $ticket->sla_response_due_at && $ticket->sla_response_due_at->diffInMinutes($now) < 30) {
    $color = 'text-orange-500';
    $label = 'SLA At Risk';
} elseif (!$resolutionOk && $ticket->sla_resolution_due_at && $ticket->sla_resolution_due_at->diffInMinutes($now) < 60) {
    $color = 'text-orange-500';
    $label = 'SLA At Risk';
} else {
    $color = 'text-green-600';
    $label = 'Within SLA';
}
@endphp

@if($ticket->sla_response_due_at || $ticket->sla_resolution_due_at)
<span class="inline-flex items-center text-xs font-medium {{ $color }}">
    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
    {{ $label }}
</span>
@endif
