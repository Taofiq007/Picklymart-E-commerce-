<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReconcileMediaAssets extends Command
{
    protected $signature = 'picklymart:reconcile-media {directory=media : Directory relative to the selected filesystem disk} {--disk=public : Configured Laravel filesystem disk}';
    protected $description = 'Report missing registered media and unregistered image files without deleting anything.';

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

        $filesystemImages = [];
        foreach ($paths as $path) {
            try {
                $mime = $disk->mimeType($path);
                if (is_string($mime) && str_starts_with($mime, 'image/')) {
                    $filesystemImages[$path] = true;
                }
            } catch (Throwable) {
                // Unreadable files will be visible as missing/unregistered in later investigation.
            }
        }

        $missing = [];
        $registeredPaths = [];
        $assets = MediaAsset::query()->get(['id', 'disk', 'path']);
        foreach ($assets as $asset) {
            if ($asset->disk === $diskName && str_starts_with($asset->path, $directory . '/')) {
                $registeredPaths[$asset->path] = true;
            }
            try {
                if (! Storage::disk($asset->disk)->exists($asset->path)) {
                    $missing[] = "asset_id={$asset->id} disk={$asset->disk} path={$asset->path}";
                    MediaAsset::whereKey($asset->id)->update(['status' => 'missing']);
                } elseif ($asset->status === 'missing') {
                    MediaAsset::whereKey($asset->id)->update(['status' => 'active']);
                }
            } catch (Throwable) {
                $missing[] = "asset_id={$asset->id} disk={$asset->disk} path={$asset->path} (disk unavailable)";
            }
        }

        $orphanPaths = array_values(array_diff(array_keys($filesystemImages), array_keys($registeredPaths)));
        $this->newLine();
        $this->info('Media reconciliation summary');
        $this->line('Images found on disk: ' . count($filesystemImages));
        $this->line('Registered paths under target directory: ' . count($registeredPaths));
        $this->line('Registered assets whose files are missing/unavailable: ' . count($missing));
        $this->line('Image files not registered for this disk/directory: ' . count($orphanPaths));

        if ($missing) {
            $this->warn('Missing registered assets (first 100):');
            foreach (array_slice($missing, 0, 100) as $item) $this->line(' - ' . $item);
        }
        if ($orphanPaths) {
            $this->warn('Unregistered image files (first 100):');
            foreach (array_slice($orphanPaths, 0, 100) as $path) $this->line(' - ' . $path);
        }

        return self::SUCCESS;
    }
}
