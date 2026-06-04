@extends('layouts.staff')
@section('title', 'Edit ' . ucfirst(str_replace('_', ' ', $role->name)))

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.roles.update', $role) }}" class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Role name</label>
            @if($isProtected)
            <input type="text" value="{{ ucfirst(str_replace('_', ' ', $role->name)) }}" disabled
                   class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 text-sm px-3 py-2 border text-gray-500">
            <p class="mt-1 text-xs text-gray-500">This is a built-in role; its name is fixed, but you can adjust its permissions.</p>
            @else
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and underscores only.</p>
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            @endif
        </div>

        @include('staff.roles._permissions')

        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.roles.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
        </div>
    </form>
</div>
@endsection
