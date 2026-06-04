@extends('layouts.staff')
@section('title', $formTemplate->name)

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500">{{ $formTemplate->description ?? 'No description' }}</p>
            <p class="text-xs text-gray-400 mt-1">
                Created by {{ $formTemplate->createdBy?->name ?? 'Unknown' }} &middot;
                {{ $formTemplate->created_at->format('M d, Y') }} &middot;
                @if($formTemplate->organization)
                    {{ $formTemplate->organization->name }}
                @else
                    All Organizations
                @endif
                &middot;
                @if($formTemplate->is_active)
                    <span class="text-green-600">Active</span>
                @else
                    <span class="text-gray-500">Inactive</span>
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('staff.form-templates.edit', $formTemplate) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Edit</a>
            <form method="POST" action="{{ route('staff.form-templates.destroy', $formTemplate) }}" onsubmit="return confirm('Delete this form template?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">Delete</button>
            </form>
        </div>
    </div>

    <!-- Preview -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Form Preview</h3>
        <div class="space-y-4">
            @foreach($formTemplate->fields as $field)
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    {{ $field['label'] }}
                    @if($field['required'] ?? false)
                    <span class="text-red-500">*</span>
                    @endif
                </label>
                <p class="text-xs text-gray-400 mb-1">Type: {{ ucfirst($field['type']) }}@if(!empty($field['placeholder'])) &middot; Placeholder: "{{ $field['placeholder'] }}"@endif</p>

                @if($field['type'] === 'textarea')
                    <textarea disabled rows="3" placeholder="{{ $field['placeholder'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-sm px-3 py-2 border"></textarea>
                @elseif($field['type'] === 'select')
                    <select disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-sm px-3 py-2 border">
                        <option>{{ $field['placeholder'] ?? 'Select...' }}</option>
                        @foreach($field['options'] ?? [] as $option)
                        <option>{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif($field['type'] === 'checkbox')
                    <div class="mt-1 space-y-1">
                        @foreach($field['options'] ?? [] as $option)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" disabled class="rounded border-gray-300">
                            {{ $option }}
                        </label>
                        @endforeach
                    </div>
                @elseif($field['type'] === 'radio')
                    <div class="mt-1 space-y-1">
                        @foreach($field['options'] ?? [] as $option)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" disabled class="border-gray-300">
                            {{ $option }}
                        </label>
                        @endforeach
                    </div>
                @else
                    <input type="{{ $field['type'] }}" disabled placeholder="{{ $field['placeholder'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-sm px-3 py-2 border">
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
