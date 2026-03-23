@extends('layouts.staff')
@section('title', 'New Change Request')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.changes.store') }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Organization</label>
                <select name="organization_id" id="org-select" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" onchange="this.form.action='{{ route('staff.changes.create') }}'; this.form.method='GET'; this.form.submit();">
                    <option value="">Select</option>
                    @foreach($organizations as $org)
                    <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Change Category</label>
                <select name="change_category_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">-- None / Custom --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" data-type="{{ $cat->default_type }}" data-risk="{{ $cat->default_risk_level }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="standard">Standard (pre-approved, low risk)</option>
                    <option value="normal" selected>Normal (requires approval)</option>
                    <option value="emergency">Emergency (expedited)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Risk Level</label>
                <select name="risk_level" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Business Justification</label>
            <textarea name="business_justification" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('business_justification') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Impact Assessment</label>
            <textarea name="impact_assessment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="Who and what will be affected by this change?">{{ old('impact_assessment') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Implementation Plan</label>
            <textarea name="implementation_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('implementation_plan') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Rollback Plan</label>
            <textarea name="rollback_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('rollback_plan') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Test Plan</label>
            <textarea name="test_plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('test_plan') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Communication Plan</label>
            <textarea name="communication_plan" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="How will stakeholders be notified?">{{ old('communication_plan') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Scheduled Start</label>
                <input type="datetime-local" name="scheduled_start_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Scheduled End</label>
                <input type="datetime-local" name="scheduled_end_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.changes.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Change Request</button>
        </div>
    </form>
</div>
@endsection
