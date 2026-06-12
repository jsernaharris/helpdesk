@extends('layouts.staff')
@section('title', 'Projects')

@php
$statusStyles = [
    'planned' => 'bg-gray-100 text-gray-700',
    'active' => 'bg-green-100 text-green-700',
    'on_hold' => 'bg-yellow-100 text-yellow-700',
    'completed' => 'bg-blue-100 text-blue-700',
    'cancelled' => 'bg-red-100 text-red-700',
];
@endphp

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div></div>
    <div class="flex gap-3">
        @can('time.view_all')
        <a href="{{ route('staff.projects.time.export', request()->query()) }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Export Time CSV</a>
        @endcan
        @can('projects.create')
        <a href="{{ route('staff.projects.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New Project</a>
        @endcan
    </div>
</div>

<div class="bg-white shadow rounded-lg mb-6 p-4">
    <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search #/name..." class="rounded-md border-gray-300 text-sm px-3 py-2 border">
        <select name="status" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Statuses</option>
            @foreach(['planned','active','on_hold','completed','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="organization_id" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Organizations</option>
            @foreach($organizations as $org)
            <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
            @endforeach
        </select>
        <select name="assigned" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Projects</option>
            <option value="me" @selected(request('assigned') === 'me')>Assigned to Me</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
    </form>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Members</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.projects.show', $project) }}'">
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span class="font-medium text-indigo-600">{{ $project->project_number }}</span>
                    <span class="text-gray-900 ml-2">{{ $project->name }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->organization?->name }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->members->count() }}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-block text-xs rounded px-2 py-0.5 {{ $statusStyles[$project->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->totalHours() }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->due_date?->format('M d, Y') ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No projects found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $projects->links() }}
    </div>
</div>
@endsection
