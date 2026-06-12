<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KbCategory;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbCategoryController extends Controller
{
    public function index()
    {
        $categories = KbCategory::withCount('articles')
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        return view('staff.kb-categories.index', compact('categories', 'organizations'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);

        KbCategory::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('staff.kb-categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(KbCategory $kbCategory)
    {
        $categories = KbCategory::where('id', '!=', $kbCategory->id)->orderBy('name')->get();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        return view('staff.kb-categories.edit', [
            'category' => $kbCategory,
            'categories' => $categories,
            'organizations' => $organizations,
        ]);
    }

    public function update(Request $request, KbCategory $kbCategory)
    {
        $data = $this->validateCategory($request, $kbCategory);

        $kbCategory->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], $kbCategory->id),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('staff.kb-categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(KbCategory $kbCategory)
    {
        // Deleting a category cascades to its articles, so block when any exist.
        if ($kbCategory->articles()->exists()) {
            return back()->with('error', 'Move or delete this category\'s articles before removing it.');
        }

        $kbCategory->delete();

        return redirect()->route('staff.kb-categories.index')
            ->with('success', 'Category deleted.');
    }

    private function validateCategory(Request $request, ?KbCategory $category = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable', 'exists:kb_categories,id',
                // A category cannot be its own parent.
                fn ($attr, $value, $fail) => $category && (int) $value === $category->id
                    ? $fail('A category cannot be its own parent.') : null,
            ],
            'organization_id' => 'nullable|exists:organizations,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'category';
        $slug = $base;
        $i = 2;

        while (
            KbCategory::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
