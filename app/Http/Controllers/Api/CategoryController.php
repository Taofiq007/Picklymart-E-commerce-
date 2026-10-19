<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'root' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Category::query()->with('parent')->withCount('products');
        if (! empty($validated['search'])) $query->where('name', 'like', '%' . trim($validated['search']) . '%');
        if (! empty($validated['root'])) $query->whereNull('parent_id');
        elseif (array_key_exists('parent_id', $validated)) $query->where('parent_id', $validated['parent_id']);
        $query->when(isset($validated['status']), fn ($q) => $q->where('status', $validated['status']));

        return CategoryResource::collection($query->orderBy('name')->paginate($validated['per_page'] ?? 50)->withQueryString());
    }

    public function store(Request $request): CategoryResource
    {
        $data = $this->validateCategory($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['status'] = $data['status'] ?? 'active';
        $category = Category::create($data);

        return new CategoryResource($category->load('parent')->loadCount('products'));
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->load(['parent', 'children', 'mediaAssets'])->loadCount('products'));
    }

    public function update(Request $request, Category $category): CategoryResource
    {
        $data = $this->validateCategory($request, $category);
        if (array_key_exists('parent_id', $data) && $data['parent_id'] !== null) {
            $ancestor = Category::find($data['parent_id']);
            $visited = [];
            while ($ancestor && ! isset($visited[$ancestor->id])) {
                if ($ancestor->id === $category->id) {
                    throw ValidationException::withMessages(['parent_id' => 'A category cannot be moved beneath itself or one of its descendants.']);
                }
                $visited[$ancestor->id] = true;
                $ancestor = $ancestor->parent;
            }
        }
        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'] ?? $category->name, $category->id);
        }
        $category->update($data);

        return new CategoryResource($category->load(['parent', 'children', 'mediaAssets'])->loadCount('products'));
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $slugRule = Rule::unique('categories', 'slug');
        if ($category) $slugRule->ignore($category->id);

        return $request->validate([
            'name' => [$category ? 'sometimes' : 'required', 'string', 'max:180'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:191', $slugRule],
            'description' => ['sometimes', 'nullable', 'string'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'status' => ['sometimes', 'in:active,inactive,draft'],
        ]);
    }

    private function uniqueSlug(string $input, ?int $ignoreId = null): string
    {
        $base = Str::slug($input) ?: 'category';
        $slug = $base;
        $counter = 2;
        while (Category::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }
}
