@extends('layouts.staff')
@section('title', 'Change: ' . $change->change_number)

@section('content')
@if($blackoutWarning)
<div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4">
    <p class="text-sm font-medium text-red-800">Warning: The scheduled time for this change falls within a blackout period.</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $change->ticket?->subject }}</h2>
                    <p class="text-sm text-gray-500">{{ $change->change_number }} &middot; {{ $change->organization?->name }}</p>
                </div>
                <div class="flex gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $change->type === 'emergency' ? 'bg-red-100 text-red-800' : ($change->type === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($change->type) }}</span>
                    @include('components.priority-badge', ['priority' => $change->risk_level])
                    @include('components.status-badge', ['status' => $change->status])
                </div>
            </div>

            <!-- Status Progress Bar -->
            @php
                $steps = ['draft', 'submitted', 'under_review', 'approved', 'implementing', 'completed'];
                $currentIdx = array_search($change->status, $steps);
                if ($currentIdx === false) $currentIdx = -1;
            @endphp
            <div class="flex items-center gap-1 mb-4">
                @foreach($steps as $idx => $step)
                <div class="flex-1 h-2 rounded-full {{ $idx <= $currentIdx ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs text-gray-500 mb-4">
                @foreach($steps as $step)
                <span>{{ ucfirst(str_replace('_', ' ', $step)) }}</span>
                @endforeach
            </div>

            <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($change->ticket?->description)) !!}</div>
        </div>

        <!-- Plans -->
        @foreach(['business_justification' => 'Business Justification', 'impact_assessment' => 'Impact Assessment', 'implementation_plan' => 'Implementation Plan', 'rollback_plan' => 'Rollback Plan', 'test_plan' => 'Test Plan', 'communication_plan' => 'Communication Plan'] as $field => $label)
        @if($change->$field)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2">{{ $label }}</h3>
            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->$field }}</div>
        </div>
        @endif
        @endforeach

        <!-- Approval History -->
        @if($change->approvals->count())
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Approval History</h3>
            <div class="space-y-3">
                @foreach($change->approvals as $approval)
                <div class="flex items-start gap-3 p-3 rounded-lg {{ $approval->decision === 'approved' ? 'bg-green-50' : ($approval->decision === 'rejected' ? 'bg-red-50' : 'bg-yellow-50') }}">
                    <div class="shrink-0 mt-0.5">
                        @if($approval->decision === 'approved')
                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif($approval->decision === 'rejected')
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @else
                        <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $approval->user?->name }} - Level {{ $approval->approval_level }}</p>
                        <p class="text-sm text-gray-600">{{ ucfirst($approval->decision) }} {{ $approval->created_at->format('M d, Y H:i') }}</p>
                        @if($approval->comments)
                        <p class="text-sm text-gray-500 mt-1">{{ $approval->comments }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Post-Implementation Review -->
        @if(in_array($change->status, ['completed', 'failed']))
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Post-Implementation Review</h3>
            @if($change->review)
            <div class="space-y-3 text-sm">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="text-center p-3 rounded-lg {{ $change->review->objectives_met ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="font-medium">Objectives Met</p>
                        <p>{{ $change->review->objectives_met ? 'Yes' : 'No' }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg {{ $change->review->on_schedule ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="font-medium">On Schedule</p>
                        <p>{{ $change->review->on_schedule ? 'Yes' : 'No' }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg {{ $change->review->within_budget ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="font-medium">Within Budget</p>
                        <p>{{ $change->review->within_budget ? 'Yes' : 'No' }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg {{ !$change->review->incidents_caused ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="font-medium">Incidents Caused</p>
                        <p>{{ $change->review->incidents_caused ? 'Yes' : 'No' }}</p>
                    </div>
                </div>
                <div class="p-3 rounded-lg {{ $change->review->overall_rating === 'successful' ? 'bg-green-50' : ($change->review->overall_rating === 'failed' ? 'bg-red-50' : 'bg-yellow-50') }}">
                    <p class="font-medium">Overall: {{ ucfirst(str_replace('_', ' ', $change->review->overall_rating)) }}</p>
                </div>
                @if($change->review->lessons_learned)
                <div><p class="font-medium text-gray-700">Lessons Learned</p><p class="text-gray-600 whitespace-pre-wrap">{{ $change->review->lessons_learned }}</p></div>
                @endif
                @if($change->review->improvement_actions)
                <div><p class="font-medium text-gray-700">Improvement Actions</p><p class="text-gray-600 whitespace-pre-wrap">{{ $change->review->improvement_actions }}</p></div>
                @endif
                <p class="text-xs text-gray-400">Reviewed by {{ $change->review->reviewer?->name }} on {{ $change->review->created_at->format('M d, Y') }}</p>
            </div>
            @else
            <form method="POST" action="{{ route('staff.changes.review', $change) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach(['objectives_met' => 'Objectives Met', 'on_schedule' => 'On Schedule', 'within_budget' => 'Within Budget'] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <select name="{{ $field }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    @endforeach
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Incidents Caused</label>
                        <select name="incidents_caused" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Incident Details (if any)</label>
                    <textarea name="incidents_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Overall Rating</label>
                    <select name="overall_rating" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="successful">Successful</option>
                        <option value="partially_successful">Partially Successful</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lessons Learned</label>
                    <textarea name="lessons_learned" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Improvement Actions</label>
                    <textarea name="improvement_actions" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                </div>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit Review</button>
            </form>
            @endif
        </div>
        @endif

        <!-- Conversation Thread -->
        @if($change->ticket?->threads->count())
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Discussion</h3>
            <div class="space-y-3">
                @foreach($change->ticket->threads as $thread)
                <div class="p-3 rounded-lg bg-gray-50">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-900">{{ $thread->user?->name ?? 'System' }}</span>
                        <span class="text-gray-500">{{ $thread->created_at->format('M d H:i') }}</span>
                    </div>
                    <p class="text-sm text-gray-700">{!! nl2br(e($thread->body)) !!}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Actions -->
        <div class="bg-white shadow rounded-lg p-5 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Actions</h3>

            @if($change->status === 'draft')
            <div class="flex gap-2">
                <a href="{{ route('staff.changes.edit', $change) }}" class="flex-1 text-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Edit</a>
                <form method="POST" action="{{ route('staff.changes.submit', $change) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit for Review</button>
                </form>
            </div>
            @endif

            @if(in_array($change->status, ['submitted', 'under_review']))
            <div class="space-y-2">
                <p class="text-xs text-gray-500">Approval Progress: {{ $change->current_approval_level }}/{{ $change->approval_level_required }}</p>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $change->approval_level_required > 0 ? ($change->current_approval_level / $change->approval_level_required * 100) : 0 }}%"></div>
                </div>

                <form method="POST" action="{{ route('staff.changes.approve', $change) }}" class="space-y-2">
                    @csrf @method('PATCH')
                    <textarea name="comments" placeholder="Approval comments (optional)..." rows="2" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                    <button type="submit" class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">Approve</button>
                </form>
                <form method="POST" action="{{ route('staff.changes.reject', $change) }}" class="space-y-2">
                    @csrf @method('PATCH')
                    <textarea name="reason" required placeholder="Reason for rejection..." rows="2" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                    <button type="submit" class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Reject</button>
                </form>
            </div>
            @endif

            @if($change->status === 'approved')
            <form method="POST" action="{{ route('staff.changes.start-implementation', $change) }}">
                @csrf @method('PATCH')
                <button type="submit" class="w-full rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white hover:bg-purple-500">Start Implementation</button>
            </form>
            @endif

            @if($change->status === 'implementing')
            <form method="POST" action="{{ route('staff.changes.complete-implementation', $change) }}" class="space-y-2">
                @csrf @method('PATCH')
                <textarea name="post_implementation_notes" placeholder="Implementation notes..." rows="3" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                <div class="grid grid-cols-2 gap-2">
                    <button type="submit" name="outcome" value="completed" class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">Mark Completed</button>
                    <button type="submit" name="outcome" value="failed" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Mark Failed</button>
                </div>
            </form>
            @endif

            @if($change->status === 'rejected')
            <a href="{{ route('staff.changes.edit', $change) }}" class="block text-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit & Resubmit</a>
            @endif

            @if($change->organization)
            <a href="{{ route('staff.changes.policy', $change->organization) }}" class="block text-center text-sm text-indigo-600 hover:underline mt-2">Manage Org Change Policy</a>
            @endif
        </div>

        <!-- Details -->
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Organization</dt><dd class="font-medium">{{ $change->organization?->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd>{{ $change->category?->name ?? 'None' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Requester</dt><dd>{{ $change->requester_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd>{{ ucfirst($change->type) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Risk Level</dt><dd>@include('components.priority-badge', ['priority' => $change->risk_level])</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">CAB Required</dt><dd>{{ $change->cab_required ? 'Yes' : 'No' }}</dd></div>
                @if($change->scheduled_start_at)
                <div class="flex justify-between"><dt class="text-gray-500">Scheduled</dt><dd>{{ $change->scheduled_start_at->format('M d H:i') }}</dd></div>
                @endif
                @if($change->scheduled_end_at)
                <div class="flex justify-between"><dt class="text-gray-500">Sched. End</dt><dd>{{ $change->scheduled_end_at->format('M d H:i') }}</dd></div>
                @endif
                @if($change->actual_start_at)
                <div class="flex justify-between"><dt class="text-gray-500">Actual Start</dt><dd>{{ $change->actual_start_at->format('M d H:i') }}</dd></div>
                @endif
                @if($change->actual_end_at)
                <div class="flex justify-between"><dt class="text-gray-500">Actual End</dt><dd>{{ $change->actual_end_at->format('M d H:i') }}</dd></div>
                @endif
                @if($change->approvedBy)
                <div class="flex justify-between"><dt class="text-gray-500">Approved By</dt><dd>{{ $change->approvedBy->name }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Created</dt><dd>{{ $change->created_at->format('M d, Y') }}</dd></div>
            </dl>
        </div>

        <!-- CAB Members -->
        @if($cabMembers->count())
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">CAB Members</h3>
            <ul class="space-y-2">
                @foreach($cabMembers as $member)
                <li class="flex justify-between text-sm">
                    <span class="text-gray-900">{{ $member->user?->name }}</span>
                    <span class="text-xs text-gray-500 capitalize">{{ $member->role }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
