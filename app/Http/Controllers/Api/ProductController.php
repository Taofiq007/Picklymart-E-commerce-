<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Product::query()->with(['category', 'brand', 'mediaAssets']);
        if (! empty($validated['search'])) {
            $term = trim($validated['search']);
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%"));
        }
        $query->when(isset($validated['category_id']), fn ($q) => $q->where('category_id', $validated['category_id']));
        $query->when(isset($validated['brand_id']), fn ($q) => $q->where('brand_id', $validated['brand_id']));
        $query->when(isset($validated['status']), fn ($q) => $q->where('status', $validated['status']));

        return ProductResource::collection($query->orderBy('id')->paginate($validated['per_page'] ?? 20)->withQueryString());
    }

    public function store(Request $request): ProductResource
    {
        $data = $this->validateProduct($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['currency'] = $data['currency'] ?? 'BDT';
        $data['status'] = $data['status'] ?? 'active';
        $product = Product::create($data);

        return new ProductResource($product->load(['category', 'brand', 'mediaAssets']));
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'brand', 'mediaAssets']));
    }

    public function update(Request $request, Product $product): ProductResource
    {
        $data = $this->validateProduct($request, $product);
        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'] ?? $product->name, $product->id);
        }
        $product->update($data);

        return new ProductResource($product->load(['category', 'brand', 'mediaAssets']));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $isCreate = $product === null;
        $slugRule = Rule::unique('products', 'slug');
        $skuRule = Rule::unique('products', 'sku');
        if ($product) {
            $slugRule->ignore($product->id);
            $skuRule->ignore($product->id);
        }

        return $request->validate([
            'sku' => ['sometimes', 'nullable', 'string', 'max:100', $skuRule],
            'slug' => ['sometimes', 'nullable', 'string', 'max:191', $slugRule],
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:220'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id'],
            'status' => ['sometimes', 'in:active,inactive,draft'],
        ]);
    }

    private function uniqueSlug(string $input, ?int $ignoreId = null): string
    {
        $base = Str::slug($input) ?: 'product';
        $slug = $base;
        $counter = 2;
        while (Product::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }
}
