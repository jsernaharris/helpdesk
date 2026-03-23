@extends('layouts.portal')
@section('title', $change->change_number)

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold text-gray-900">{{ $change->ticket?->subject }}</h2>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $change->type === 'emergency' ? 'bg-red-100 text-red-800' : ($change->type === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($change->type) }}</span>
                @include('components.status-badge', ['status' => $change->status])
            </div>
        </div>
        <div class="text-sm text-gray-500 flex items-center gap-4">
            <span>{{ $change->change_number }}</span>
            <span>Submitted {{ $change->submitted_at?->format('M d, Y') ?? $change->created_at->format('M d, Y') }}</span>
            @if($change->category)<span>{{ $change->category->name }}</span>@endif
        </div>

        <!-- Progress -->
        @php
            $steps = ['submitted', 'under_review', 'approved', 'implementing', 'completed'];
            $currentIdx = array_search($change->status, $steps);
            if ($currentIdx === false) $currentIdx = -1;
        @endphp
        <div class="mt-4 flex items-center gap-1">
            @foreach($steps as $idx => $step)
            <div class="flex-1 h-2 rounded-full {{ $idx <= $currentIdx ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
            @endforeach
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
            @foreach($steps as $step)
            <span>{{ ucfirst(str_replace('_', ' ', $step)) }}</span>
            @endforeach
        </div>
    </div>

    <!-- Details -->
    <div class="space-y-4 mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Description</h3>
            <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($change->ticket?->description)) !!}</div>
        </div>

        @if($change->business_justification)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Business Justification</h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->business_justification }}</p>
        </div>
        @endif

        @if($change->impact_assessment)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Impact Assessment</h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->impact_assessment }}</p>
        </div>
        @endif

        @if($change->scheduled_start_at)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Schedule</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Scheduled Start</dt><dd class="font-medium">{{ $change->scheduled_start_at->format('M d, Y H:i') }}</dd></div>
                @if($change->scheduled_end_at)<div><dt class="text-gray-500">Scheduled End</dt><dd class="font-medium">{{ $change->scheduled_end_at->format('M d, Y H:i') }}</dd></div>@endif
                @if($change->actual_start_at)<div><dt class="text-gray-500">Actual Start</dt><dd class="font-medium">{{ $change->actual_start_at->format('M d, Y H:i') }}</dd></div>@endif
                @if($change->actual_end_at)<div><dt class="text-gray-500">Actual End</dt><dd class="font-medium">{{ $change->actual_end_at->format('M d, Y H:i') }}</dd></div>@endif
            </dl>
        </div>
        @endif
    </div>

    <!-- Approval Trail -->
    @if($change->approvals->count())
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Approval Status</h3>
        <div class="space-y-2">
            @foreach($change->approvals as $approval)
            <div class="flex items-center gap-3 p-2 rounded-lg {{ $approval->decision === 'approved' ? 'bg-green-50' : ($approval->decision === 'rejected' ? 'bg-red-50' : 'bg-yellow-50') }}">
                @if($approval->decision === 'approved')
                <svg class="h-5 w-5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                @else
                <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                @endif
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($approval->decision) }} by {{ $approval->user?->name }}</p>
                    <p class="text-xs text-gray-500">{{ $approval->created_at->format('M d, Y H:i') }}</p>
                    @if($approval->comments)<p class="text-xs text-gray-600 mt-1">{{ $approval->comments }}</p>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Review -->
    @if($change->review)
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Implementation Review</h3>
        <div class="p-3 rounded-lg {{ $change->review->overall_rating === 'successful' ? 'bg-green-50' : ($change->review->overall_rating === 'failed' ? 'bg-red-50' : 'bg-yellow-50') }}">
            <p class="text-sm font-medium">Result: {{ ucfirst(str_replace('_', ' ', $change->review->overall_rating)) }}</p>
        </div>
        @if($change->review->lessons_learned)
        <div class="mt-3"><p class="text-xs text-gray-500">Lessons Learned</p><p class="text-sm text-gray-700">{{ $change->review->lessons_learned }}</p></div>
        @endif
    </div>
    @endif
</div>
@endsection
