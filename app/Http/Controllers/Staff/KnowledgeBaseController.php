<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $categories = KbCategory::withCount('articles')->orderBy('sort_order')->get();

        $query = KbArticle::with(['category', 'author']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        return view('staff.kb.index', compact('categories', 'articles'));
    }

    public function create()
    {
        $categories = KbCategory::orderBy('name')->get();
        return view('staff.kb.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories,id',
            'visibility' => 'required|in:public,internal,customer_specific',
            'status' => 'required|in:draft,published',
            'excerpt' => 'nullable|string',
        ]);

        $article = KbArticle::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'category_id' => $request->category_id,
            'author_id' => $request->user()->id,
            'visibility' => $request->visibility,
            'status' => $request->status,
            'excerpt' => $request->excerpt,
            'published_at' => $request->status === 'published' ? now() : null,
        ]);

        return redirect()->route('staff.kb.index')
            ->with('success', 'Article created successfully.');
    }

    public function edit(KbArticle $article)
    {
        $categories = KbCategory::orderBy('name')->get();
        return view('staff.kb.edit', compact('article', 'categories'));
    }

    public function update(Request $request, KbArticle $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories,id',
            'visibility' => 'required|in:public,internal,customer_specific',
            'status' => 'required|in:draft,published,archived',
            'excerpt' => 'nullable|string',
        ]);

        $wasPublished = $article->status === 'published';

        $article->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'category_id' => $request->category_id,
            'visibility' => $request->visibility,
            'status' => $request->status,
            'excerpt' => $request->excerpt,
            'published_at' => (!$wasPublished && $request->status === 'published') ? now() : $article->published_at,
        ]);

        return redirect()->route('staff.kb.index')
            ->with('success', 'Article updated.');
    }

    public function destroy(KbArticle $article)
    {
        $article->delete();
        return redirect()->route('staff.kb.index')
            ->with('success', 'Article deleted.');
    }
}
