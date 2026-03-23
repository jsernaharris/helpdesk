<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $categories = KbCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->whereNull('organization_id')
                    ->orWhere('organization_id', $request->user()->organization_id);
            })
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $featuredArticles = KbArticle::published()
            ->where('visibility', 'public')
            ->where('is_pinned', true)
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $query = null;
        $searchResults = null;
        if ($request->filled('q')) {
            $query = $request->q;
            $searchResults = KbArticle::published()
                ->where('visibility', 'public')
                ->whereRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE)', [$query])
                ->limit(20)
                ->get();
        }

        return view('portal.kb.index', compact('categories', 'featuredArticles', 'query', 'searchResults'));
    }

    public function category(KbCategory $category)
    {
        $articles = $category->articles()
            ->published()
            ->where('visibility', 'public')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('portal.kb.category', compact('category', 'articles'));
    }

    public function show(KbCategory $category, KbArticle $article)
    {
        if ($article->status !== 'published' || $article->visibility !== 'public') {
            abort(404);
        }

        $article->increment('view_count');

        $relatedArticles = KbArticle::published()
            ->where('visibility', 'public')
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->limit(5)
            ->get();

        return view('portal.kb.show', compact('category', 'article', 'relatedArticles'));
    }
}
