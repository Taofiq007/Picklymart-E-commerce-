@extends('admin.layout')

@section('title', 'Products')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-[0.17em] text-[#62816a]">Catalog directory</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Products</h1><p class="mt-2 text-sm text-[#778278]">Manage local product records used by the storefront and catalog API.</p></div>
        <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-[#176243] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#104d33]">＋ Add product</a>
    </div>
    <form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-2xl border border-[#e6ebe4] bg-white p-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, SKU, or source ID" class="min-w-56 flex-1 rounded-xl border border-[#dfe5dd] bg-[#fbfcfa] px-4 py-2.5 text-sm outline-none focus:border-[#42845a]">
        <select name="status" class="rounded-xl border border-[#dfe5dd] bg-white px-3 py-2.5 text-sm"><option value="">All statuses</option>@foreach (['active', 'inactive', 'draft'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <button class="rounded-xl bg-[#edf4ed] px-4 py-2.5 text-sm font-semibold text-[#306746]">Filter</button>
    </form>
    <div class="mt-5 overflow-hidden rounded-2xl border border-[#e6ebe4] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-[#f8faf7] text-xs uppercase tracking-wide text-[#859084]"><tr><th class="px-5 py-3.5 font-semibold">Product</th><th class="px-5 py-3.5 font-semibold">Category / brand</th><th class="px-5 py-3.5 font-semibold">Price</th><th class="px-5 py-3.5 font-semibold">Media</th><th class="px-5 py-3.5 font-semibold">Status</th><th class="px-5 py-3.5"></th></tr></thead>
                <tbody class="divide-y divide-[#edf0eb]">
                    @forelse ($products as $product)
                        <tr class="hover:bg-[#fcfdfb]">
                            <td class="px-5 py-4"><p class="font-semibold text-[#2b3b2f]">{{ $product->name }}</p><p class="mt-1 text-xs text-[#8a958a]">{{ $product->sku ?: 'No SKU' }} · {{ $product->slug }}</p></td>
                            <td class="px-5 py-4"><p>{{ $product->category?->name ?? '—' }}</p><p class="mt-1 text-xs text-[#8a958a]">{{ $product->brand?->name ?? 'No brand' }}</p></td>
                            <td class="px-5 py-4 font-medium">{{ $product->sale_price !== null || $product->price !== null ? '৳'.number_format((float) ($product->sale_price ?? $product->price), 2) : '—' }}</td>
                            <td class="px-5 py-4">{{ $product->mediaAssets->count() }} linked</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->status === 'active' ? 'bg-[#eaf5ec] text-[#36734b]' : 'bg-[#f1f2ef] text-[#737d73]' }}">{{ ucfirst($product->status) }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-[#32714a] hover:underline">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center"><p class="font-semibold">No products found</p><p class="mt-1 text-sm text-[#879187]">Add a product or change the filters.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())<div class="border-t border-[#edf0eb] px-5 py-4">{{ $products->links() }}</div>@endif
    </div>
@endsection
