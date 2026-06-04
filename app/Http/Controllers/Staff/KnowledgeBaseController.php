<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $categories = KbCategory::withCount('articles')->orderBy('sort_order')->get();

        $query = KbArticle::with(['category', 'author', 'organization']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        return view('staff.kb.index', compact('categories', 'articles', 'organizations'));
    }

    public function create()
    {
        $categories = KbCategory::orderBy('name')->get();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        return view('staff.kb.create', compact('categories', 'organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories,id',
            'visibility' => 'required|in:public,internal,customer_specific',
            'organization_id' => 'nullable|required_if:visibility,customer_specific|exists:organizations,id',
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
            'organization_id' => $request->visibility === 'customer_specific' ? $request->organization_id : null,
            'status' => $request->status,
            'excerpt' => $request->excerpt,
            'published_at' => $request->status === 'published' ? now() : null,
        ]);

        return redirect()->route('staff.kb.index')
            ->with('success', 'Article created successfully.');
    }

    public function show(KbArticle $kb)
    {
        $article = $kb;
        $article->load(['category', 'author', 'organization', 'tags']);
        return view('staff.kb.show', compact('article'));
    }

    public function edit(KbArticle $kb)
    {
        $article = $kb;
        $categories = KbCategory::orderBy('name')->get();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        return view('staff.kb.edit', compact('article', 'categories', 'organizations'));
    }

    public function update(Request $request, KbArticle $kb)
    {
        $article = $kb;
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:kb_categories,id',
            'visibility' => 'required|in:public,internal,customer_specific',
            'organization_id' => 'nullable|required_if:visibility,customer_specific|exists:organizations,id',
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
            'organization_id' => $request->visibility === 'customer_specific' ? $request->organization_id : null,
            'status' => $request->status,
            'excerpt' => $request->excerpt,
            'published_at' => (!$wasPublished && $request->status === 'published') ? now() : $article->published_at,
        ]);

        return redirect()->route('staff.kb.index')
            ->with('success', 'Article updated.');
    }

    public function destroy(KbArticle $kb)
    {
        $kb->delete();
        return redirect()->route('staff.kb.index')
            ->with('success', 'Article deleted.');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB
        ]);

        $path = $request->file('image')->store('kb-images', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }
}
