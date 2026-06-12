@extends('layouts.staff')
@section('title', 'Tickets')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div></div>
    <a href="{{ route('staff.tickets.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New Ticket</a>
</div>

<!-- Filters -->
<div class="bg-white shadow rounded-lg mb-6 p-4">
    <form method="GET" class="grid grid-cols-2 md:grid-cols-6 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-md border-gray-300 text-sm px-3 py-2 border">
        <select name="status" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Statuses</option>
            @foreach(['new','open','pending','on_hold','resolved','closed','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="priority" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Priorities</option>
            @foreach(['critical','high','medium','low'] as $p)
            <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <select name="organization_id" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Organizations</option>
            @foreach($organizations as $org)
            <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
            @endforeach
        </select>
        <select name="queue_id" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Queues</option>
            @foreach($queues as $queue)
            <option value="{{ $queue->id }}" @selected(request('queue_id') == $queue->id)>{{ $queue->name }}</option>
            @endforeach
        </select>
        <select name="assigned_to" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Assignees</option>
            <option value="me" @selected(request('assigned_to') === 'me')>Assigned to Me</option>
            <option value="unassigned" @selected(request('assigned_to') === 'unassigned')>Unassigned</option>
            @foreach($technicians as $tech)
            <option value="{{ $tech->id }}" @selected(request('assigned_to') == $tech->id)>{{ $tech->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
    </form>
</div>

<!-- Ticket Table -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Queue</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SLA</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($tickets as $ticket)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.tickets.show', $ticket) }}'">
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-600">{{ $ticket->ticket_number }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">{{ $ticket->subject }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $ticket->organization?->name }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    @if($ticket->queue)
                    <span class="inline-block bg-indigo-50 text-indigo-700 text-xs rounded px-2 py-0.5">{{ $ticket->queue->name }}</span>
                    @else
                    <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $ticket->requester?->name ?? $ticket->requesterContact?->email ?? '-' }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $ticket->assignedTo?->name ?? $ticket->assignedToTeam?->name ?? 'Unassigned' }}</td>
                <td class="px-4 py-3 whitespace-nowrap">@include('components.priority-badge', ['priority' => $ticket->priority])</td>
                <td class="px-4 py-3 whitespace-nowrap">@include('components.status-badge', ['status' => $ticket->status])</td>
                <td class="px-4 py-3 whitespace-nowrap">@include('components.sla-indicator', ['ticket' => $ticket])</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $ticket->created_at->format('M d, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $tickets->links() }}
    </div>
</div>
@endsection