@extends('layouts.staff')
@section('title', $article->title)

@section('content')
<div class="max-w-4xl">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $article->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    By {{ $article->author?->name }} &middot;
                    {{ $article->category?->name }} &middot;
                    {{ $article->published_at?->format('M d, Y') ?? 'Not published' }} &middot;
                    {{ $article->view_count }} views
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if($article->visibility === 'customer_specific')
                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                        {{ $article->organization?->name ?? 'Customer Specific' }}
                    </span>
                @elseif($article->visibility === 'internal')
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Internal</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Public</span>
                @endif
                @include('components.status-badge', ['status' => $article->status])
                <a href="{{ route('staff.kb.edit', $article) }}" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Edit</a>
            </div>
        </div>

        @if($article->excerpt)
        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600 italic">{{ $article->excerpt }}</p>
        </div>
        @endif
    </div>

    <!-- Content -->
    <div class="bg-white shadow rounded-lg p-8">
        <div class="prose prose-sm max-w-none prose-img:rounded-lg prose-img:shadow-md">
            {!! Str::markdown($article->content) !!}
        </div>
    </div>

    <!-- Meta -->
    <div class="mt-6 flex items-center justify-between">
        <a href="{{ route('staff.kb.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Knowledge Base</a>
        <div class="flex gap-3">
            <a href="{{ route('staff.kb.edit', $article) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit Article</a>
            <form method="POST" action="{{ route('staff.kb.destroy', $article) }}" onsubmit="return confirm('Delete this article?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
