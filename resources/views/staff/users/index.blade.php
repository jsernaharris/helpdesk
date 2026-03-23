@extends('layouts.staff')
@section('title', 'Users')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('staff.users.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add User</a>
</div>
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($users as $user)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.users.show', $user) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $user->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $user->organization?->name }}</td>
                <td class="px-4 py-3"><span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">{{ $user->roles->first()?->name ?? 'None' }}</span></td>
                <td class="px-4 py-3"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $users->links() }}</div>
</div>
@endsection
