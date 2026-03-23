@extends('layouts.portal')
@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-8">
    <div class="bg-white rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500">Open</dt>
        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['open'] }}</dd>
    </div>
    <div class="bg-white rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500">Pending</dt>
        <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['pending'] }}</dd>
    </div>
    <div class="bg-white rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500">Resolved</dt>
        <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['resolved'] }}</dd>
    </div>
    <div class="bg-white rounded-lg shadow px-5 py-4">
        <dt class="text-sm font-medium text-gray-500">Total</dt>
        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</dd>
    </div>
</div>

<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-gray-900">Recent Tickets</h2>
    <a href="{{ route('portal.tickets.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit New Ticket</a>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($recentTickets as $ticket)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('portal.tickets.show', $ticket) }}'">
                <td class="px-4 py-3 text-sm font-medium text-indigo-600">{{ $ticket->ticket_number }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $ticket->subject }}</td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $ticket->status])</td>
                <td class="px-4 py-3">@include('components.priority-badge', ['priority' => $ticket->priority])</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $ticket->created_at->format('M d, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No tickets yet. <a href="{{ route('portal.tickets.create') }}" class="text-indigo-600 hover:underline">Submit one now.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
