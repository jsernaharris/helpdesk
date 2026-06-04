@extends('layouts.staff')
@section('title', 'Edit: ' . $article->title)

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.kb.update', $article) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf @method('PUT')
        <div x-data="{ visibility: '{{ old('visibility', $article->visibility) }}' }">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected($article->category_id == $cat->id)>{{ $cat->name }}</option>@endforeach
                    </select></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700">Visibility</label>
                        <select name="visibility" x-model="visibility" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="public" @selected($article->visibility === 'public')>Public (all customers)</option>
                            <option value="internal" @selected($article->visibility === 'internal')>Internal (MSP staff only)</option>
                            <option value="customer_specific" @selected($article->visibility === 'customer_specific')>Customer Specific</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">Status</label><select name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"><option value="draft" @selected($article->status === 'draft')>Draft</option><option value="published" @selected($article->status === 'published')>Published</option><option value="archived" @selected($article->status === 'archived')>Archived</option></select></div>
                </div>
            </div>
            <div x-show="visibility === 'customer_specific'" x-cloak class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Assign to Organization</label>
                <select name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">-- Select organization --</option>
                    @foreach($organizations as $org)
                    <option value="{{ $org->id }}" @selected($article->organization_id == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Only users from this organization will see this article in their portal.</p>
            </div>
        </div>

        <div><label class="block text-sm font-medium text-gray-700">Title</label><input type="text" name="title" value="{{ $article->title }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><label class="block text-sm font-medium text-gray-700">Excerpt</label><textarea name="excerpt" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ $article->excerpt }}</textarea></div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="font-normal text-gray-400">(Markdown supported &mdash; drag & drop images)</span></label>
            <x-markdown-editor name="content" :value="$article->content" :upload-url="route('staff.kb.upload-image')" />
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.kb.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
        </div>
    </form>
</div>
@endsection
