@extends('layouts.portal')
@section('title', 'Change Requests')

@section('content')
<div class="flex justify-between items-center mb-6">
    <form method="GET" class="flex gap-3">
        <select name="status" class="rounded-md border-gray-300 text-sm px-3 py-2 border" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(['draft','submitted','under_review','approved','rejected','implementing','completed','failed','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('portal.changes.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Request a Change</a>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change #</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($changes as $change)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('portal.changes.show', $change) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $change->change_number }}</td>
                <td class="px-4 py-3 text-gray-900 max-w-xs truncate">{{ $change->ticket?->subject }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->category?->name ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $change->type === 'emergency' ? 'bg-red-100 text-red-800' : ($change->type === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($change->type) }}</span>
                </td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $change->status])</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->scheduled_start_at?->format('M d, H:i') ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $change->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No change requests. <a href="{{ route('portal.changes.create') }}" class="text-indigo-600 hover:underline">Submit one now.</a></td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $changes->links() }}</div>
</div>
@endsection
