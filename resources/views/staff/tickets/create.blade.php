@extends('layouts.staff')
@section('title', 'Create Ticket')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="bg-white shadow rounded-lg p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Organization</label>
                    <select name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="">Select organization</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}" @selected(old('organization_id') == $org->id)>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Requester Email</label>
                    <input type="email" name="requester_email" value="{{ old('requester_email') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="user@company.com">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="incident" @selected(old('type') === 'incident')>Incident</option>
                        <option value="service_request" @selected(old('type') === 'service_request')>Service Request</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Impact</label>
                    <select name="impact" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        @foreach(['minor','moderate','significant','extensive'] as $i)
                        <option value="{{ $i }}" @selected(old('impact', 'moderate') === $i)>{{ ucfirst($i) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Urgency</label>
                    <select name="urgency" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        @foreach(['low','medium','high','critical'] as $u)
                        <option value="{{ $u }}" @selected(old('urgency', 'medium') === $u)>{{ ucfirst($u) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Source</label>
                    <select name="source" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        @foreach(['portal','email','phone','chat','api','monitoring'] as $src)
                        <option value="{{ $src }}" @selected(old('source', 'portal') === $src)>{{ ucfirst($src) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Assign to Technician</label>
                    <select name="assigned_to_user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="">Unassigned</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Assign to Team</label>
                    <select name="assigned_to_team_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="">None</option>
                        @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Service</label>
                <select name="service_catalog_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">None</option>
                    @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Attachments</label>
                <input type="file" name="attachments[]" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.tickets.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Ticket</button>
        </div>
    </form>
</div>
@endsection