@extends('layouts.staff')
@section('title', 'Edit: ' . $change->change_number)

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.changes.update', $change) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf @method('PUT')

        <div class="rounded-md bg-blue-50 p-3 mb-2">
            <p class="text-sm text-blue-700">{{ $change->change_number }} &middot; {{ $change->organization?->name }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input type="text" name="subject" value="{{ old('subject', $change->ticket?->subject) }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    @foreach(['standard','normal','emergency'] as $t)
                    <option value="{{ $t }}" @selected($change->type === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Risk Level</label>
                <select name="risk_level" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    @foreach(['low','medium','high','critical'] as $r)
                    <option value="{{ $r }}" @selected($change->risk_level === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div><label class="block text-sm font-medium text-gray-700">Business Justification</label><textarea name="business_justification" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $change->business_justification }}</textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Impact Assessment</label><textarea name="impact_assessment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $change->impact_assessment }}</textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Implementation Plan</label><textarea name="implementation_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $change->implementation_plan }}</textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Rollback Plan</label><textarea name="rollback_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $change->rollback_plan }}</textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Test Plan</label><textarea name="test_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $change->test_plan }}</textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Communication Plan</label><textarea name="communication_plan" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $change->communication_plan }}</textarea></div>

        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">Scheduled Start</label><input type="datetime-local" name="scheduled_start_at" value="{{ $change->scheduled_start_at?->format('Y-m-d\\TH:i') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">Scheduled End</label><input type="datetime-local" name="scheduled_end_at" value="{{ $change->scheduled_end_at?->format('Y-m-d\\TH:i') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.changes.show', $change) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
        </div>
    </form>
</div>
@endsection
