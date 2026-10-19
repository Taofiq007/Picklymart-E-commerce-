<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductMediaController extends Controller
{
    public function store(Request $request, Product $product): ProductResource
    {
        $validated = $request->validate([
            'media_asset_id' => ['required', 'integer', 'exists:media_assets,id'],
            'role' => ['required', 'in:main,gallery,thumbnail,detail,other'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ]);

        $asset = MediaAsset::findOrFail($validated['media_asset_id']);
        abort_unless($asset->fileExists(), 422, 'The selected media file is not present on its configured disk.');

        DB::transaction(function () use ($product, $asset, $validated) {
            $pivot = [
                'role' => $validated['role'],
                'position' => $validated['position'] ?? 0,
            ];
            if ($product->mediaAssets()->whereKey($asset->id)->exists()) {
                $product->mediaAssets()->updateExistingPivot($asset->id, $pivot);
            } else {
                $product->mediaAssets()->attach($asset->id, $pivot);
            }
        });

        return new ProductResource($product->load(['category', 'brand', 'mediaAssets']));
    }

    public function destroy(Product $product, MediaAsset $mediaAsset)
    {
        $product->mediaAssets()->detach($mediaAsset->id);

        return response()->noContent();
    }
}
