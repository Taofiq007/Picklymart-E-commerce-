<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Discover everyday finds at Picklymart.">
    <title>Picklymart — Everyday finds, made easy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8f5] font-sans text-[#202820] antialiased">
    <div class="bg-[#173d2b] px-4 py-2.5 text-center text-xs font-medium tracking-wide text-white sm:text-sm">
        Good finds for everyday life <span class="px-2 text-[#9fc7a6]">·</span> Welcome to Picklymart
    </div>

    <header class="border-b border-[#0d3c31] bg-[#124638] text-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-5 py-5 lg:flex-nowrap lg:px-8">
            <a href="{{ route('storefront.index') }}" class="flex shrink-0 items-center gap-2.5" aria-label="Picklymart home">
                <span class="grid size-10 place-items-center rounded-2xl bg-white/10 text-[#b8e2c3]">
                    <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
                        <path d="M5 8.5h14l-1.1 11H6.1L5 8.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M9 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <path d="m9.5 14 1.7 1.7 3.6-3.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-xl font-semibold tracking-tight">pickly<span class="text-[#a7d9b2]">mart</span></span>
            </a>

            <form action="{{ route('storefront.index') }}" method="GET" class="order-3 flex w-full lg:order-none lg:mx-auto lg:max-w-xl">
                @if ($categorySlug)
                    <input type="hidden" name="category" value="{{ $categorySlug }}">
                @endif
                <label for="catalog-search" class="sr-only">Search products</label>
                <div class="flex w-full items-center rounded-xl border border-white/15 bg-white px-4 text-[#28382b] transition focus-within:border-[#a7d9b2] focus-within:ring-4 focus-within:ring-white/15">
                    <svg viewBox="0 0 24 24" fill="none" class="mr-3 size-5 shrink-0 text-[#758074]" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.7"/>
                        <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                    <input id="catalog-search" name="q" value="{{ $search }}" placeholder="Search for something good..." class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm outline-none placeholder:text-[#8b9388] focus:ring-0">
                    <button type="submit" class="ml-3 rounded-lg bg-[#246b43] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#195735]">Search</button>
                </div>
            </form>

            <div class="ml-auto flex items-center gap-2 text-sm text-white/80">
                <span class="hidden size-9 place-items-center rounded-full bg-white/10 text-[#b8e2c3] sm:grid">
                    <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                        <path d="M12 21s7-3.8 7-10V5l-7-2-7 2v6c0 6.2 7 10 7 10Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="hidden font-medium sm:inline">Shop with ease</span>
                <a href="{{ auth()->user()?->is_admin ? route('admin.dashboard') : route('login') }}" class="rounded-lg border border-white/20 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10 sm:text-sm">
                    {{ auth()->user()?->is_admin ? 'Admin' : 'Admin sign in' }}
                </a>
            </div>
        </div>

        <nav aria-label="Shop categories" class="border-t border-white/10 bg-white text-[#26332b]">
            <div class="mx-auto flex max-w-7xl items-center gap-2 overflow-x-auto px-5 py-3 lg:px-8">
                <a href="{{ route('storefront.index') }}" class="{{ !$categorySlug ? 'bg-[#eaf3eb] text-[#246b43]' : 'text-[#61695f] hover:bg-[#f3f5f1]' }} shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition">All products</a>
                @foreach ($categories as $category)
                    <a href="{{ route('storefront.index', array_filter(['category' => $category->slug, 'q' => $search])) }}" class="{{ $categorySlug === $category->slug ? 'bg-[#eaf3eb] text-[#246b43]' : 'text-[#61695f] hover:bg-[#f3f5f1]' }} shrink-0 rounded-full px-4 py-2 text-sm font-medium transition">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-7xl px-5 pb-16 pt-7 lg:px-8 lg:pt-10">
        <section class="relative isolate overflow-hidden rounded-[2rem] bg-[#e8f1e5]">
            <div class="absolute -right-12 -top-24 -z-10 size-80 rounded-full bg-[#d5e7d1] sm:right-20 sm:size-96"></div>
            <div class="absolute -bottom-36 right-1/4 -z-10 size-72 rounded-full border-[36px] border-white/30"></div>
            <div class="grid min-h-[330px] items-center gap-5 px-7 py-9 sm:px-12 lg:grid-cols-[1.2fr_0.8fr] lg:px-16 lg:py-12">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#d2e2cf] bg-white/70 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.15em] text-[#3c704a]">
                        A little something for every day
                    </span>
                    <h1 class="mt-5 max-w-xl text-4xl font-semibold leading-[1.08] tracking-tight text-[#1f3928] sm:text-5xl lg:text-[3.5rem]">
                        Everyday finds,<br class="hidden sm:block"> made a little easier.
                    </h1>
                    <p class="mt-4 max-w-lg text-base leading-7 text-[#576b59] sm:text-lg">
                        Browse thoughtful picks for your home, your tech, and all the little moments in between.
                    </p>
                    <a href="#products" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-[#246b43] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#195735]">
                        Explore the collection
                        <svg viewBox="0 0 24 24" fill="none" class="size-4" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
                <div class="hidden justify-self-center lg:block" aria-hidden="true">
                    <div class="relative grid size-64 place-items-center rounded-full bg-[#dcebd7]">
                        <div class="absolute right-9 top-7 size-5 rounded-full bg-[#b5cda9]"></div>
                        <div class="absolute bottom-10 left-8 size-3 rounded-full bg-[#a4c293]"></div>
                        <div class="relative h-48 w-28 rotate-[-8deg] rounded-[2rem] border-[7px] border-[#25372c] bg-[#fffdf7] shadow-[0_22px_45px_-18px_rgba(31,57,40,0.55)]">
                            <div class="absolute left-1/2 top-1.5 h-2 w-9 -translate-x-1/2 rounded-full bg-[#25372c]"></div>
                            <div class="absolute inset-x-2 top-8 bottom-3 overflow-hidden rounded-[1.25rem] bg-gradient-to-br from-[#dcebd7] via-[#f0ead8] to-[#bfd6ba]">
                                <div class="absolute -right-4 top-7 size-20 rounded-full bg-[#e8c98d]/70"></div>
                                <div class="absolute -left-3 bottom-5 h-20 w-24 rotate-[-20deg] rounded-t-full bg-[#4d8a62]/80"></div>
                                <div class="absolute bottom-0 h-10 w-full bg-[#39764f]"></div>
                                <div class="absolute bottom-4 left-5 h-16 w-1 rounded-full bg-[#f5eddb]"></div>
                            </div>
                        </div>
                        <span class="absolute bottom-8 right-0 rounded-2xl bg-white px-4 py-2 text-xs font-semibold text-[#346044] shadow-lg">Find your everyday favourite</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="products" class="scroll-mt-6 pt-12">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.17em] text-[#62816a]">Picked for you</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">
                        {{ $categorySlug ? ($categories->firstWhere('slug', $categorySlug)?->name ?? 'Collection') : ($search !== '' ? 'Search results' : 'Explore our collection') }}
                    </h2>
                </div>
                <p class="text-sm text-[#758074]">
                    {{ $products->total() }} {{ \Illuminate\Support\Str::plural('item', $products->total()) }}
                    @if ($search !== '')
                        for <span class="font-semibold text-[#354638]">“{{ $search }}”</span>
                    @endif
                </p>
            </div>

            @if ($products->isNotEmpty())
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <article class="group overflow-hidden rounded-2xl border border-[#e9ebe5] bg-white transition duration-200 hover:-translate-y-1 hover:border-[#c9d9c7] hover:shadow-[0_18px_38px_-24px_rgba(34,68,43,0.32)]">
                            <div class="relative aspect-[4/3] overflow-hidden bg-[#edf2e9]">
                                @if ($product->storefront_image)
                                    <img src="{{ $product->storefront_image }}" alt="{{ $product->name }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                @else
                                    <div class="grid size-full place-items-center bg-[radial-gradient(circle_at_70%_25%,#d9e8d2_0,transparent_32%),linear-gradient(145deg,#f1f3e9,#e0ebdd)]">
                                        <div class="grid size-24 place-items-center rounded-[2rem] border border-white/80 bg-white/70 text-[#62816a] shadow-sm">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-11" aria-hidden="true">
                                                <path d="M7 3.8h10a1.8 1.8 0 0 1 1.8 1.8v13.1a1.5 1.5 0 0 1-1.5 1.5H6.7a1.5 1.5 0 0 1-1.5-1.5V5.6A1.8 1.8 0 0 1 7 3.8Z" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M9 7h6M10 17.2h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                                @if ($product->sale_price && $product->price && (float) $product->sale_price < (float) $product->price)
                                    <span class="absolute left-3 top-3 rounded-full bg-[#246b43] px-3 py-1 text-xs font-semibold text-white">Special price</span>
                                @endif
                            </div>
                            <div class="p-5">
                                <p class="text-xs font-medium uppercase tracking-[0.12em] text-[#7b887a]">{{ $product->category?->name ?? 'Picklymart finds' }}</p>
                                <h3 class="mt-2 line-clamp-2 min-h-12 text-base font-semibold leading-6 text-[#28382b]">{{ $product->name }}</h3>
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-baseline gap-x-2">
                                        @if ($product->sale_price)
                                            <span class="text-lg font-semibold text-[#246b43]">৳{{ number_format((float) $product->sale_price, 0) }}</span>
                                            @if ($product->price)
                                                <span class="text-sm text-[#90978e] line-through">৳{{ number_format((float) $product->price, 0) }}</span>
                                            @endif
                                        @elseif ($product->price)
                                            <span class="text-lg font-semibold text-[#246b43]">৳{{ number_format((float) $product->price, 0) }}</span>
                                        @else
                                            <span class="text-sm font-medium text-[#667165]">Price on request</span>
                                        @endif
                                    </div>
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#f0f5ef] text-[#39724b] transition group-hover:bg-[#246b43] group-hover:text-white" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-4"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($products->hasPages())
                    <div class="mt-8">{{ $products->links() }}</div>
                @endif
            @else
                <div class="mt-6 rounded-2xl border border-dashed border-[#d9dfd5] bg-white px-6 py-16 text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#edf3ea] text-[#608068]">
                        <svg viewBox="0 0 24 24" fill="none" class="size-7" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.6"/><path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                    </span>
                    <h3 class="mt-4 text-lg font-semibold">No products found</h3>
                    <p class="mt-1 text-sm text-[#758074]">Try a different search or browse all of our products.</p>
                    <a href="{{ route('storefront.index') }}" class="mt-5 inline-flex rounded-lg bg-[#246b43] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#195735]">View all products</a>
                </div>
            @endif
        </section>
    </main>

    <footer class="border-t border-[#e8ebe4] bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-7 text-sm text-[#758074] sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <p class="font-semibold text-[#344c39]">pickly<span class="text-[#287647]">mart</span></p>
            <p>Everyday finds, made a little easier.</p>
        </div>
    </footer>
</body>
</html>
