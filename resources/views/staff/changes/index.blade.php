@extends('layouts.staff')
@section('title', 'Change Management')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div></div>
    <div class="flex gap-2">
        <a href="{{ route('staff.changes.calendar') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Calendar View</a>
        <a href="{{ route('staff.changes.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Change Request</a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white shadow rounded-lg mb-6 p-4">
    <form method="GET" class="grid grid-cols-2 md:grid-cols-6 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-md border-gray-300 text-sm px-3 py-2 border">
        <select name="status" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Statuses</option>
            @foreach(['draft','submitted','under_review','approved','rejected','implementing','completed','failed','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Types</option>
            @foreach(['standard','normal','emergency'] as $t)
            <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select name="organization_id" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Organizations</option>
            @foreach($organizations as $org)
            <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
            @endforeach
        </select>
        <select name="risk_level" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Risk Levels</option>
            @foreach(['low','medium','high','critical'] as $r)
            <option value="{{ $r }}" @selected(request('risk_level') === $r)>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
    </form>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change #</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">CAB</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($changes as $change)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.changes.show', $change) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $change->change_number }}</td>
                <td class="px-4 py-3 text-gray-900 max-w-xs truncate">{{ $change->ticket?->subject }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->organization?->name }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $change->type === 'emergency' ? 'bg-red-100 text-red-800' : ($change->type === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($change->type) }}</span>
                </td>
                <td class="px-4 py-3">@include('components.priority-badge', ['priority' => $change->risk_level])</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->cab_required ? 'Required' : 'No' }}</td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $change->status])</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->scheduled_start_at?->format('M d, H:i') ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->requester_name }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No change requests.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $changes->links() }}</div>
</div>
@endsection
