<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryMediaController extends Controller
{
    public function store(Request $request, Category $category): CategoryResource
    {
        $validated = $request->validate([
            'media_asset_id' => ['required', 'integer', 'exists:media_assets,id'],
            'role' => ['required', 'in:banner,thumbnail,icon,other'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ]);

        $asset = MediaAsset::findOrFail($validated['media_asset_id']);
        abort_unless($asset->fileExists(), 422, 'The selected media file is not present on its configured disk.');

        DB::transaction(function () use ($category, $asset, $validated) {
            $pivot = [
                'role' => $validated['role'],
                'position' => $validated['position'] ?? 0,
            ];
            if ($category->mediaAssets()->whereKey($asset->id)->exists()) {
                $category->mediaAssets()->updateExistingPivot($asset->id, $pivot);
            } else {
                $category->mediaAssets()->attach($asset->id, $pivot);
            }
        });

        return new CategoryResource($category->load(['parent', 'children', 'mediaAssets'])->loadCount('products'));
    }

    public function destroy(Category $category, MediaAsset $mediaAsset)
    {
        $category->mediaAssets()->detach($mediaAsset->id);

        return response()->noContent();
    }
}
