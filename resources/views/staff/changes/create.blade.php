@extends('layouts.staff')
@section('title', 'New Change Request')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.changes.store') }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">Organization</label>
                <select name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">Select</option>
                    @foreach(\App\Models\Organization::where('is_active', true)->orderBy('name')->get() as $org)
                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="standard">Standard</option><option value="normal">Normal</option><option value="emergency">Emergency</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Risk Level</label>
                    <select name="risk_level" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option>
                    </select>
                </div>
            </div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700">Subject</label><input type="text" name="subject" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Description</label><textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Implementation Plan</label><textarea name="implementation_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Rollback Plan</label><textarea name="rollback_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Test Plan</label><textarea name="test_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">Scheduled Start</label><input type="datetime-local" name="scheduled_start_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">Scheduled End</label><input type="datetime-local" name="scheduled_end_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        </div>
        <label class="flex items-center gap-2"><input type="checkbox" name="cab_required" value="1" class="rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">CAB Review Required</span></label>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.changes.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Change Request</button>
        </div>
    </form>
</div>
@endsection
