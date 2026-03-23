@extends('layouts.staff')
@section('title', 'Change: ' . $change->change_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-start mb-4">
                <div><h2 class="text-lg font-semibold text-gray-900">{{ $change->ticket->subject }}</h2><p class="text-sm text-gray-500">{{ $change->change_number }} &middot; {{ $change->ticket->organization?->name }}</p></div>
                <div class="flex gap-2">@include('components.status-badge', ['status' => $change->status]) @include('components.priority-badge', ['priority' => $change->risk_level])</div>
            </div>
            <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($change->ticket->description)) !!}</div>
        </div>

        @if($change->implementation_plan)
        <div class="bg-white shadow rounded-lg p-6"><h3 class="font-semibold text-gray-900 mb-2">Implementation Plan</h3><div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->implementation_plan }}</div></div>
        @endif
        @if($change->rollback_plan)
        <div class="bg-white shadow rounded-lg p-6"><h3 class="font-semibold text-gray-900 mb-2">Rollback Plan</h3><div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->rollback_plan }}</div></div>
        @endif
        @if($change->test_plan)
        <div class="bg-white shadow rounded-lg p-6"><h3 class="font-semibold text-gray-900 mb-2">Test Plan</h3><div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->test_plan }}</div></div>
        @endif
    </div>
    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-5">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="font-medium">{{ ucfirst($change->type) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Risk</dt><dd>@include('components.priority-badge', ['priority' => $change->risk_level])</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">CAB Required</dt><dd>{{ $change->cab_required ? 'Yes' : 'No' }}</dd></div>
                @if($change->scheduled_start_at)<div class="flex justify-between"><dt class="text-gray-500">Scheduled</dt><dd>{{ $change->scheduled_start_at->format('M d H:i') }} - {{ $change->scheduled_end_at?->format('M d H:i') }}</dd></div>@endif
                @if($change->approvedBy)<div class="flex justify-between"><dt class="text-gray-500">Approved By</dt><dd>{{ $change->approvedBy->name }}</dd></div>@endif
            </dl>
        </div>

        @if(in_array($change->status, ['submitted', 'under_review']))
        <div class="bg-white shadow rounded-lg p-5 space-y-3">
            <h3 class="font-semibold text-gray-900">Approval</h3>
            <form method="POST" action="{{ route('staff.changes.approve', $change) }}">@csrf @method('PATCH')
                <button type="submit" class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">Approve</button>
            </form>
            <form method="POST" action="{{ route('staff.changes.reject', $change) }}">@csrf @method('PATCH')
                <textarea name="reason" required placeholder="Reason for rejection..." class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border mb-2"></textarea>
                <button type="submit" class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Reject</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
