<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_id' => $this->source_id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? new CategoryResource($this->parent) : null),
            'children' => $this->whenLoaded('children', fn () => CategoryResource::collection($this->children)),
            'products_count' => $this->whenCounted('products'),
            'media_assets' => $this->whenLoaded('mediaAssets', fn () => MediaAssetResource::collection($this->mediaAssets)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
