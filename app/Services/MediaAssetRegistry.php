<?php

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MediaAssetRegistry
{
    public function registerUpload(UploadedFile $file, ?string $sourceId = null): MediaAsset
    {
        $disk = 'public';
        $path = $file->storePublicly('media/assets', $disk);
        if (! is_string($path)) {
            throw new RuntimeException('The image upload could not be stored on the public disk.');
        }

        try {
            return MediaAsset::create([
                'source_id' => $sourceId,
                'filename' => basename($path),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'disk' => $disk,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'checksum' => $this->checksum($disk, $path),
                'status' => 'active',
            ]);
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    public function registerExistingPath(string $disk, string $path, ?string $sourceId = null): MediaAsset
    {
        if (
            $path === ''
            || str_starts_with($path, '/')
            || str_contains($path, '\\')
            || str_contains($path, "\0")
            || in_array('..', explode('/', $path), true)
        ) {
            throw ValidationException::withMessages([
                'path' => 'The repository path must be a relative path inside its configured storage disk.',
            ]);
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            throw ValidationException::withMessages([
                'path' => 'The selected path does not exist on the configured storage disk.',
            ]);
        }

        $stream = $storage->readStream($path);
        if (! is_resource($stream)) {
            throw new RuntimeException("Unable to read the registered media file at {$disk}:{$path}.");
        }

        try {
            $sample = fread($stream, 65536);
        } finally {
            fclose($stream);
        }

        if (! is_string($sample) || $sample === '') {
            throw new RuntimeException("Unable to inspect the registered media file at {$disk}:{$path}.");
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($sample);
        if (! is_string($mimeType) || ! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            throw ValidationException::withMessages([
                'path' => 'Only JPEG, PNG, WebP, and GIF files can be registered as catalog media.',
            ]);
        }

        return MediaAsset::create([
            'source_id' => $sourceId,
            'filename' => basename($path),
            'original_name' => basename($path),
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $mimeType,
            'size_bytes' => $storage->size($path),
            'checksum' => $this->checksum($disk, $path),
            'status' => 'active',
        ]);
    }

    private function checksum(string $disk, string $path): ?string
    {
        if (config("filesystems.disks.{$disk}.driver") !== 'local') {
            return null;
        }

        $checksum = hash_file('sha256', Storage::disk($disk)->path($path));
        if ($checksum === false) {
            throw new RuntimeException("Unable to calculate the media checksum for {$disk}:{$path}.");
        }

        return $checksum;
    }
}
