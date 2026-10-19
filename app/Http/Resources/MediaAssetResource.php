<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_id' => $this->source_id,
            'filename' => $this->filename,
            'original_name' => $this->original_name,
            'disk' => $this->disk,
            'path' => $this->path,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'checksum' => $this->checksum,
            'status' => $this->status,
            'exists' => $this->fileExists(),
            'url' => $this->publicUrl(),
            'role' => $this->whenPivotLoaded('product_media', fn () => $this->pivot->role),
            'position' => $this->whenPivotLoaded('product_media', fn () => $this->pivot->position),
            'category_role' => $this->whenPivotLoaded('category_media', fn () => $this->pivot->role),
            'category_position' => $this->whenPivotLoaded('category_media', fn () => $this->pivot->position),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
