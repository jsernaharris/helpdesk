@extends('layouts.staff')
@section('title', 'Create Problem Record')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('staff.problems.store') }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        <div><label class="block text-sm font-medium text-gray-700">Organization</label>
            <select name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="">Select</option>
                @foreach(\App\Models\Organization::where('is_active', true)->orderBy('name')->get() as $org)
                <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-sm font-medium text-gray-700">Subject</label><input type="text" name="subject" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Description</label><textarea name="description" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.problems.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create</button>
        </div>
    </form>
</div>
@endsection
