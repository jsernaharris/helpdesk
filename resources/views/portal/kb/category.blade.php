@extends('layouts.portal')
@section('title', $category->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('portal.kb.index') }}" class="hover:text-indigo-600">Knowledge Base</a> / {{ $category->name }}
    </nav>

    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $category->name }}</h2>
    @if($category->description)
    <p class="text-sm text-gray-500 mb-6">{{ $category->description }}</p>
    @endif

    <div class="space-y-3">
        @forelse($articles as $article)
        <a href="{{ route('portal.kb.show', [$category, $article]) }}" class="block bg-white shadow rounded-lg p-4 hover:bg-gray-50">
            <h3 class="text-sm font-semibold text-indigo-600">{{ $article->title }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $article->excerpt ?? Str::limit(strip_tags($article->content), 150) }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ $article->published_at?->format('M d, Y') }} &middot; {{ $article->view_count }} views</p>
        </a>
        @empty
        <p class="text-sm text-gray-500">No articles in this category yet.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $articles->links() }}</div>
</div>
@endsection
