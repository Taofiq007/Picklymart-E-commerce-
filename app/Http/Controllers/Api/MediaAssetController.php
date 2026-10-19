<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaAssetRequest;
use App\Http\Resources\MediaAssetResource;
use App\Models\MediaAsset;
use App\Services\MediaAssetRegistry;
use Illuminate\Http\Request;

class MediaAssetController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,inactive,missing'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = MediaAsset::query();
        if (! empty($validated['search'])) {
            $term = trim($validated['search']);
            $query->where(fn ($q) => $q->where('filename', 'like', "%{$term}%")
                ->orWhere('path', 'like', "%{$term}%"));
        }
        $query->when(isset($validated['mime_type']), fn ($q) => $q->where('mime_type', $validated['mime_type']));
        if (($validated['status'] ?? null) === 'missing') {
            $query->where('status', 'missing');
        } elseif (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return MediaAssetResource::collection(
            $query->orderByDesc('id')->paginate($validated['per_page'] ?? 30)->withQueryString()
        );
    }

    public function show(MediaAsset $mediaAsset): MediaAssetResource
    {
        return new MediaAssetResource($mediaAsset);
    }

    public function store(StoreMediaAssetRequest $request, MediaAssetRegistry $registry): MediaAssetResource
    {
        $validated = $request->validated();

        $asset = $request->hasFile('file')
            ? $registry->registerUpload($validated['file'], $validated['source_id'] ?? null)
            : $registry->registerExistingPath($validated['disk'], $validated['path'], $validated['source_id'] ?? null);

        return new MediaAssetResource($asset);
    }
}
