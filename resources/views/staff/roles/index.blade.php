@extends('layouts.staff')
@section('title', 'Roles & Permissions')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Define roles and the permissions they grant. Assign roles to users from the Users screen.</p>
    <a href="{{ route('staff.roles.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Role</a>
</div>

@if(session('error'))
<div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
@endif

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($roles as $role)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">
                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    @if(in_array($role->name, $protected))
                    <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">System</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-500">{{ $role->permissions_count }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $role->users_count }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('staff.roles.edit', $role) }}" class="text-indigo-600 hover:underline">Edit</a>
                    @unless(in_array($role->name, $protected))
                    <form method="POST" action="{{ route('staff.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Delete the {{ $role->name }} role? Users will lose this role.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ml-3 text-red-600 hover:underline">Delete</button>
                    </form>
                    @endunless
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
