@extends('layouts.portal')
@section('title', $article->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('portal.kb.index') }}" class="hover:text-indigo-600">Knowledge Base</a> /
        <a href="{{ route('portal.kb.category', $category) }}" class="hover:text-indigo-600">{{ $category->name }}</a> /
        {{ $article->title }}
    </nav>

    <article class="bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $article->title }}</h1>
        <p class="text-sm text-gray-500 mb-6">By {{ $article->author?->name }} &middot; {{ $article->published_at?->format('M d, Y') }} &middot; {{ $article->view_count }} views</p>
        <div class="prose prose-sm max-w-none prose-img:rounded-lg prose-img:shadow-md">{!! Str::markdown($article->content) !!}</div>
    </article>

    @if($relatedArticles->count())
    <div class="mt-8">
        <h3 class="text-base font-semibold text-gray-900 mb-3">Related Articles</h3>
        <div class="space-y-2">
            @foreach($relatedArticles as $related)
            <a href="{{ route('portal.kb.show', [$category, $related]) }}" class="block text-sm text-indigo-600 hover:underline">{{ $related->title }}</a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
