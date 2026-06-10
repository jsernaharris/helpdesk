@extends('layouts.staff')
@section('title', $mailbox->name)

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-lg font-semibold text-gray-900">{{ $mailbox->name }}</h1>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('staff.mailboxes.test', $mailbox) }}">
                @csrf
                <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Test Connection</button>
            </form>
            <a href="{{ route('staff.mailboxes.edit', $mailbox) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div><dt class="text-gray-500">Email Address</dt><dd class="text-gray-900">{{ $mailbox->email_address }}</dd></div>
            <div><dt class="text-gray-500">Driver</dt><dd class="text-gray-900">{{ $mailbox->isGraph() ? 'Microsoft Graph' : 'IMAP / SMTP' }}</dd></div>
            <div><dt class="text-gray-500">Organization</dt><dd class="text-gray-900">{{ $mailbox->organization?->name ?? '— None (MSP default) —' }}</dd></div>
            <div><dt class="text-gray-500">Status</dt><dd><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $mailbox->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $mailbox->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
            <div><dt class="text-gray-500">Auto-create tickets</dt><dd class="text-gray-900">{{ $mailbox->auto_create_tickets ? 'Yes' : 'No' }}</dd></div>
            <div><dt class="text-gray-500">Last Fetched</dt><dd class="text-gray-900">{{ $mailbox->last_fetched_at?->diffForHumans() ?? 'Never' }}</dd></div>
            <div><dt class="text-gray-500">Default Priority</dt><dd class="text-gray-900">{{ ucfirst($mailbox->default_priority) }}</dd></div>
            <div><dt class="text-gray-500">Default Type</dt><dd class="text-gray-900">{{ str($mailbox->default_type)->headline() }}</dd></div>

            @if($mailbox->isGraph())
            <div><dt class="text-gray-500">Tenant ID</dt><dd class="text-gray-900 break-all">{{ $mailbox->graph_tenant_id }}</dd></div>
            <div><dt class="text-gray-500">Client ID</dt><dd class="text-gray-900 break-all">{{ $mailbox->graph_client_id }}</dd></div>
            <div><dt class="text-gray-500">Graph Mailbox</dt><dd class="text-gray-900">{{ $mailbox->graph_user_id }}</dd></div>
            <div><dt class="text-gray-500">Client Secret</dt><dd class="text-gray-900">•••••• (stored encrypted)</dd></div>
            @else
            <div><dt class="text-gray-500">IMAP Host</dt><dd class="text-gray-900">{{ $mailbox->imap_host }}:{{ $mailbox->imap_port }}</dd></div>
            <div><dt class="text-gray-500">SMTP Host</dt><dd class="text-gray-900">{{ $mailbox->smtp_host }}:{{ $mailbox->smtp_port }}</dd></div>
            <div><dt class="text-gray-500">IMAP Username</dt><dd class="text-gray-900">{{ $mailbox->imap_username }}</dd></div>
            @endif
        </dl>
    </div>

    <form method="POST" action="{{ route('staff.mailboxes.destroy', $mailbox) }}" onsubmit="return confirm('Delete this mailbox?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-500">Delete mailbox</button>
    </form>
</div>
@endsection
