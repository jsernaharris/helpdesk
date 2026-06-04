@extends('layouts.staff')
@section('title', 'Form Templates')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Manage custom form templates for ticket submissions.</p>
    <a href="{{ route('staff.form-templates.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New Template</a>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fields</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tickets</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($templates as $template)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('staff.form-templates.show', $template) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ $template->name }}</a>
                    @if($template->description)
                    <p class="text-xs text-gray-500 truncate max-w-xs">{{ $template->description }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $template->organization?->name ?? 'All Organizations' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ count($template->fields) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $template->tickets_count }}</td>
                <td class="px-4 py-3">
                    @if($template->is_active)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Active</span>
                    @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('staff.form-templates.edit', $template) }}" class="text-sm text-indigo-600 hover:text-indigo-500">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No form templates yet. Create one to get started.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $templates->links() }}</div>
@endsection
