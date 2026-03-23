@extends('layouts.staff')
@section('title', 'Edit ' . $user->name)

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('staff.users.update', $user) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf @method('PUT')
        <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" value="{{ $user->name }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Email</label><input type="email" name="email" value="{{ $user->email }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Organization</label>
            <select name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                @foreach($organizations as $org)<option value="{{ $org->id }}" @selected($user->organization_id == $org->id)>{{ $org->name }}</option>@endforeach
            </select></div>
        <div><label class="block text-sm font-medium text-gray-700">Role</label>
            <select name="role" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                @foreach($roles as $role)<option value="{{ $role->name }}" @selected($user->hasRole($role->name))>{{ ucfirst(str_replace('_',' ',$role->name)) }}</option>@endforeach
            </select></div>
        <div><label class="block text-sm font-medium text-gray-700">New Password (leave blank to keep)</label><input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Confirm Password</label><input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="rounded border-gray-300 text-indigo-600"><label class="text-sm text-gray-700">Active</label></div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.users.show', $user) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
        </div>
    </form>
</div>
@endsection
