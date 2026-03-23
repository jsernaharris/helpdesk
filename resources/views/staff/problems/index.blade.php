@extends('layouts.staff')
@section('title', 'Problem Management')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('staff.problems.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Problem Record</a>
</div>
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Problem Ticket</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Known Error</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($problems as $problem)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.problems.show', $problem) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $problem->ticket->ticket_number }}</td>
                <td class="px-4 py-3 text-gray-900">{{ $problem->ticket->subject }}</td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $problem->status])</td>
                <td class="px-4 py-3">{{ $problem->known_error ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $problem->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No problem records.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $problems->links() }}</div>
</div>
@endsection
