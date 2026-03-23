@extends('layouts.portal')
@section('title', 'Knowledge Base')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Search -->
    <div class="mb-8">
        <form method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search the knowledge base..." class="block w-full rounded-md border-gray-300 text-sm px-4 py-3 border focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-500">Search</button>
        </form>
    </div>

    @if($searchResults)
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Search Results for "{{ $query }}"</h2>
        <div class="space-y-3">
            @forelse($searchResults as $article)
            <a href="{{ route('portal.kb.show', [$article->category, $article]) }}" class="block bg-white shadow rounded-lg p-4 hover:bg-gray-50">
                <h3 class="text-sm font-semibold text-indigo-600">{{ $article->title }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $article->excerpt ?? Str::limit(strip_tags($article->content), 150) }}</p>
            </a>
            @empty
            <p class="text-sm text-gray-500">No results found.</p>
            @endforelse
        </div>
    </div>
    @endif

    @if($featuredArticles->count())
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Featured Articles</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($featuredArticles as $article)
            <a href="{{ route('portal.kb.show', [$article->category, $article]) }}" class="bg-white shadow rounded-lg p-4 hover:bg-gray-50">
                <h3 class="text-sm font-semibold text-indigo-600">{{ $article->title }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $article->excerpt ?? Str::limit(strip_tags($article->content), 100) }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Categories -->
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Browse by Category</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($categories as $category)
        <a href="{{ route('portal.kb.category', $category) }}" class="bg-white shadow rounded-lg p-5 hover:bg-gray-50">
            <h3 class="text-base font-semibold text-gray-900">{{ $category->name }}</h3>
            @if($category->description)
            <p class="text-sm text-gray-500 mt-1">{{ $category->description }}</p>
            @endif
            <p class="text-xs text-indigo-600 mt-2">{{ $category->articles_count ?? $category->articles->count() }} articles</p>
        </a>
        @endforeach
    </div>
</div>
@endsection
