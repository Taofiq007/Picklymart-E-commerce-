<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ScanMediaAssets extends Command
{
    protected $signature = 'picklymart:scan-media {directory=media : Directory relative to the selected filesystem disk} {--disk=public : Configured Laravel filesystem disk}';
    protected $description = 'Inventory image files from a local filesystem disk into media_assets.';

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $directory = trim((string) $this->argument('directory'), '/');

        try {
            $disk = Storage::disk($diskName);
            $paths = $disk->allFiles($directory);
        } catch (Throwable $e) {
            $this->error("Could not scan disk '{$diskName}': {$e->getMessage()}");
            return self::FAILURE;
        }

        $seen = 0;
        $skipped = 0;
        foreach ($paths as $path) {
            try {
                $mime = $disk->mimeType($path);
                if (! is_string($mime) || ! str_starts_with($mime, 'image/')) {
                    $skipped++;
                    continue;
                }

                $absolutePath = null;
                try {
                    $absolutePath = $disk->path($path);
                } catch (Throwable) {
                    // Non-local disks might not have an absolute path.
                }
                $checksum = $absolutePath && is_file($absolutePath)
                    ? hash_file('sha256', $absolutePath)
                    : null;

                MediaAsset::updateOrCreate(
                    ['disk' => $diskName, 'path' => $path],
                    [
                        'filename' => basename($path),
                        'mime_type' => $mime,
                        'size_bytes' => $disk->size($path),
                        'checksum' => $checksum ?: null,
                        'status' => 'active',
                    ]
                );
                $seen++;
            } catch (Throwable $e) {
                $this->warn("Skipped {$path}: {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->info("Inventory complete. Upserted {$seen} image files; skipped {$skipped} files.");
        return self::SUCCESS;
    }
}
