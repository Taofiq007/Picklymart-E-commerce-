<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ImportPicklymartCatalog extends Command
{
    protected $signature = 'picklymart:import-catalog {directory : Directory containing approved CSV exports}';
    protected $description = 'Import an approved, limited Picklymart catalog CSV export without deleting local records.';

    public function handle(): int
    {
        $directory = rtrim((string) $this->argument('directory'), DIRECTORY_SEPARATOR);
        if (! is_dir($directory)) {
            $this->error("Directory not found: {$directory}");
            return self::FAILURE;
        }

        $totals = ['categories' => 0, 'brands' => 0, 'products' => 0, 'media_assets' => 0, 'product_media' => 0, 'category_media' => 0, 'skipped' => 0];

        // Categories first, with parent links resolved in a second pass.
        $categoryRows = $this->readCsv($directory . '/categories.csv');
        foreach ($categoryRows as $row) {
            try {
                $sourceId = trim((string) ($row['source_id'] ?? ''));
                $name = trim((string) ($row['name'] ?? ''));
                if ($sourceId === '' || $name === '') throw new \RuntimeException('source_id and name are required');
                Category::updateOrCreate(
                    ['source_id' => $sourceId],
                    [
                        'parent_id' => null,
                        'name' => $name,
                        'slug' => $this->slug($row['slug'] ?? '', $name),
                        'description' => $this->nullable($row['description'] ?? null),
                        'status' => $this->status($row['status'] ?? 'active'),
                    ]
                );
                $totals['categories']++;
            } catch (Throwable $e) {
                $totals['skipped']++;
                $this->warn('categories.csv row skipped: ' . $e->getMessage());
            }
        }

        foreach ($categoryRows as $row) {
            $sourceId = trim((string) ($row['source_id'] ?? ''));
            $parentSource = trim((string) ($row['parent_source_id'] ?? ''));
            if ($sourceId === '') continue;
            $category = Category::where('source_id', $sourceId)->first();
            if (! $category) continue;
            if ($parentSource === '') {
                $category->update(['parent_id' => null]);
                continue;
            }
            $parent = Category::where('source_id', $parentSource)->first();
            if (! $parent) {
                $this->warn("Category {$sourceId} references unknown parent_source_id {$parentSource}; keeping it at root.");
                $category->update(['parent_id' => null]);
                $totals['skipped']++;
            } elseif ($parent->id === $category->id) {
                $this->warn("Category {$sourceId} cannot be its own parent.");
                $category->update(['parent_id' => null]);
                $totals['skipped']++;
            } else {
                $category->update(['parent_id' => $parent->id]);
            }
        }

        $this->importSimpleEntity($directory . '/brands.csv', $totals, 'brands', function (array $row) {
            $sourceId = trim((string) ($row['source_id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($sourceId === '' || $name === '') throw new \RuntimeException('source_id and name are required');
            Brand::updateOrCreate(['source_id' => $sourceId], [
                'name' => $name,
                'slug' => $this->slug($row['slug'] ?? '', $name),
                'status' => $this->status($row['status'] ?? 'active'),
            ]);
        });

        $this->importSimpleEntity($directory . '/products.csv', $totals, 'products', function (array $row) {
            $sourceId = trim((string) ($row['source_id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($sourceId === '' || $name === '') throw new \RuntimeException('source_id and name are required');
            $categorySource = trim((string) ($row['category_source_id'] ?? ''));
            $brandSource = trim((string) ($row['brand_source_id'] ?? ''));
            $category = $categorySource !== '' ? Category::where('source_id', $categorySource)->first() : null;
            $brand = $brandSource !== '' ? Brand::where('source_id', $brandSource)->first() : null;
            if ($categorySource !== '' && ! $category) throw new \RuntimeException("unknown category_source_id {$categorySource}");
            if ($brandSource !== '' && ! $brand) throw new \RuntimeException("unknown brand_source_id {$brandSource}");
            $sku = $this->nullable($row['sku'] ?? null);
            Product::updateOrCreate(['source_id' => $sourceId], [
                'sku' => $sku,
                'slug' => $this->slug($row['slug'] ?? '', $sku ?: $name),
                'name' => $name,
                'description' => $this->nullable($row['description'] ?? null),
                'price' => $this->numberOrNull($row['price'] ?? null),
                'sale_price' => $this->numberOrNull($row['sale_price'] ?? null),
                'currency' => strtoupper(substr(trim((string) ($row['currency'] ?? 'BDT')) ?: 'BDT', 0, 3)),
                'category_id' => $category?->id,
                'brand_id' => $brand?->id,
                'status' => $this->status($row['status'] ?? 'active'),
            ]);
        });

        $this->importSimpleEntity($directory . '/media_assets.csv', $totals, 'media_assets', function (array $row) {
            $disk = trim((string) ($row['disk'] ?? 'public')) ?: 'public';
            $path = str_replace('\\', '/', trim((string) ($row['path'] ?? '')));
            if ($path === '' || str_starts_with($path, '/') || preg_match('~(^|/)\.\.(/|$)~', $path)) {
                throw new \RuntimeException('path must be a safe relative path');
            }
            $filename = trim((string) ($row['filename'] ?? basename($path))) ?: basename($path);
            $sourceId = $this->nullable($row['source_id'] ?? null);
            $attributes = [
                'filename' => $filename,
                'original_name' => $this->nullable($row['original_name'] ?? null),
                'mime_type' => $this->nullable($row['mime_type'] ?? null),
                'size_bytes' => $this->integerOrNull($row['size_bytes'] ?? null),
                'checksum' => $this->nullable($row['checksum'] ?? null),
                'status' => $this->status($row['status'] ?? 'active'),
            ];
            $asset = $sourceId !== null ? MediaAsset::where('source_id', $sourceId)->first() : null;
            if (! $asset) {
                $asset = MediaAsset::where('disk', $disk)->where('path', $path)->first();
            }
            $asset ??= new MediaAsset();
            $payload = ['disk' => $disk, 'path' => $path] + $attributes;
            if ($sourceId !== null) $payload['source_id'] = $sourceId;
            $asset->fill($payload);
            $asset->save();
        });

        $this->importSimpleEntity($directory . '/product_media.csv', $totals, 'product_media', function (array $row) {
            $productSource = trim((string) ($row['product_source_id'] ?? ''));
            $assetSource = trim((string) ($row['media_source_id'] ?? ''));
            $product = Product::where('source_id', $productSource)->first();
            $asset = MediaAsset::where('source_id', $assetSource)->first();
            if (! $product || ! $asset) throw new \RuntimeException("unknown product_source_id/media_source_id {$productSource}/{$assetSource}");
            $pivot = ['role' => $this->role($row['role'] ?? 'gallery', ['main', 'gallery', 'thumbnail', 'detail', 'other']), 'position' => max(0, min(65535, (int) ($row['position'] ?? 0))), 'updated_at' => now(), 'created_at' => now()];
            DB::table('product_media')->updateOrInsert(['product_id' => $product->id, 'media_asset_id' => $asset->id], $pivot);
        });

        $this->importSimpleEntity($directory . '/category_media.csv', $totals, 'category_media', function (array $row) {
            $categorySource = trim((string) ($row['category_source_id'] ?? ''));
            $assetSource = trim((string) ($row['media_source_id'] ?? ''));
            $category = Category::where('source_id', $categorySource)->first();
            $asset = MediaAsset::where('source_id', $assetSource)->first();
            if (! $category || ! $asset) throw new \RuntimeException("unknown category_source_id/media_source_id {$categorySource}/{$assetSource}");
            $pivot = ['role' => $this->role($row['role'] ?? 'banner', ['banner', 'thumbnail', 'icon', 'other']), 'position' => max(0, min(65535, (int) ($row['position'] ?? 0))), 'updated_at' => now(), 'created_at' => now()];
            DB::table('category_media')->updateOrInsert(['category_id' => $category->id, 'media_asset_id' => $asset->id], $pivot);
        });

        $this->newLine();
        $this->info('Import finished (no records were deleted).');
        foreach ($totals as $name => $count) $this->line("{$name}: {$count}");
        $this->line('Run php artisan picklymart:reconcile-media media --disk=public after copying files.');
        return self::SUCCESS;
    }

    private function importSimpleEntity(string $path, array &$totals, string $key, callable $import): void
    {
        foreach ($this->readCsv($path) as $row) {
            try {
                $import($row);
                $totals[$key]++;
            } catch (Throwable $e) {
                $totals['skipped']++;
                $this->warn(basename($path) . ' row skipped: ' . $e->getMessage());
            }
        }
    }

    private function readCsv(string $path): array
    {
        if (! is_file($path)) return [];
        $handle = fopen($path, 'r');
        if ($handle === false) return [];
        try {
            $headers = fgetcsv($handle, null, ',', '"', '\\');
            if (! $headers) return [];
            $headers = array_map(fn ($header) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)), $headers);
            $rows = [];
            while (($values = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
                if (count($values) === 1 && trim((string) $values[0]) === '') continue;
                $values = array_pad($values, count($headers), null);
                $rows[] = array_combine($headers, array_slice($values, 0, count($headers)));
            }
            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function slug(mixed $slug, string $fallback): string
    {
        $value = trim((string) $slug);
        return Str::slug($value !== '' ? $value : $fallback) ?: Str::random(12);
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function numberOrNull(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') return null;
        if (! is_numeric($value) || (float) $value < 0) throw new \RuntimeException("invalid non-negative price: {$value}");
        return number_format((float) $value, 2, '.', '');
    }

    private function integerOrNull(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') return null;
        if (! ctype_digit($value)) throw new \RuntimeException("invalid integer: {$value}");
        return (int) $value;
    }

    private function status(mixed $value): string
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['active', 'inactive', 'draft', 'missing'], true) ? $value : 'active';
    }

    private function role(mixed $value, array $allowed): string
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, $allowed, true) ? $value : $allowed[0];
    }
}
