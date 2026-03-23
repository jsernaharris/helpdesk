@extends('layouts.staff')
@section('title', $organization->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $organization->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $organization->email_domain }}</p>
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

    <div>
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
    </div>
</div>
@endsection
