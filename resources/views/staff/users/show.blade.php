@extends('layouts.staff')
@section('title', $user->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between"><h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2><a href="{{ route('staff.users.edit', $user) }}" class="text-sm text-indigo-600 hover:underline">Edit</a></div>
            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Email</dt><dd>{{ $user->email }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd>{{ $user->phone ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Organization</dt><dd>{{ $user->organization?->name }}</dd></div>
                <div><dt class="text-gray-500">Role</dt><dd>{{ $user->roles->first()?->name }}</dd></div>
                <div><dt class="text-gray-500">Last Login</dt><dd>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white shadow rounded-lg"><div class="px-5 py-4 border-b"><h3 class="font-semibold">Assigned Tickets</h3></div>
            <ul class="divide-y divide-gray-200">
                @forelse($recentTickets as $ticket)
                <li class="px-5 py-3"><a href="{{ route('staff.tickets.show', $ticket) }}" class="flex justify-between"><div><span class="text-sm font-medium text-indigo-600">{{ $ticket->ticket_number }}</span><span class="text-sm text-gray-900 ml-2">{{ $ticket->subject }}</span></div>@include('components.status-badge', ['status' => $ticket->status])</a></li>
                @empty
                <li class="px-5 py-8 text-center text-sm text-gray-500">No assigned tickets.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-5"><h3 class="font-semibold text-gray-900 mb-3">Teams</h3>
            @forelse($user->teams as $team)<p class="text-sm text-gray-700">{{ $team->name }} @if($team->pivot->is_lead)<span class="text-xs text-indigo-600">(Lead)</span>@endif</p>@empty<p class="text-sm text-gray-500">No teams.</p>@endforelse
        </div>

        @if($user->isMspStaff())
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Organization Access</h3>
            @if($user->isMspAdmin())
                <p class="text-sm text-gray-500">Admins have access to all organizations.</p>
            @else
                @if($user->accessibleOrganizations->isEmpty())
                    <p class="text-sm text-green-600 mb-3">Unrestricted - can access all organizations.</p>
                @else
                    <p class="text-sm text-gray-500 mb-2">Restricted to:</p>
                    <ul class="space-y-1 mb-3">
                        @foreach($user->accessibleOrganizations as $org)
                        <li class="text-sm text-gray-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>
                            {{ $org->name }}
                        </li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('staff.users.edit', $user) }}" class="text-sm text-indigo-600 hover:underline">Edit access</a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
