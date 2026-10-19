@extends('admin.layout')

@section('title', $category->exists ? 'Edit category' : 'Add category')

@section('content')
    <div class="flex items-center gap-3 text-sm text-[#778278]"><a href="{{ route('admin.categories.index') }}" class="hover:text-[#32714a]">Categories</a><span>/</span><span class="text-[#354638]">{{ $category->exists ? 'Edit category' : 'New category' }}</span></div>
    <h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ $category->exists ? 'Edit category' : 'Add a category' }}</h1>
    <p class="mt-2 text-sm text-[#778278]">Build a stable, parent-linked directory that can be shared with the catalog API.</p>
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="mt-6">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <div class="grid gap-5 xl:grid-cols-[1fr_340px]">
            <div class="flex flex-col gap-5">
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
                    <h2 class="font-semibold">Directory details</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><label for="name" class="mb-1.5 block text-sm font-medium">Name <span class="text-red-500">*</span></label><input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="180" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div><label for="source_id" class="mb-1.5 block text-sm font-medium">External source ID</label><input id="source_id" name="source_id" value="{{ old('source_id', $category->source_id) }}" maxlength="191" placeholder="e.g. category-123" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div><label for="slug" class="mb-1.5 block text-sm font-medium">URL slug</label><input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="191" placeholder="Generated from name" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]"></div>
                        <div class="sm:col-span-2"><label for="description" class="mb-1.5 block text-sm font-medium">Description</label><textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]">{{ old('description', $category->description) }}</textarea></div>
                    </div>
                </section>
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
                    <h2 class="font-semibold">Category media</h2>
                    <p class="mt-1 text-sm text-[#7e897e]">Selected repository assets are mapped as ordered category banners.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @forelse ($assets as $asset)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#e8ece6] p-3 transition hover:border-[#a8c8ac] has-[:checked]:border-[#67a077] has-[:checked]:bg-[#f2f8f2]">
                                <input type="checkbox" name="media_asset_ids[]" value="{{ $asset->id }}" @checked(in_array($asset->id, old('media_asset_ids', $selectedAssets))) class="rounded border-[#cbd4c9] text-[#246b43] focus:ring-[#246b43]">
                                <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-lg bg-[#f0f4ed]">@if ($asset->publicUrl())<img src="{{ $asset->publicUrl() }}" alt="" class="size-full object-cover">@else<span class="text-lg text-[#78907b]">▧</span>@endif</span>
                                <span class="min-w-0"><span class="block truncate text-sm font-semibold">{{ $asset->filename }}</span><span class="mt-1 block truncate text-xs text-[#899389]">{{ $asset->source_id ?: $asset->path }}</span></span>
                            </label>
                        @empty
                            <p class="text-sm text-[#7e897e]">No active assets. <a href="{{ route('admin.media-assets.index') }}" class="font-semibold text-[#32714a] underline">Open the repository</a>.</p>
                        @endforelse
                    </div>
                </section>
            </div>
            <aside class="flex flex-col gap-5">
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5">
                    <h2 class="font-semibold">Placement</h2>
                    <div class="mt-4 flex flex-col gap-4">
                        <div><label for="parent_id" class="mb-1.5 block text-sm font-medium">Parent category</label><select id="parent_id" name="parent_id" class="w-full rounded-xl border border-[#dfe5dd] bg-white px-3 py-2.5 text-sm"><option value="">No parent (root)</option>@foreach ($parents as $parent)<option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>@endforeach</select><p class="mt-1.5 text-xs text-[#879187]">The API prevents circular category trees.</p></div>
                        <div><label for="status" class="mb-1.5 block text-sm font-medium">Status</label><select id="status" name="status" class="w-full rounded-xl border border-[#dfe5dd] bg-white px-3 py-2.5 text-sm">@foreach (['draft', 'active', 'inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $category->status ?: 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                    </div>
                </section>
                <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5">
                    <button class="w-full rounded-xl bg-[#176243] px-4 py-3 text-sm font-semibold text-white hover:bg-[#104d33]">{{ $category->exists ? 'Save category' : 'Create category' }}</button>
                    <a href="{{ route('admin.categories.index') }}" class="mt-3 block text-center text-sm font-medium text-[#727e73] hover:text-[#354638]">Cancel</a>
                    @if ($category->exists)
                        <button form="delete-category" class="mt-5 w-full border-t border-[#edf0eb] pt-4 text-sm font-medium text-red-600 hover:text-red-800">Delete category</button>
                    @endif
                </section>
            </aside>
        </div>
    </form>
    @if ($category->exists)
        <form id="delete-category" method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Products will become uncategorized and its children will be promoted to root categories.')">@csrf @method('DELETE')</form>
    @endif
@endsection
