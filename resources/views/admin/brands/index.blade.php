@extends('admin.layout')

@section('title', 'Brands')

@section('content')
    <div><p class="text-xs font-bold uppercase tracking-[0.17em] text-[#62816a]">Catalog directory</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Brands</h1><p class="mt-2 text-sm text-[#778278]">Manage brand records referenced by products and the catalog API.</p></div>

    <section class="mt-6 rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
        <h2 class="font-semibold">Add a brand</h2>
        <form method="POST" action="{{ route('admin.brands.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @csrf
            <input name="name" value="{{ old('name') }}" required maxlength="180" placeholder="Brand name" class="rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]">
            <input name="source_id" value="{{ old('source_id') }}" maxlength="191" placeholder="Source ID (optional)" class="rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]">
            <input name="slug" value="{{ old('slug') }}" maxlength="191" placeholder="Slug (auto-generated)" class="rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]">
            <select name="status" class="rounded-xl border border-[#dfe5dd] bg-white px-3 py-2.5 text-sm"><option value="active">Active</option><option value="draft">Draft</option><option value="inactive">Inactive</option></select>
            <button class="rounded-xl bg-[#176243] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#104d33]">Add brand</button>
        </form>
    </section>

    <form method="GET" class="mt-5 flex gap-3 rounded-2xl border border-[#e6ebe4] bg-white p-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search brands" class="min-w-0 flex-1 rounded-xl border border-[#dfe5dd] bg-[#fbfcfa] px-4 py-2.5 text-sm outline-none focus:border-[#42845a]">
        <button class="rounded-xl bg-[#edf4ed] px-4 py-2.5 text-sm font-semibold text-[#306746]">Search</button>
    </form>
    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        @forelse ($brands as $brand)
            <article class="rounded-2xl border border-[#e6ebe4] bg-white p-5">
                <form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <div class="sm:col-span-2 flex items-center justify-between"><div><h2 class="font-semibold">{{ $brand->name }}</h2><p class="mt-1 text-xs text-[#899389]">{{ $brand->products_count }} products · {{ $brand->source_id ?: 'No source ID' }}</p></div><span class="rounded-full bg-[#edf4ed] px-2.5 py-1 text-xs font-semibold text-[#36734b]">{{ ucfirst($brand->status) }}</span></div>
                    <div><label class="mb-1 block text-xs font-medium text-[#7a857a]">Name</label><input name="name" value="{{ $brand->name }}" required maxlength="180" class="w-full rounded-lg border border-[#dfe5dd] px-3 py-2 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-medium text-[#7a857a]">Slug</label><input name="slug" value="{{ $brand->slug }}" maxlength="191" class="w-full rounded-lg border border-[#dfe5dd] px-3 py-2 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-medium text-[#7a857a]">Source ID</label><input name="source_id" value="{{ $brand->source_id }}" maxlength="191" class="w-full rounded-lg border border-[#dfe5dd] px-3 py-2 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-medium text-[#7a857a]">Status</label><select name="status" class="w-full rounded-lg border border-[#dfe5dd] bg-white px-3 py-2 text-sm">@foreach (['active', 'inactive', 'draft'] as $status)<option value="{{ $status }}" @selected($brand->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                    <div class="sm:col-span-2 flex items-center justify-between border-t border-[#edf0eb] pt-3"><button class="text-sm font-semibold text-[#32714a] hover:underline">Save changes</button></div>
                </form>
                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Delete this brand? Products will no longer reference it.')" class="mt-[-36px] flex justify-end">@csrf @method('DELETE')<button class="text-sm font-medium text-red-600 hover:underline">Delete</button></form>
            </article>
        @empty
            <p class="rounded-2xl border border-[#e6ebe4] bg-white p-8 text-center text-sm text-[#7b867b] xl:col-span-2">No brands found.</p>
        @endforelse
    </div>
    @if ($brands->hasPages())<div class="mt-5">{{ $brands->links() }}</div>@endif
@endsection
