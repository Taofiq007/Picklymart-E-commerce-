@extends('admin.layout')

@section('title', 'Media repository')

@section('content')
    <div><p class="text-xs font-bold uppercase tracking-[0.17em] text-[#62816a]">Central media repository</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Media assets</h1><p class="mt-2 text-sm text-[#778278]">Register files in local storage, map them to the product/category directory, and reconcile missing paths.</p></div>

    <section class="mt-6 rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
        <h2 class="font-semibold">Add repository asset</h2>
        <p class="mt-1 text-sm text-[#7e897e]">Upload a new image or register a file that already exists on one of the configured repository disks.</p>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-[#e8ece6] bg-[#fbfcfa] p-4">
                <h3 class="text-sm font-semibold">Upload to local repository</h3>
                <p class="mt-1 text-xs text-[#899389]">A new file is saved on the public disk.</p>
                <form method="POST" action="{{ route('admin.media-assets.store') }}" enctype="multipart/form-data" class="mt-3 flex flex-col gap-3">
                    @csrf
                    <label for="upload-source-id" class="text-sm font-medium">Central source ID <span class="font-normal text-[#899389]">(optional)</span></label>
                    <input id="upload-source-id" name="source_id" value="{{ old('source_id') }}" maxlength="191" placeholder="e.g. image-4582" class="w-full rounded-lg border border-[#dfe5dd] bg-white px-3 py-2 text-sm outline-none focus:border-[#42845a]">
                    <label for="upload-file" class="text-sm font-medium">Image file</label>
                    <input id="upload-file" type="file" name="file" accept="image/jpeg,image/png,image/webp,image/gif" required class="w-full rounded-lg border border-[#dfe5dd] bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#edf4ed] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[#306746]">
                    <button class="self-start rounded-lg bg-[#176243] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#104d33]">Upload image</button>
                </form>
            </div>
            <div class="rounded-xl border border-[#e8ece6] bg-[#fbfcfa] p-4">
                <h3 class="text-sm font-semibold">Register existing repository file</h3>
                <p class="mt-1 text-xs text-[#899389]">The file must already exist and be an image.</p>
                <form method="POST" action="{{ route('admin.media-assets.store') }}" class="mt-3 flex flex-col gap-3">
                    @csrf
                    <label for="existing-source-id" class="text-sm font-medium">Central source ID <span class="font-normal text-[#899389]">(optional)</span></label>
                    <input id="existing-source-id" name="source_id" value="{{ old('source_id') }}" maxlength="191" placeholder="e.g. image-4582" class="w-full rounded-lg border border-[#dfe5dd] bg-white px-3 py-2 text-sm outline-none focus:border-[#42845a]">
                    <label for="existing-disk" class="text-sm font-medium">Configured storage disk</label>
                    <select id="existing-disk" name="disk" required class="rounded-lg border border-[#dfe5dd] bg-white px-3 py-2 text-sm"><option value="">Storage disk…</option>@foreach ($storageDisks as $disk)<option value="{{ $disk }}" @selected(old('disk') === $disk)>{{ $disk }}</option>@endforeach</select>
                    <label for="existing-path" class="text-sm font-medium">Repository-relative path</label>
                    <input id="existing-path" name="path" value="{{ old('path') }}" maxlength="255" required placeholder="e.g. catalog/products/image.png" class="min-w-0 rounded-lg border border-[#dfe5dd] bg-white px-3 py-2 text-sm">
                    <p class="text-xs text-[#909a90]">JPEG, PNG, WebP, or GIF. The image file remains in place.</p>
                    <button class="self-start rounded-lg bg-[#176243] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#104d33]">Register existing file</button>
                </form>
            </div>
        </div>
        <p class="mt-3 text-xs text-[#909a90]">New uploads are limited to 10 MB. Existing files stay on their configured disk and are inspected before registration.</p>
    </section>

    <form method="GET" class="mt-5 flex flex-wrap gap-3 rounded-2xl border border-[#e6ebe4] bg-white p-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search filename, path, or source ID" class="min-w-52 flex-1 rounded-xl border border-[#dfe5dd] bg-[#fbfcfa] px-4 py-2.5 text-sm outline-none focus:border-[#42845a]">
        <select name="status" class="rounded-xl border border-[#dfe5dd] bg-white px-3 py-2.5 text-sm"><option value="">All statuses</option>@foreach (['active', 'inactive', 'missing'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <button class="rounded-xl bg-[#edf4ed] px-4 py-2.5 text-sm font-semibold text-[#306746]">Filter</button>
    </form>

    <div class="mt-5 flex flex-col gap-4">
        @forelse ($assets as $asset)
            <article class="overflow-hidden rounded-2xl border border-[#e6ebe4] bg-white">
                <div class="grid gap-5 p-5 lg:grid-cols-[160px_1fr]">
                    <a href="{{ $asset->publicUrl() ?: '#' }}" target="_blank" rel="noreferrer" class="grid aspect-square place-items-center overflow-hidden rounded-xl bg-[#f0f4ed]">
                        @if ($asset->publicUrl())<img src="{{ $asset->publicUrl() }}" alt="{{ $asset->original_name ?: $asset->filename }}" class="size-full object-cover">@else<span class="text-3xl text-[#78907b]">▧</span>@endif
                    </a>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0"><h2 class="truncate font-semibold">{{ $asset->filename }}</h2><p class="mt-1 break-all text-xs text-[#899389]">{{ $asset->disk }}://{{ $asset->path }}</p><p class="mt-1 text-xs text-[#899389]">{{ $asset->source_id ?: 'No source ID' }} · {{ $asset->mime_type ?: 'Unknown type' }} · {{ $asset->size_bytes ? number_format($asset->size_bytes / 1024, 1).' KB' : 'Size unavailable' }}</p></div>
                            <form method="POST" action="{{ route('admin.media-assets.status', $asset) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <select name="status" class="rounded-lg border border-[#dfe5dd] bg-white px-2.5 py-2 text-xs">@foreach (['active', 'inactive', 'missing'] as $status)<option value="{{ $status }}" @selected($asset->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                                <button class="rounded-lg bg-[#edf4ed] px-3 py-2 text-xs font-semibold text-[#306746]">Save</button>
                            </form>
                        </div>
                        <div class="mt-4 grid gap-4 xl:grid-cols-2">
                            <div class="rounded-xl bg-[#f8faf7] p-3.5">
                                <h3 class="text-xs font-bold uppercase tracking-wide text-[#748075]">Product mappings</h3>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse ($asset->products as $product)
                                        <form method="POST" action="{{ route('admin.media-assets.products.detach', [$asset, $product]) }}" class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1.5 text-xs text-[#526254]">
                                            @csrf @method('DELETE')<span>{{ $product->name }}</span><button aria-label="Remove {{ $product->name }} mapping" class="font-bold text-red-500">×</button>
                                        </form>
                                    @empty
                                        <span class="text-xs text-[#929b91]">Not mapped</span>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ route('admin.media-assets.products.attach', $asset) }}" class="mt-3 flex flex-wrap gap-2">
                                    @csrf
                                    <select name="product_id" required class="min-w-0 flex-1 rounded-lg border border-[#dfe5dd] bg-white px-2.5 py-2 text-xs"><option value="">Choose product…</option>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select>
                                    <select name="role" class="rounded-lg border border-[#dfe5dd] bg-white px-2 py-2 text-xs"><option value="main">Main</option><option value="gallery">Gallery</option><option value="thumbnail">Thumbnail</option><option value="detail">Detail</option></select>
                                    <input type="number" name="position" min="0" max="65535" value="0" aria-label="Position" class="w-16 rounded-lg border border-[#dfe5dd] px-2 py-2 text-xs">
                                    <button class="rounded-lg bg-[#176243] px-3 py-2 text-xs font-semibold text-white">Map</button>
                                </form>
                            </div>
                            <div class="rounded-xl bg-[#f8faf7] p-3.5">
                                <h3 class="text-xs font-bold uppercase tracking-wide text-[#748075]">Category mappings</h3>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse ($asset->categories as $category)
                                        <form method="POST" action="{{ route('admin.media-assets.categories.detach', [$asset, $category]) }}" class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1.5 text-xs text-[#526254]">
                                            @csrf @method('DELETE')<span>{{ $category->name }}</span><button aria-label="Remove {{ $category->name }} mapping" class="font-bold text-red-500">×</button>
                                        </form>
                                    @empty
                                        <span class="text-xs text-[#929b91]">Not mapped</span>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ route('admin.media-assets.categories.attach', $asset) }}" class="mt-3 flex flex-wrap gap-2">
                                    @csrf
                                    <select name="category_id" required class="min-w-0 flex-1 rounded-lg border border-[#dfe5dd] bg-white px-2.5 py-2 text-xs"><option value="">Choose category…</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>
                                    <select name="role" class="rounded-lg border border-[#dfe5dd] bg-white px-2 py-2 text-xs"><option value="banner">Banner</option><option value="thumbnail">Thumbnail</option><option value="other">Other</option></select>
                                    <input type="number" name="position" min="0" max="65535" value="0" aria-label="Position" class="w-16 rounded-lg border border-[#dfe5dd] px-2 py-2 text-xs">
                                    <button class="rounded-lg bg-[#176243] px-3 py-2 text-xs font-semibold text-white">Map</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-[#d9dfd5] bg-white px-6 py-14 text-center"><p class="font-semibold">No media assets found</p><p class="mt-1 text-sm text-[#758074]">Upload a local image or search with different filters.</p></div>
        @endforelse
    </div>
    @if ($assets->hasPages())<div class="mt-5">{{ $assets->links() }}</div>@endif
@endsection
