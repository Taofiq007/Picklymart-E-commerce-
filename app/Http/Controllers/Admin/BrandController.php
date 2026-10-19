<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $brands = Brand::query()
            ->withCount('products')
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.brands.index', compact('brands', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_id' => ['nullable', 'string', 'max:191', Rule::unique('brands', 'source_id')],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('brands', 'slug')],
            'status' => ['required', 'in:active,inactive,draft'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('status', 'Brand created.');
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'source_id' => ['nullable', 'string', 'max:191', Rule::unique('brands', 'source_id')->ignore($brand->id)],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'status' => ['required', 'in:active,inactive,draft'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], $brand->id);
        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('status', 'Brand deleted.');
    }

    private function uniqueSlug(string $input, ?int $ignoreId = null): string
    {
        $base = Str::slug($input) ?: 'brand';
        $slug = $base;
        $counter = 2;

        while (Brand::where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
