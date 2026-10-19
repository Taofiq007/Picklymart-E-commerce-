<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive,draft'],
        ]);

        $products = Product::query()
            ->with(['category', 'brand', 'mediaAssets'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query
                ->where(fn (Builder $query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('source_id', 'like', "%{$term}%")))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'filters'));
    }

    public function create(): View
    {
        return $this->form(new Product, []);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $assetIds = $data['media_asset_ids'] ?? [];
        unset($data['media_asset_ids']);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['currency'] = strtoupper($data['currency'] ?? 'BDT');
        $data['status'] = $data['status'] ?? 'draft';

        $product = DB::transaction(function () use ($data, $assetIds): Product {
            $product = Product::create($data);
            $product->mediaAssets()->sync($this->mediaPivot($assetIds));

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return $this->form($product->load('mediaAssets'), $product->mediaAssets->modelKeys());
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);
        $assetIds = $data['media_asset_ids'] ?? [];
        unset($data['media_asset_ids']);

        if (array_key_exists('name', $data) || array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'] ?? $product->name, $product->id);
        }
        if (isset($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }

        DB::transaction(function () use ($product, $data, $assetIds): void {
            $product->update($data);
            $product->mediaAssets()->sync($this->mediaPivot($assetIds));
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $uniqueSource = Rule::unique('products', 'source_id');
        $uniqueSku = Rule::unique('products', 'sku');
        $uniqueSlug = Rule::unique('products', 'slug');

        if ($product) {
            $uniqueSource->ignore($product->id);
            $uniqueSku->ignore($product->id);
            $uniqueSlug->ignore($product->id);
        }

        $data = $request->validate([
            'source_id' => ['nullable', 'string', 'max:191', $uniqueSource],
            'sku' => ['nullable', 'string', 'max:100', $uniqueSku],
            'name' => [$product ? 'sometimes' : 'required', 'string', 'max:220'],
            'slug' => ['nullable', 'string', 'max:191', $uniqueSlug],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'status' => ['required', 'in:active,inactive,draft'],
            'media_asset_ids' => ['sometimes', 'array'],
            'media_asset_ids.*' => ['integer', 'distinct', Rule::exists('media_assets', 'id')->where('status', 'active')],
        ]);

        if (isset($data['sale_price'], $data['price']) && (float) $data['sale_price'] > (float) $data['price']) {
            throw ValidationException::withMessages(['sale_price' => 'The sale price must not exceed the regular price.']);
        }

        return $data;
    }

    private function form(Product $product, array $selectedAssets): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::with('parent')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'assets' => MediaAsset::where('status', 'active')->orderByDesc('created_at')->get(),
            'selectedAssets' => $selectedAssets,
        ]);
    }

    private function mediaPivot(array $assetIds): array
    {
        $pivot = [];

        foreach (array_values($assetIds) as $position => $assetId) {
            $pivot[$assetId] = [
                'role' => $position === 0 ? 'main' : 'gallery',
                'position' => $position,
            ];
        }

        return $pivot;
    }

    private function uniqueSlug(string $input, ?int $ignoreId = null): string
    {
        $base = Str::slug($input) ?: 'product';
        $slug = $base;
        $counter = 2;

        while (Product::where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
