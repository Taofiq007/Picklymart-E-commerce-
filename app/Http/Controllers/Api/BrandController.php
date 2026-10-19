<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Brand::query();
        if (! empty($validated['search'])) $query->where('name', 'like', '%' . trim($validated['search']) . '%');
        $query->when(isset($validated['status']), fn ($q) => $q->where('status', $validated['status']));

        return BrandResource::collection($query->orderBy('name')->paginate($validated['per_page'] ?? 50)->withQueryString());
    }

    public function show(Brand $brand): BrandResource
    {
        return new BrandResource($brand);
    }

    public function store(Request $request): BrandResource
    {
        $data = $request->validate([
            'source_id' => ['nullable', 'string', 'max:191', 'unique:brands,source_id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:191', 'unique:brands,slug'],
            'status' => ['sometimes', 'in:active,inactive,draft'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['status'] = $data['status'] ?? 'active';

        return new BrandResource(Brand::create($data));
    }

    public function update(Request $request, Brand $brand): BrandResource
    {
        $data = $request->validate([
            'source_id' => ['sometimes', 'nullable', 'string', 'max:191', Rule::unique('brands', 'source_id')->ignore($brand->id)],
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:191', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'status' => ['sometimes', 'in:active,inactive,draft'],
        ]);
        if (array_key_exists('name', $data) || array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'] ?? $brand->name, $brand->id);
        }
        $brand->update($data);

        return new BrandResource($brand);
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->noContent();
    }

    private function uniqueSlug(string $input, ?int $ignoreId = null): string
    {
        $base = Str::slug($input) ?: 'brand';
        $slug = $base;
        $counter = 2;

        while (Brand::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
