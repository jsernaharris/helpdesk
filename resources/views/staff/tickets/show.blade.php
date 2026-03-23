@extends('layouts.staff')
@section('title', $ticket->ticket_number . ' - ' . $ticket->subject)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Ticket Info -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $ticket->subject }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    @include('components.priority-badge', ['priority' => $ticket->priority])
                    @include('components.status-badge', ['status' => $ticket->status])
                    @include('components.sla-indicator', ['ticket' => $ticket])
                </div>
            </div>
            <div class="text-sm text-gray-500 space-y-1">
                <p>Type: <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->type)) }}</span> &middot; Source: <span class="font-medium text-gray-700">{{ ucfirst($ticket->source) }}</span></p>
                <p>Impact: <span class="font-medium text-gray-700">{{ ucfirst($ticket->impact) }}</span> &middot; Urgency: <span class="font-medium text-gray-700">{{ ucfirst($ticket->urgency) }}</span></p>
                @if($ticket->sla_response_due_at)
                <p>Response Due: <span class="font-medium {{ $ticket->sla_response_breached ? 'text-red-600' : 'text-gray-700' }}">{{ $ticket->sla_response_due_at->format('M d, Y H:i') }}</span>
                    @if($ticket->first_responded_at) <span class="text-green-600">(Responded {{ $ticket->first_responded_at->format('M d, H:i') }})</span> @endif
                </p>
                @endif
                @if($ticket->sla_resolution_due_at)
                <p>Resolution Due: <span class="font-medium {{ $ticket->sla_resolution_breached ? 'text-red-600' : 'text-gray-700' }}">{{ $ticket->sla_resolution_due_at->format('M d, Y H:i') }}</span></p>
                @endif
            </div>
        </div>

        <!-- Thread -->
        <div class="space-y-4">
            @foreach($ticket->threads as $thread)
            <div class="bg-white shadow rounded-lg p-5 @if($thread->is_internal) border-l-4 border-yellow-400 @elseif($thread->type === 'system') border-l-4 border-gray-300 @endif">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">{{ $thread->author_name }}</span>
                        @if($thread->is_internal)
                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Internal Note</span>
                        @endif
                        @if($thread->type === 'email_inbound')
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">Email</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-500">{{ $thread->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($thread->body)) !!}</div>
                @if($thread->attachments->count())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($thread->attachments as $attachment)
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs text-gray-700">
                        <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                        {{ $attachment->file_name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Reply / Note Forms -->
        <div class="bg-white shadow rounded-lg p-6">
            <div x-data="{ tab: 'reply' }">
                <div class="flex gap-4 mb-4 border-b">
                    <button @click="tab = 'reply'" :class="tab === 'reply' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-2 border-b-2 text-sm font-medium">Reply</button>
                    <button @click="tab = 'note'" :class="tab === 'note' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-2 border-b-2 text-sm font-medium">Internal Note</button>
                </div>

                <form x-show="tab === 'reply'" method="POST" action="{{ route('staff.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                    @csrf
                    <textarea name="body" rows="4" required placeholder="Type your reply..." class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border mb-3"></textarea>
                    <div class="flex justify-between items-center">
                        <input type="file" name="attachments[]" multiple class="text-sm text-gray-500">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send Reply</button>
                    </div>
                </form>

                <form x-show="tab === 'note'" method="POST" action="{{ route('staff.tickets.note', $ticket) }}">
                    @csrf
                    <input type="hidden" name="is_internal" value="1">
                    <textarea name="body" rows="4" required placeholder="Internal note (not visible to customer)..." class="block w-full rounded-md border-yellow-300 text-sm px-3 py-2 border bg-yellow-50 mb-3"></textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-400">Add Note</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Activity Log</h3>
            <div class="flow-root">
                <ul class="-mb-4">
                    @foreach($ticket->activities as $activity)
                    <li class="pb-4">
                        <div class="flex items-start gap-2">
                            <span class="text-xs text-gray-400 w-32 shrink-0">{{ $activity->created_at->format('M d H:i') }}</span>
                            <div class="text-sm text-gray-600">
                                <span class="font-medium text-gray-900">{{ $activity->user?->name ?? 'System' }}</span>
                                {{ str_replace('_', ' ', $activity->action) }}
                                @if($activity->old_value && $activity->new_value)
                                    from <span class="font-medium">{{ $activity->old_value }}</span> to <span class="font-medium">{{ $activity->new_value }}</span>
                                @elseif($activity->new_value)
                                    <span class="font-medium">{{ $activity->new_value }}</span>
                                @endif
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Actions</h3>

            <!-- Status Change -->
            @if(count($allowedTransitions))
            <form method="POST" action="{{ route('staff.tickets.update', $ticket) }}" class="mb-4">
                @csrf @method('PUT')
                <label class="block text-xs font-medium text-gray-500 mb-1">Change Status</label>
                <div class="flex gap-2">
                    <select name="status" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                        @foreach($allowedTransitions as $transition)
                        <option value="{{ $transition }}">{{ ucfirst(str_replace('_',' ',$transition)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-md bg-gray-800 px-3 py-1.5 text-sm text-white hover:bg-gray-700 whitespace-nowrap">Update</button>
                </div>
            </form>
            @endif

            <!-- Assign -->
            <form method="POST" action="{{ route('staff.tickets.assign', $ticket) }}" class="mb-4">
                @csrf @method('PATCH')
                <label class="block text-xs font-medium text-gray-500 mb-1">Assign To</label>
                <select name="assigned_to_user_id" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border mb-2">
                    <option value="">Unassigned</option>
                    @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" @selected($ticket->assigned_to_user_id == $tech->id)>{{ $tech->name }}</option>
                    @endforeach
                </select>
                <select name="assigned_to_team_id" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border mb-2">
                    <option value="">No Team</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" @selected($ticket->assigned_to_team_id == $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full rounded-md bg-gray-800 px-3 py-1.5 text-sm text-white hover:bg-gray-700">Assign</button>
            </form>

            <!-- Escalate -->
            <form method="POST" action="{{ route('staff.tickets.escalate', $ticket) }}">
                @csrf @method('PATCH')
                <label class="block text-xs font-medium text-gray-500 mb-1">Escalate (Level {{ $ticket->escalation_level }})</label>
                <div class="flex gap-2">
                    <select name="level" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                        @for($i = $ticket->escalation_level + 1; $i <= 5; $i++)
                        <option value="{{ $i }}">Level {{ $i }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-500 whitespace-nowrap">Escalate</button>
                </div>
            </form>
        </div>

        <!-- Details -->
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Organization</dt>
                    <dd class="text-gray-900 font-medium">{{ $ticket->organization?->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Requester</dt>
                    <dd class="text-gray-900">{{ $ticket->requester_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Assigned To</dt>
                    <dd class="text-gray-900">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Team</dt>
                    <dd class="text-gray-900">{{ $ticket->assignedToTeam?->name ?? 'None' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Service</dt>
                    <dd class="text-gray-900">{{ $ticket->serviceCatalog?->name ?? 'None' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">SLA Plan</dt>
                    <dd class="text-gray-900">{{ $ticket->slaPlan?->name ?? 'Default' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Created</dt>
                    <dd class="text-gray-900">{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                </div>
                @if($ticket->resolved_at)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Resolved</dt>
                    <dd class="text-gray-900">{{ $ticket->resolved_at->format('M d, Y H:i') }}</dd>
                </div>
                @endif
                @if($ticket->is_escalated)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Escalation</dt>
                    <dd class="text-red-600 font-medium">Level {{ $ticket->escalation_level }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Tags -->
        @if($ticket->tags->count())
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Tags</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($ticket->tags as $tag)
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" style="background-color: {{ $tag->color ?? '#e5e7eb' }}20; color: {{ $tag->color ?? '#374151' }}">{{ $tag->name }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
@endsection