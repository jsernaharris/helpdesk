@extends('layouts.staff')
@section('title', 'Organizations')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('staff.organizations.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Organization</a>
</div>
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Domain</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tickets</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($organizations as $org)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.organizations.show', $org) }}'">
                <td class="px-4 py-3 text-sm font-medium text-indigo-600">{{ $org->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $org->email_domain ?? '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $org->users_count }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $org->tickets_count }}</td>
                <td class="px-4 py-3"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $org->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $org->is_active ? 'Active' : 'Inactive' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $organizations->links() }}</div>
</div>
@endsection
