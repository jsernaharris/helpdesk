@extends('layouts.staff')
@section('title', 'Change Management')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('staff.changes.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Change Request</a>
</div>
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change #</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($changes as $change)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.changes.show', $change) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $change->change_number }}</td>
                <td class="px-4 py-3 text-gray-900">{{ $change->ticket->subject }}</td>
                <td class="px-4 py-3">{{ ucfirst($change->type) }}</td>
                <td class="px-4 py-3">@include('components.priority-badge', ['priority' => $change->risk_level])</td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $change->status])</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->scheduled_start_at?->format('M d, Y') ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No change requests.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $changes->links() }}</div>
</div>
@endsection
