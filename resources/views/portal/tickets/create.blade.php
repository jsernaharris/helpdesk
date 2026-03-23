@extends('layouts.portal')
@section('title', 'Submit a Ticket')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Submit a Support Request</h2>
            <p class="mt-1 text-sm text-gray-500">Please fill out the form below and we'll get back to you as soon as possible.</p>
        </div>

        <form method="POST" action="{{ route('portal.tickets.store') }}" enctype="multipart/form-data" class="px-6 py-5 space-y-5">
            @csrf

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Request Type</label>
                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="incident" @selected(old('type') === 'incident')>Report an Issue / Incident</option>
                    <option value="service_request" @selected(old('type') === 'service_request')>Service Request</option>
                </select>
            </div>

            @if($services->count())
            <div>
                <label for="service_catalog_id" class="block text-sm font-medium text-gray-700">Service Category</label>
                <select name="service_catalog_id" id="service_catalog_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Select a service (optional) --</option>
                    @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Brief summary of your issue or request">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="6" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Please provide as much detail as possible. Include steps to reproduce, error messages, and any other relevant information.">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="impact" class="block text-sm font-medium text-gray-700">Impact</label>
                    <select name="impact" id="impact" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="minor">Minor - Affects only me</option>
                        <option value="moderate" selected>Moderate - Affects a few people</option>
                        <option value="significant">Significant - Affects a department</option>
                        <option value="extensive">Extensive - Affects the entire company</option>
                    </select>
                </div>
                <div>
                    <label for="urgency" class="block text-sm font-medium text-gray-700">Urgency</label>
                    <select name="urgency" id="urgency" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="low">Low - When you get a chance</option>
                        <option value="medium" selected>Medium - Within normal timeframes</option>
                        <option value="high">High - Needs attention soon</option>
                        <option value="critical">Critical - Cannot work at all</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Attachments</label>
                <div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pt-5 pb-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
                        <div class="mt-2 flex text-sm text-gray-600">
                            <label for="attachments" class="cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                <span>Upload files</span>
                                <input id="attachments" name="attachments[]" type="file" class="sr-only" multiple>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Screenshots, logs, documents up to 25MB each</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                <a href="{{ route('portal.tickets.index') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection
