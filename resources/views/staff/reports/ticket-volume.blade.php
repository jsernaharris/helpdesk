@extends('layouts.staff')
@section('title', 'Ticket Volume Report')

@section('content')
<form method="GET" class="flex gap-3 mb-6">
    <input type="date" name="date_from" value="{{ $dateFrom }}" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
    <input type="date" name="date_to" value="{{ $dateTo }}" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
    <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Apply</button>
</form>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">By Source</h3>
        <dl class="space-y-2">
            @foreach($bySource as $row)
            <div class="flex justify-between text-sm"><dt class="text-gray-600">{{ ucfirst($row->source) }}</dt><dd class="font-semibold">{{ $row->count }}</dd></div>
            @endforeach
        </dl>
    </div>
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">By Priority</h3>
        <dl class="space-y-2">
            @foreach($byPriority as $row)
            <div class="flex justify-between text-sm"><dt>@include('components.priority-badge', ['priority' => $row->priority])</dt><dd class="font-semibold">{{ $row->count }}</dd></div>
            @endforeach
        </dl>
    </div>
</div>
@endsection
