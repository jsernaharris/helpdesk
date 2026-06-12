@extends('layouts.portal')
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
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Projects</h1>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($projects as $project)
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('portal.projects.show', $project) }}'">
                    <td class="px-4 py-3 text-sm">
                        <span class="font-medium text-indigo-600">{{ $project->project_number }}</span>
                        <span class="text-gray-900 ml-2">{{ $project->name }}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-block text-xs rounded px-2 py-0.5 {{ $statusStyles[$project->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->start_date?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->due_date?->format('M d, Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
</div>
@endsection
