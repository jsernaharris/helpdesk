@extends('layouts.portal')
@section('title', 'Request a Change')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Submit a Change Request</h2>
            <p class="mt-1 text-sm text-gray-500">Describe the change you need. Our team will review and schedule it according to your organization's change management policy.</p>
        </div>

        <form method="POST" action="{{ route('portal.changes.store') }}" class="px-6 py-5 space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Change Type</label>
                    <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="standard">Standard (routine, pre-approved)</option>
                        <option value="normal" selected>Normal (requires review & approval)</option>
                        <option value="emergency">Emergency (critical, expedited)</option>
                    </select>
                </div>
                @if($categories->count())
                <div>
                    <label for="change_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="change_category_id" id="change_category_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Select category --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Brief summary of the change">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Describe what you need changed and why.">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="business_justification" class="block text-sm font-medium text-gray-700">Business Justification <span class="text-red-500">*</span></label>
                <textarea name="business_justification" id="business_justification" rows="3" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Why is this change necessary? What business need does it address?">{{ old('business_justification') }}</textarea>
            </div>

            <div>
                <label for="impact_assessment" class="block text-sm font-medium text-gray-700">Impact Assessment</label>
                <textarea name="impact_assessment" id="impact_assessment" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Who will be affected? Any expected downtime?">{{ old('impact_assessment') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="scheduled_start_at" class="block text-sm font-medium text-gray-700">Preferred Start Date/Time</label>
                    <input type="datetime-local" name="scheduled_start_at" id="scheduled_start_at"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="scheduled_end_at" class="block text-sm font-medium text-gray-700">Preferred End Date/Time</label>
                    <input type="datetime-local" name="scheduled_end_at" id="scheduled_end_at"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            @if($policy->change_window_notes)
            <div class="rounded-md bg-blue-50 p-3">
                <p class="text-sm text-blue-700"><strong>Note:</strong> {{ $policy->change_window_notes }}</p>
            </div>
            @endif

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                <a href="{{ route('portal.changes.index') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit Change Request</button>
            </div>
        </form>
    </div>
</div>
@endsection
