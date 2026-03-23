@extends('layouts.staff')
@section('title', 'Dashboard')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="bg-white overflow-hidden rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500 truncate">Open Tickets</dt>
        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['open'] }}</dd>
    </div>
    <div class="bg-white overflow-hidden rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
        <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['pending'] }}</dd>
    </div>
    <div class="bg-white overflow-hidden rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500 truncate">SLA Breached</dt>
        <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $stats['sla_breached'] }}</dd>
    </div>
    <div class="bg-white overflow-hidden rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500 truncate">My Open Tickets</dt>
        <dd class="mt-1 text-3xl font-semibold text-indigo-600">{{ $stats['my_tickets'] }}</dd>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- My Assigned Tickets -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">My Assigned Tickets</h3>
        </div>
        <ul class="divide-y divide-gray-200">
            @forelse($myTickets as $ticket)
            <li class="px-5 py-3">
                <a href="{{ route('staff.tickets.show', $ticket) }}" class="flex items-center justify-between hover:bg-gray-50 -mx-5 -my-3 px-5 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-indigo-600">{{ $ticket->ticket_number }}</p>
                        <p class="text-sm text-gray-900 truncate">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-500">{{ $ticket->organization?->name }} &middot; {{ $ticket->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="ml-4 flex items-center gap-2">
                        @include('components.priority-badge', ['priority' => $ticket->priority])
                        @include('components.status-badge', ['status' => $ticket->status])
                    </div>
                </a>
            </li>
            @empty
            <li class="px-5 py-8 text-center text-sm text-gray-500">No tickets assigned to you.</li>
            @endforelse
        </ul>
    </div>

    <!-- Recent Tickets -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-900">Recent Tickets</h3>
            <span class="text-sm text-gray-500">{{ $stats['unassigned'] }} unassigned</span>
        </div>
        <ul class="divide-y divide-gray-200">
            @forelse($recentTickets as $ticket)
            <li class="px-5 py-3">
                <a href="{{ route('staff.tickets.show', $ticket) }}" class="flex items-center justify-between hover:bg-gray-50 -mx-5 -my-3 px-5 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-indigo-600">{{ $ticket->ticket_number }}</p>
                        <p class="text-sm text-gray-900 truncate">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $ticket->organization?->name }} &middot;
                            {{ $ticket->assignedTo?->name ?? 'Unassigned' }} &middot;
                            {{ $ticket->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="ml-4 flex items-center gap-2">
                        @include('components.priority-badge', ['priority' => $ticket->priority])
                        @include('components.status-badge', ['status' => $ticket->status])
                    </div>
                </a>
            </li>
            @empty
            <li class="px-5 py-8 text-center text-sm text-gray-500">No tickets yet.</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="bg-white overflow-hidden rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500">On Hold</dt>
        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['on_hold'] }}</dd>
    </div>
    <div class="bg-white overflow-hidden rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500">Resolved Today</dt>
        <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $stats['resolved_today'] }}</dd>
    </div>
    <div class="bg-white overflow-hidden rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500">Unassigned</dt>
        <dd class="mt-1 text-2xl font-semibold text-orange-600">{{ $stats['unassigned'] }}</dd>
    </div>
</div>
@endsection