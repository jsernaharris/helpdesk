@extends('layouts.staff')
@section('title', 'Knowledge Base Management')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div></div>
    <div class="flex gap-3">
        <a href="{{ route('staff.kb-categories.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Manage Categories</a>
        <a href="{{ route('staff.kb.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Article</a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white shadow rounded-lg mb-6 p-4">
    <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-md border-gray-300 text-sm px-3 py-2 border">
        <select name="visibility" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Visibility</option>
            <option value="public" @selected(request('visibility') === 'public')>Public</option>
            <option value="internal" @selected(request('visibility') === 'internal')>Internal</option>
            <option value="customer_specific" @selected(request('visibility') === 'customer_specific')>Customer Specific</option>
        </select>
        <select name="organization_id" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Organizations</option>
            @foreach($organizations as $org)
            <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Statuses</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
            <option value="archived" @selected(request('status') === 'archived')>Archived</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
    </form>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visibility</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($articles as $article)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.kb.show', $article) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $article->title }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $article->category?->name }}</td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $article->status])</td>
                <td class="px-4 py-3">
                    @if($article->visibility === 'customer_specific')
                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">Customer Specific</span>
                    @elseif($article->visibility === 'internal')
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Internal</span>
                    @else
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Public</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-500">{{ $article->organization?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $article->view_count }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $article->updated_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No articles.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $articles->links() }}</div>
</div>
@endsection
