@extends('layouts.staff')
@section('title', 'New Role')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.roles.store') }}" class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Role name</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. site_a_tech"
                   class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and underscores only.</p>
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        @include('staff.roles._permissions')

        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.roles.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Role</button>
        </div>
    </form>
</div>
@endsection
