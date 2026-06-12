@extends('layouts.staff')
@section('title', $organization->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $organization->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $organization->domains->pluck('domain')->implode(', ') ?: '—' }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('staff.changes.policy', $organization) }}" class="text-sm text-indigo-600 hover:underline">Change Policy</a>
                    <a href="{{ route('staff.organizations.edit', $organization) }}" class="text-sm text-indigo-600 hover:underline">Edit</a>
                </div>
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Address</dt><dd class="text-gray-900">{{ $organization->address ?? '-' }}, {{ $organization->city }} {{ $organization->state }} {{ $organization->zip }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd class="text-gray-900">{{ $organization->phone ?? '-' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b"><h3 class="font-semibold text-gray-900">Recent Tickets</h3></div>
            <ul class="divide-y divide-gray-200">
                @forelse($recentTickets as $ticket)
                <li class="px-5 py-3">
                    <a href="{{ route('staff.tickets.show', $ticket) }}" class="flex justify-between hover:bg-gray-50 -mx-5 -my-3 px-5 py-3">
                        <div>
                            <span class="text-sm font-medium text-indigo-600">{{ $ticket->ticket_number }}</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $ticket->subject }}</span>
                        </div>
                        @include('components.status-badge', ['status' => $ticket->status])
                    </a>
                </li>
                @empty
                <li class="px-5 py-8 text-center text-sm text-gray-500">No tickets.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Users ({{ $organization->users->count() }})</h3>
            <ul class="space-y-2">
                @foreach($organization->users as $user)
                <li class="flex justify-between text-sm">
                    <a href="{{ route('staff.users.show', $user) }}" class="text-indigo-600 hover:underline">{{ $user->name }}</a>
                    <span class="text-gray-500">{{ $user->roles->first()?->name }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white shadow rounded-lg p-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-900">Email Domains</h3>
                <a href="{{ route('staff.organizations.edit', $organization) }}" class="text-xs text-indigo-600 hover:underline">Edit</a>
            </div>
            @forelse($organization->domains as $domain)
            <span class="inline-block bg-gray-100 text-gray-700 text-xs rounded px-2 py-1 mr-1 mb-1">{{ $domain->domain }}</span>
            @empty
            <p class="text-sm text-gray-500">No domains. Inbound mail uses the mailbox default org.</p>
            @endforelse
        </div>

        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Queues</h3>
            <ul class="space-y-2 mb-4">
                @forelse($organization->queues as $queue)
                <li class="flex justify-between items-center text-sm">
                    <span class="text-gray-900">{{ $queue->name }}</span>
                    <form method="POST" action="{{ route('staff.queues.destroy', [$organization, $queue]) }}" onsubmit="return confirm('Remove this queue?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                    </form>
                </li>
                @empty
                <li class="text-sm text-gray-500">No queues yet.</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('staff.queues.store', $organization) }}" class="border-t pt-3 space-y-2">
                @csrf
                <input type="text" name="name" required placeholder="Queue name (e.g. Cybersecurity)" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                <input type="text" name="description" placeholder="Description (optional)" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Add Queue</button>
            </form>
        </div>
    </div>
</div>
@endsection
