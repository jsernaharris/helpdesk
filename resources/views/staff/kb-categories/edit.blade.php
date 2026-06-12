@extends('layouts.staff')
@section('title', 'Edit Category')

@section('content')
@if($errors->any())
<div class="rounded-md bg-red-50 p-3 mb-4 text-sm text-red-800">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="max-w-xl">
    <div class="mb-4">
        <a href="{{ route('staff.kb-categories.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to Categories</a>
    </div>

    <form method="POST" action="{{ route('staff.kb-categories.update', $category) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            <p class="mt-1 text-xs text-gray-500">Used in portal URLs. Changing it changes the article links.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Parent</label>
                <select name="parent_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">— None (top level) —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('parent_id', $category->parent_id) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Organization</label>
                <select name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">All organizations (global)</option>
                    @foreach($organizations as $org)
                    <option value="{{ $org->id }}" @selected(old('organization_id', $category->organization_id) == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="w-28">
                <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-5">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) class="rounded border-gray-300"> Active
            </label>
        </div>
        <div class="flex justify-end gap-3 border-t pt-4">
            <a href="{{ route('staff.kb-categories.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
        </div>
    </form>
</div>
@endsection
