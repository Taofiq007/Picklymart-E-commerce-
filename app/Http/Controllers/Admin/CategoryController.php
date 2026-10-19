<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $categories = Category::with(['parent', 'mediaAssets'])
            ->withCount('products')
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'filters'));
    }

    public function create(): View
    {
        return $this->form(new Category, []);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);
        $assetIds = $data['media_asset_ids'] ?? [];
        unset($data['media_asset_ids']);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['status'] = $data['status'] ?? 'draft';

        $category = DB::transaction(function () use ($data, $assetIds): Category {
            $category = Category::create($data);
            $category->mediaAssets()->sync($this->mediaPivot($assetIds));

            return $category;
        });

        return redirect()->route('admin.categories.edit', $category)->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return $this->form($category->load('mediaAssets'), $category->mediaAssets->modelKeys());
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateCategory($request, $category);
        $assetIds = $data['media_asset_ids'] ?? [];
        unset($data['media_asset_ids']);

        if (array_key_exists('parent_id', $data) && $data['parent_id'] !== null) {
            $ancestor = Category::find($data['parent_id']);
            $visited = [];

            while ($ancestor) {
                if ($ancestor->is($category)) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'A category cannot be nested beneath itself or one of its descendants.',
                    ]);
                }

                if (isset($visited[$ancestor->id])) {
                    break;
                }

                $visited[$ancestor->id] = true;
                $ancestor = $ancestor->parent;
            }
        }

        if (array_key_exists('name', $data) || array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'] ?? $category->name, $category->id);
        }

        DB::transaction(function () use ($category, $data, $assetIds): void {
            $category->update($data);
            $category->mediaAssets()->sync($this->mediaPivot($assetIds));
        });

        return redirect()->route('admin.categories.edit', $category)->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $uniqueSource = Rule::unique('categories', 'source_id');
        $uniqueSlug = Rule::unique('categories', 'slug');
        if ($category) {
            $uniqueSource->ignore($category->id);
            $uniqueSlug->ignore($category->id);
        }

        return $request->validate([
            'source_id' => ['nullable', 'string', 'max:191', $uniqueSource],
            'name' => [$category ? 'sometimes' : 'required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:191', $uniqueSlug],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['required', 'in:active,inactive,draft'],
            'media_asset_ids' => ['sometimes', 'array'],
            'media_asset_ids.*' => ['integer', 'distinct', Rule::exists('media_assets', 'id')->where('status', 'active')],
        ]);
    }

    private function form(Category $category, array $selectedAssets): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::query()
                ->when($category->exists, fn (Builder $query) => $query->whereKeyNot($category->id))
                ->orderBy('name')
                ->get(),
            'assets' => MediaAsset::where('status', 'active')->orderByDesc('created_at')->get(),
            'selectedAssets' => $selectedAssets,
        ]);
    }

    private function mediaPivot(array $assetIds): array
    {
        $pivot = [];
        foreach (array_values($assetIds) as $position => $assetId) {
            $pivot[$assetId] = [
                'role' => 'banner',
                'position' => $position,
            ];
        }

        return $pivot;
    }

    private function uniqueSlug(string $input, ?int $ignoreId = null): string
    {
        $base = Str::slug($input) ?: 'category';
        $slug = $base;
        $counter = 2;

        while (Category::where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
