@extends('layouts.staff')
@section('title', 'Knowledge Base Management')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('staff.kb.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Article</a>
</div>
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visibility</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($articles as $article)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.kb.edit', $article) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $article->title }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $article->category?->name }}</td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $article->status])</td>
                <td class="px-4 py-3 text-gray-500">{{ ucfirst($article->visibility) }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $article->view_count }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $article->updated_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No articles.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $articles->links() }}</div>
</div>
@endsection
