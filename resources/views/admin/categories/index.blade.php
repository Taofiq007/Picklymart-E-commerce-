@extends('admin.layout')

@section('title', 'Categories')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-[0.17em] text-[#62816a]">Product taxonomy</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Categories</h1><p class="mt-2 text-sm text-[#778278]">Organize the local directory into a product category tree.</p></div>
        <a href="{{ route('admin.categories.create') }}" class="rounded-xl bg-[#176243] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#104d33]">＋ Add category</a>
    </div>
    <form method="GET" class="mt-6 flex gap-3 rounded-2xl border border-[#e6ebe4] bg-white p-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search categories" class="min-w-0 flex-1 rounded-xl border border-[#dfe5dd] bg-[#fbfcfa] px-4 py-2.5 text-sm outline-none focus:border-[#42845a]">
        <button class="rounded-xl bg-[#edf4ed] px-4 py-2.5 text-sm font-semibold text-[#306746]">Search</button>
    </form>
    <div class="mt-5 overflow-hidden rounded-2xl border border-[#e6ebe4] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="bg-[#f8faf7] text-xs uppercase tracking-wide text-[#859084]"><tr><th class="px-5 py-3.5 font-semibold">Category</th><th class="px-5 py-3.5 font-semibold">Parent</th><th class="px-5 py-3.5 font-semibold">Products</th><th class="px-5 py-3.5 font-semibold">Assets</th><th class="px-5 py-3.5 font-semibold">Status</th><th class="px-5 py-3.5"></th></tr></thead>
                <tbody class="divide-y divide-[#edf0eb]">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-[#fcfdfb]">
                            <td class="px-5 py-4"><p class="font-semibold">{{ $category->name }}</p><p class="mt-1 text-xs text-[#899389]">{{ $category->slug }} · {{ $category->source_id ?: 'No source ID' }}</p></td>
                            <td class="px-5 py-4">{{ $category->parent?->name ?? 'Root category' }}</td>
                            <td class="px-5 py-4">{{ $category->products_count }}</td>
                            <td class="px-5 py-4">{{ $category->mediaAssets->count() }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->status === 'active' ? 'bg-[#eaf5ec] text-[#36734b]' : 'bg-[#f1f2ef] text-[#737d73]' }}">{{ ucfirst($category->status) }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('admin.categories.edit', $category) }}" class="font-semibold text-[#32714a] hover:underline">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-[#7b867b]">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())<div class="border-t border-[#edf0eb] px-5 py-4">{{ $categories->links() }}</div>@endif
    </div>
@endsection
