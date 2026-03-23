@extends('layouts.staff')
@section('title', 'Problem: ' . $problem->ticket->ticket_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-start mb-4">
                <div><h2 class="text-lg font-semibold text-gray-900">{{ $problem->ticket->subject }}</h2><p class="text-sm text-gray-500">{{ $problem->ticket->ticket_number }} &middot; {{ $problem->ticket->organization?->name }}</p></div>
                @include('components.status-badge', ['status' => $problem->status])
            </div>
            <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($problem->ticket->description)) !!}</div>
        </div>

        <form method="POST" action="{{ route('staff.problems.update', $problem) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-sm font-medium text-gray-700">Root Cause</label><textarea name="root_cause" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $problem->root_cause }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700">Workaround</label><textarea name="workaround" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $problem->workaround }}</textarea></div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2"><input type="checkbox" name="known_error" value="1" @checked($problem->known_error) class="rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">Known Error</span></label>
                <select name="status" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                    @foreach(['open','investigating','root_cause_identified','resolved','closed'] as $s)
                    <option value="{{ $s }}" @selected($problem->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
            </div>
        </form>
    </div>
    <div>
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Linked Incidents ({{ $problem->incidents->count() }})</h3>
            <ul class="space-y-2">
                @foreach($problem->incidents as $incident)
                <li><a href="{{ route('staff.tickets.show', $incident) }}" class="text-sm text-indigo-600 hover:underline">{{ $incident->ticket_number }} - {{ $incident->subject }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
