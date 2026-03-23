@extends('layouts.staff')
@section('title', 'Reports')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('staff.reports.sla-compliance') }}" class="bg-white shadow rounded-lg p-6 hover:bg-gray-50">
        <h3 class="text-base font-semibold text-gray-900">SLA Compliance</h3>
        <p class="text-sm text-gray-500 mt-1">Response and resolution breach rates by priority and organization.</p>
    </a>
    <a href="{{ route('staff.reports.ticket-volume') }}" class="bg-white shadow rounded-lg p-6 hover:bg-gray-50">
        <h3 class="text-base font-semibold text-gray-900">Ticket Volume</h3>
        <p class="text-sm text-gray-500 mt-1">Daily ticket creation trends by type, source, and priority.</p>
    </a>
    <a href="{{ route('staff.reports.technician-performance') }}" class="bg-white shadow rounded-lg p-6 hover:bg-gray-50">
        <h3 class="text-base font-semibold text-gray-900">Technician Performance</h3>
        <p class="text-sm text-gray-500 mt-1">Resolution times and SLA compliance per technician.</p>
    </a>
</div>
@endsection
