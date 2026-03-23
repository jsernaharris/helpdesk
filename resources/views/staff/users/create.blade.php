@extends('layouts.staff')
@section('title', 'Add User')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('staff.users.store') }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Email</label><input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Organization</label>
            <select name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                @foreach($organizations as $org)<option value="{{ $org->id }}">{{ $org->name }}</option>@endforeach
            </select></div>
        <div><label class="block text-sm font-medium text-gray-700">Role</label>
            <select name="role" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                @foreach($roles as $role)<option value="{{ $role->name }}">{{ ucfirst(str_replace('_',' ',$role->name)) }}</option>@endforeach
            </select></div>
        <div><label class="block text-sm font-medium text-gray-700">Password</label><input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Confirm Password</label><input type="password" name="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.users.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create User</button>
        </div>
    </form>
</div>
@endsection
