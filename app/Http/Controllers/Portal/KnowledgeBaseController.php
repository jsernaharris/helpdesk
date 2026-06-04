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

        $orgId = $request->user()->organization_id;

        $featuredArticles = KbArticle::published()
            ->where(function ($q) use ($orgId) {
                $q->where('visibility', 'public')
                    ->orWhere(function ($q2) use ($orgId) {
                        $q2->where('visibility', 'customer_specific')
                            ->where('organization_id', $orgId);
                    });
            })
            ->where('is_pinned', true)
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $query = null;
        $searchResults = null;
        if ($request->filled('q')) {
            $query = $request->q;
            $searchResults = KbArticle::published()
                ->where(function ($q2) use ($orgId) {
                    $q2->where('visibility', 'public')
                        ->orWhere(function ($q3) use ($orgId) {
                            $q3->where('visibility', 'customer_specific')
                                ->where('organization_id', $orgId);
                        });
                })
                ->whereRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE)', [$query])
                ->limit(20)
                ->get();
        }

        return view('portal.kb.index', compact('categories', 'featuredArticles', 'query', 'searchResults'));
    }

    public function category(Request $request, KbCategory $category)
    {
        $orgId = $request->user()->organization_id;

        $articles = $category->articles()
            ->published()
            ->where(function ($q) use ($orgId) {
                $q->where('visibility', 'public')
                    ->orWhere(function ($q2) use ($orgId) {
                        $q2->where('visibility', 'customer_specific')
                            ->where('organization_id', $orgId);
                    });
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('portal.kb.category', compact('category', 'articles'));
    }

    public function show(Request $request, KbCategory $category, KbArticle $article)
    {
        $orgId = $request->user()->organization_id;

        if ($article->status !== 'published') {
            abort(404);
        }

        // Allow public articles and customer-specific articles for the user's org
        if ($article->visibility === 'customer_specific' && $article->organization_id !== $orgId) {
            abort(404);
        }
        if ($article->visibility === 'internal') {
            abort(404);
        }

        $article->increment('view_count');

        $relatedArticles = KbArticle::published()
            ->where(function ($q) use ($orgId) {
                $q->where('visibility', 'public')
                    ->orWhere(function ($q2) use ($orgId) {
                        $q2->where('visibility', 'customer_specific')
                            ->where('organization_id', $orgId);
                    });
            })
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->limit(5)
            ->get();

        return view('portal.kb.show', compact('category', 'article', 'relatedArticles'));
    }
}
