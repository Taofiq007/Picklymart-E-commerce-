@extends('admin.layout')

@section('title', $product->exists ? 'Edit product' : 'Add product')

@section('content')
    <div class="flex items-center gap-3 text-sm text-[#778278]"><a href="{{ route('admin.products.index') }}" class="hover:text-[#32714a]">Products</a><span>/</span><span class="text-[#354638]">{{ $product->exists ? 'Edit product' : 'New product' }}</span></div>
    <div class="mt-3 flex flex-wrap items-end justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[0.17em] text-[#62816a]">Local product directory</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $product->exists ? 'Edit product' : 'Add a product' }}</h1><p class="mt-2 text-sm text-[#778278]">Product data stays in this local catalog and is available through the authenticated API.</p></div></div>

    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="mt-6">
        @csrf
        @if ($product->exists) @method('PUT') @endif
        <div class="grid gap-5 xl:grid-cols-[1fr_340px]">
            <div class="flex flex-col gap-5">
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
                    <h2 class="font-semibold">Product details</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><label for="name" class="mb-1.5 block text-sm font-medium">Product name <span class="text-red-500">*</span></label><input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="220" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a] focus:ring-4 focus:ring-[#42845a]/10"></div>
                        <div><label for="source_id" class="mb-1.5 block text-sm font-medium">External source ID</label><input id="source_id" name="source_id" value="{{ old('source_id', $product->source_id) }}" maxlength="191" placeholder="e.g. product-123" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div><label for="sku" class="mb-1.5 block text-sm font-medium">SKU</label><input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="100" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div><label for="slug" class="mb-1.5 block text-sm font-medium">URL slug</label><input id="slug" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="191" placeholder="Generated from product name" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div><label for="status" class="mb-1.5 block text-sm font-medium">Status</label><select id="status" name="status" class="w-full rounded-xl border border-[#dfe5dd] bg-white px-3.5 py-2.5 text-sm">@foreach (['draft', 'active', 'inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $product->status ?: 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                        <div class="sm:col-span-2"><label for="description" class="mb-1.5 block text-sm font-medium">Description</label><textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]">{{ old('description', $product->description) }}</textarea></div>
                    </div>
                </section>
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
                    <h2 class="font-semibold">Pricing</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-3">
                        <div><label for="price" class="mb-1.5 block text-sm font-medium">Regular price</label><input id="price" type="number" min="0" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div><label for="sale_price" class="mb-1.5 block text-sm font-medium">Sale price</label><input id="sale_price" type="number" min="0" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div><label for="currency" class="mb-1.5 block text-sm font-medium">Currency</label><input id="currency" name="currency" value="{{ old('currency', $product->currency ?: 'BDT') }}" maxlength="3" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm uppercase outline-none focus:border-[#42845a]"></div>
                    </div>
                </section>
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
                    <h2 class="font-semibold">Central media mappings</h2>
                    <p class="mt-1 text-sm text-[#7e897e]">Select assets from the repository. The first selected image is the main image; remaining images are gallery assets.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @forelse ($assets as $asset)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#e8ece6] p-3 transition hover:border-[#a8c8ac] has-[:checked]:border-[#67a077] has-[:checked]:bg-[#f2f8f2]">
                                <input type="checkbox" name="media_asset_ids[]" value="{{ $asset->id }}" @checked(in_array($asset->id, old('media_asset_ids', $selectedAssets))) class="rounded border-[#cbd4c9] text-[#246b43] focus:ring-[#246b43]">
                                <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-lg bg-[#f0f4ed]">
                                    @if ($asset->publicUrl())<img src="{{ $asset->publicUrl() }}" alt="" class="size-full object-cover">@else<span class="text-lg text-[#78907b]">▧</span>@endif
                                </span>
                                <span class="min-w-0"><span class="block truncate text-sm font-semibold">{{ $asset->filename }}</span><span class="mt-1 block truncate text-xs text-[#899389]">{{ $asset->source_id ?: $asset->path }}</span></span>
                            </label>
                        @empty
                            <p class="text-sm text-[#7e897e]">No active assets yet. <a href="{{ route('admin.media-assets.index') }}" class="font-semibold text-[#32714a] underline">Upload one</a>.</p>
                        @endforelse
                    </div>
                </section>
            </div>
            <aside class="flex flex-col gap-5">
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5">
                    <h2 class="font-semibold">Directory placement</h2>
                    <div class="mt-4 flex flex-col gap-4">
                        <div><label for="category_id" class="mb-1.5 block text-sm font-medium">Category</label><select id="category_id" name="category_id" class="w-full rounded-xl border border-[#dfe5dd] bg-white px-3 py-2.5 text-sm"><option value="">Uncategorized</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->parent ? $category->parent->name.' / ' : '' }}{{ $category->name }}</option>@endforeach</select></div>
                        <div><label for="brand_id" class="mb-1.5 block text-sm font-medium">Brand</label><select id="brand_id" name="brand_id" class="w-full rounded-xl border border-[#dfe5dd] bg-white px-3 py-2.5 text-sm"><option value="">No brand</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>@endforeach</select><a href="{{ route('admin.brands.index') }}" class="mt-2 inline-flex text-xs font-semibold text-[#32714a] hover:underline">Manage brands →</a></div>
                    </div>
                </section>
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5">
                    <button class="w-full rounded-xl bg-[#176243] px-4 py-3 text-sm font-semibold text-white hover:bg-[#104d33]">{{ $product->exists ? 'Save product' : 'Create product' }}</button>
                    <a href="{{ route('admin.products.index') }}" class="mt-3 block text-center text-sm font-medium text-[#727e73] hover:text-[#354638]">Cancel</a>
                    @if ($product->exists)
                        <button form="delete-product" class="mt-5 w-full border-t border-[#edf0eb] pt-4 text-sm font-medium text-red-600 hover:text-red-800">Delete product</button>
                    @endif
                </section>
            </aside>
        </div>
    </form>
    @if ($product->exists)
        <form id="delete-product" method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product? Its media mappings will be removed.')">@csrf @method('DELETE')</form>
    @endif
@endsection
