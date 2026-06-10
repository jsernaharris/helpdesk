@extends('layouts.staff')
@section('title', 'Mailboxes')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('staff.mailboxes.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Mailbox</a>
</div>
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Fetched</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($mailboxes as $mailbox)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.mailboxes.show', $mailbox) }}'">
                <td class="px-4 py-3 text-sm font-medium text-indigo-600">{{ $mailbox->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $mailbox->email_address }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $mailbox->isGraph() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $mailbox->isGraph() ? 'Microsoft Graph' : 'IMAP' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $mailbox->organization?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $mailbox->last_fetched_at?->diffForHumans() ?? 'Never' }}</td>
                <td class="px-4 py-3"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $mailbox->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $mailbox->is_active ? 'Active' : 'Inactive' }}</span></td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-6 text-sm text-gray-500 text-center">No mailboxes configured yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $mailboxes->links() }}</div>
</div>
@endsection
