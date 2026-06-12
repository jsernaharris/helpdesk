@extends('layouts.portal')
@section('title', $project->project_number)

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
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('portal.projects.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to Projects</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-gray-900">{{ $project->name }}</h1>
            <span class="inline-block text-xs rounded px-2 py-0.5 {{ $statusStyles[$project->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
        </div>
        <p class="text-sm text-gray-500">{{ $project->project_number }}</p>

        @if($project->description)
        <p class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $project->description }}</p>
        @endif

        <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Start Date</dt><dd class="text-gray-900">{{ $project->start_date?->format('M d, Y') ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Due Date</dt><dd class="text-gray-900">{{ $project->due_date?->format('M d, Y') ?? '—' }}</dd></div>
        </dl>

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Assigned Team</h3>
            <ul class="flex flex-wrap gap-2">
                @forelse($project->members as $member)
                <li class="inline-block bg-gray-100 text-gray-700 text-xs rounded px-2 py-1">{{ $member->name }}</li>
                @empty
                <li class="text-sm text-gray-500">Not yet assigned.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mt-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Project Updates</h3>
        <ul class="space-y-4">
            @forelse($ledger as $entry)
            <li class="flex gap-3">
                <div class="mt-1 h-2 w-2 rounded-full bg-indigo-400 shrink-0"></div>
                <div>
                    <p class="text-sm text-gray-800">{{ $entry->description }}</p>
                    <p class="text-xs text-gray-400">{{ $entry->created_at->format('M d, Y') }}</p>
                </div>
            </li>
            @empty
            <li class="text-sm text-gray-500">No updates yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
