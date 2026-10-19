@extends('admin.layout')

@section('title', 'Overview')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.17em] text-[#62816a]">Workspace overview</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#26392d]">Good to see you, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-sm text-[#778278]">Your local Picklymart catalog and central media connections.</p>
        </div>
        <a href="{{ route('storefront.index') }}" class="rounded-xl border border-[#dce4d9] bg-white px-4 py-2.5 text-sm font-semibold text-[#43644b] shadow-sm hover:bg-[#f9fbf8]">View storefront</a>
    </div>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Products', $counts['products'], route('admin.products.index'), '▦', 'bg-[#eaf3ec] text-[#286543]'],
            ['Categories', $counts['categories'], route('admin.categories.index'), '◫', 'bg-[#eef0fb] text-[#535a9a]'],
            ['Brands', $counts['brands'], route('admin.brands.index'), '◇', 'bg-[#fbf2e6] text-[#9a6a2b]'],
            ['Media assets', $counts['assets'], route('admin.media-assets.index'), '▧', 'bg-[#f6edf5] text-[#925982]'],
        ] as [$label, $count, $url, $icon, $iconClass])
            <a href="{{ $url }}" class="group rounded-2xl border border-[#e6ebe4] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-[#758075]">{{ $label }}</span>
                    <span class="grid size-10 place-items-center rounded-xl {{ $iconClass }} text-xl">{{ $icon }}</span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-[#2b3b2f]">{{ number_format($count) }}</p>
                <span class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-[#43805a]">Manage {{ strtolower($label) }} <span class="transition group-hover:translate-x-1">→</span></span>
            </a>
        @endforeach
    </div>

    @if ($counts['missingAssets'] > 0)
        <a href="{{ route('admin.media-assets.index', ['status' => 'missing']) }}" class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span><strong>{{ $counts['missingAssets'] }} media {{ \Illuminate\Support\Str::plural('file', $counts['missingAssets']) }}</strong> are marked missing from storage.</span>
            <span class="font-semibold">Review assets →</span>
        </a>
    @endif

    <div class="mt-8 grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="overflow-hidden rounded-2xl border border-[#e6ebe4] bg-white">
            <div class="flex items-center justify-between border-b border-[#edf0eb] px-5 py-4">
                <div><h2 class="font-semibold">Recently updated products</h2><p class="mt-1 text-xs text-[#8a958a]">Local catalog directory</p></div>
                <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-[#32714a] hover:underline">View all</a>
            </div>
            <div class="divide-y divide-[#f0f2ee]">
                @forelse ($recentProducts as $product)
                    <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-[#fafbf9]">
                        <div class="min-w-0"><p class="truncate text-sm font-semibold">{{ $product->name }}</p><p class="mt-1 text-xs text-[#899389]">{{ $product->category?->name ?? 'Uncategorized' }} · {{ $product->sku ?: $product->slug }}</p></div>
                        <span class="{{ $product->status === 'active' ? 'bg-[#eaf5ec] text-[#36734b]' : 'bg-[#f2f3f1] text-[#737d73]' }} rounded-full px-2.5 py-1 text-xs font-medium">{{ ucfirst($product->status) }}</span>
                    </a>
                @empty
                    <p class="px-5 py-8 text-sm text-[#7b867b]">No products have been added yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-[0.14em] text-[#62816a]">Central media</p><h2 class="mt-2 text-lg font-semibold">Asset mapping</h2></div>
                <span class="grid size-10 place-items-center rounded-xl bg-[#f3eef5] text-xl text-[#925982]">▧</span>
            </div>
            <p class="mt-2 text-sm leading-6 text-[#778278]">Upload local files into the shared repository, register external paths, and map assets to products or category banners.</p>
            <a href="{{ route('admin.media-assets.index') }}" class="mt-5 inline-flex rounded-lg bg-[#edf4ed] px-4 py-2.5 text-sm font-semibold text-[#306746] hover:bg-[#e3eee3]">Open media repository</a>
            <div class="mt-5 border-t border-[#edf0eb] pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#899389]">API connection</p>
                <p class="mt-1 text-sm text-[#667268]">Issue a scoped catalog token for connected services.</p>
                <a href="{{ route('admin.integrations.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#32714a] hover:underline">Configure API access →</a>
            </div>
        </section>
    </div>
@endsection
