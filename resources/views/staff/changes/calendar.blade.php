@extends('layouts.staff')
@section('title', 'Change Calendar')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('staff.changes.calendar', ['month' => $start->copy()->subMonth()->format('Y-m')]) }}" class="text-gray-500 hover:text-gray-700">&larr; Prev</a>
        <h2 class="text-lg font-semibold text-gray-900">{{ $start->format('F Y') }}</h2>
        <a href="{{ route('staff.changes.calendar', ['month' => $start->copy()->addMonth()->format('Y-m')]) }}" class="text-gray-500 hover:text-gray-700">Next &rarr;</a>
    </div>
    <a href="{{ route('staff.changes.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Change</a>
</div>

<!-- Blackout Periods -->
@if($blackouts->count())
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-2">Active Blackout Periods</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($blackouts as $blackout)
        <span class="inline-flex items-center rounded-md bg-red-50 border border-red-200 px-3 py-1.5 text-xs text-red-800">
            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
            {{ $blackout->name }}: {{ $blackout->starts_at->format('M d') }} - {{ $blackout->ends_at->format('M d') }}
            ({{ $blackout->organization?->name }})
            @if($blackout->allow_emergency) <span class="ml-1 text-gray-500">(emergency OK)</span> @endif
        </span>
        @endforeach
    </div>
</div>
@endif

<!-- Calendar Grid -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="grid grid-cols-7 bg-gray-50 border-b">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
        <div class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ $day }}</div>
        @endforeach
    </div>
    <div class="grid grid-cols-7 divide-x divide-y divide-gray-200">
        @php
            $calStart = $start->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $calEnd = $end->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
            $current = $calStart->copy();
        @endphp
        @while($current <= $calEnd)
        @php
            $isCurrentMonth = $current->month === $start->month;
            $isToday = $current->isToday();
            $dayChanges = $changes->filter(function ($c) use ($current) {
                return $c->scheduled_start_at && $c->scheduled_start_at->isSameDay($current);
            });
            $dayBlackouts = $blackouts->filter(function ($b) use ($current) {
                return $current->between($b->starts_at->startOfDay(), $b->ends_at->endOfDay());
            });
        @endphp
        <div class="min-h-[100px] p-2 {{ !$isCurrentMonth ? 'bg-gray-50' : '' }} {{ $dayBlackouts->count() ? 'bg-red-50' : '' }}">
            <p class="text-sm {{ $isToday ? 'font-bold text-indigo-600' : ($isCurrentMonth ? 'text-gray-900' : 'text-gray-400') }}">{{ $current->day }}</p>
            @foreach($dayChanges->take(3) as $c)
            <a href="{{ route('staff.changes.show', $c) }}" class="block mt-1 px-1.5 py-0.5 rounded text-xs truncate {{ $c->type === 'emergency' ? 'bg-red-100 text-red-700' : ($c->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700') }}">
                {{ $c->change_number }}
            </a>
            @endforeach
            @if($dayChanges->count() > 3)
            <p class="text-xs text-gray-500 mt-1">+{{ $dayChanges->count() - 3 }} more</p>
            @endif
        </div>
        @php $current->addDay(); @endphp
        @endwhile
    </div>
</div>

<!-- Scheduled Changes List -->
<div class="mt-6 bg-white shadow rounded-lg p-6">
    <h3 class="font-semibold text-gray-900 mb-3">Scheduled Changes This Month</h3>
    <div class="space-y-2">
        @forelse($changes->sortBy('scheduled_start_at') as $change)
        <a href="{{ route('staff.changes.show', $change) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border">
            <div>
                <span class="text-sm font-medium text-indigo-600">{{ $change->change_number }}</span>
                <span class="text-sm text-gray-900 ml-2">{{ $change->ticket?->subject }}</span>
                <span class="text-xs text-gray-500 ml-2">{{ $change->organization?->name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">{{ $change->scheduled_start_at->format('M d, H:i') }}</span>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $change->type === 'emergency' ? 'bg-red-100 text-red-800' : ($change->type === 'normal' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($change->type) }}</span>
                @include('components.status-badge', ['status' => $change->status])
            </div>
        </a>
        @empty
        <p class="text-sm text-gray-500">No scheduled changes this month.</p>
        @endforelse
    </div>
</div>
@endsection
