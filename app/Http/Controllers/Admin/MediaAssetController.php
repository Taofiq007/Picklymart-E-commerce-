<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaAssetRequest;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Services\MediaAssetRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MediaAssetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'in:active,inactive,missing'],
        ]);

        $assets = MediaAsset::query()
            ->with(['products:id,name', 'categories:id,name'])
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query
                ->where(fn ($query) => $query->where('filename', 'like', "%{$term}%")
                    ->orWhere('path', 'like', "%{$term}%")
                    ->orWhere('source_id', 'like', "%{$term}%")))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.media-assets.index', [
            'assets' => $assets,
            'filters' => $filters,
            'products' => Product::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'storageDisks' => array_keys(config('filesystems.disks', [])),
        ]);
    }

    public function store(StoreMediaAssetRequest $request, MediaAssetRegistry $registry): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $registry->registerUpload($data['file'], $data['source_id'] ?? null);
        } else {
            $registry->registerExistingPath($data['disk'], $data['path'], $data['source_id'] ?? null);
        }

        return redirect()->route('admin.media-assets.index')->with('status', 'Media asset registered.');
    }

    public function updateStatus(Request $request, MediaAsset $mediaAsset): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:active,inactive,missing']]);
        $mediaAsset->update($data);

        return redirect()->route('admin.media-assets.index')->with('status', 'Asset status updated.');
    }

    public function attachProduct(Request $request, MediaAsset $mediaAsset): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'role' => ['required', 'in:main,gallery,thumbnail,detail,other'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ]);
        $this->ensureAvailableFile($mediaAsset);

        $product = Product::findOrFail($data['product_id']);
        $product->mediaAssets()->syncWithoutDetaching([
            $mediaAsset->id => [
                'role' => $data['role'],
                'position' => $data['position'] ?? 0,
            ],
        ]);

        return redirect()->route('admin.media-assets.index')->with('status', 'Asset mapped to product.');
    }

    public function detachProduct(MediaAsset $mediaAsset, Product $product): RedirectResponse
    {
        $product->mediaAssets()->detach($mediaAsset->id);

        return redirect()->route('admin.media-assets.index')->with('status', 'Product mapping removed.');
    }

    public function attachCategory(Request $request, MediaAsset $mediaAsset): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'role' => ['required', 'in:banner,thumbnail,other'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ]);
        $this->ensureAvailableFile($mediaAsset);

        $category = Category::findOrFail($data['category_id']);
        $category->mediaAssets()->syncWithoutDetaching([
            $mediaAsset->id => [
                'role' => $data['role'],
                'position' => $data['position'] ?? 0,
            ],
        ]);

        return redirect()->route('admin.media-assets.index')->with('status', 'Asset mapped to category.');
    }

    public function detachCategory(MediaAsset $mediaAsset, Category $category): RedirectResponse
    {
        $category->mediaAssets()->detach($mediaAsset->id);

        return redirect()->route('admin.media-assets.index')->with('status', 'Category mapping removed.');
    }

    private function ensureAvailableFile(MediaAsset $mediaAsset): void
    {
        if ($mediaAsset->status !== 'active' || ! $mediaAsset->fileExists()) {
            throw ValidationException::withMessages([
                'media_asset' => 'Only active assets whose files exist on the configured disk can be mapped.',
            ]);
        }
    }
}
