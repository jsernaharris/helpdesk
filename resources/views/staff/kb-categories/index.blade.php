@extends('layouts.staff')
@section('title', 'KB Categories')

@section('content')
@if(session('success'))
<div class="rounded-md bg-green-50 p-3 mb-4 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="rounded-md bg-red-50 p-3 mb-4 text-sm text-red-800">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="rounded-md bg-red-50 p-3 mb-4 text-sm text-red-800">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h1 class="text-lg font-semibold text-gray-900">Knowledge Base Categories</h1>
    <a href="{{ route('staff.kb.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to Articles</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                <th class="px-4 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categories as $cat)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $cat->slug }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $cat->parent?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $cat->articles_count }}</td>
                    <td class="px-4 py-3">
                        @if($cat->is_active)<span class="text-green-600">Active</span>@else<span class="text-gray-400">Hidden</span>@endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('staff.kb-categories.edit', $cat) }}" class="text-xs text-indigo-600 hover:underline mr-3">Edit</a>
                        <form method="POST" action="{{ route('staff.kb-categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No categories yet — add one on the right.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white shadow rounded-lg p-5">
        <h3 class="font-semibold text-gray-900 mb-3">Add Category</h3>
        <form method="POST" action="{{ route('staff.kb-categories.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Slug <span class="text-gray-400">(optional)</span></label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto from name" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Parent</label>
                <select name="parent_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">— None (top level) —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('parent_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Organization <span class="text-gray-400">(optional)</span></label>
                <select name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">All organizations (global)</option>
                    @foreach($organizations as $org)
                    <option value="{{ $org->id }}" @selected(old('organization_id') == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-24">
                    <label class="block text-sm font-medium text-gray-700">Sort</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-5">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Active
                </label>
            </div>
            <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Category</button>
        </form>
    </div>
</div>
@endsection
