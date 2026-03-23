@extends('layouts.staff')
@section('title', 'New KB Article')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.kb.store') }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700">Visibility</label><select name="visibility" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"><option value="public">Public</option><option value="internal">Internal</option><option value="customer_specific">Customer Specific</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700">Status</label><select name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"><option value="draft">Draft</option><option value="published">Published</option></select></div>
            </div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700">Title</label><input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Excerpt</label><textarea name="excerpt" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700">Content</label><textarea name="content" rows="12" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono"></textarea></div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.kb.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Publish Article</button>
        </div>
    </form>
</div>
@endsection
