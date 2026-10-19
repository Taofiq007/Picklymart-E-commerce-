<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Picklymart</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f6f3] font-sans text-[#26332b] antialiased">
    <header class="sticky top-0 z-30 border-b border-white/10 bg-[#124638] text-white shadow-sm">
        <div class="mx-auto flex h-[68px] max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-white/10">
                    <svg viewBox="0 0 24 24" fill="none" class="size-6 text-[#b8e2c3]" aria-hidden="true">
                        <path d="M5 8.5h14l-1.1 11H6.1L5 8.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M9 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="text-lg font-semibold tracking-tight">pickly<span class="text-[#a7d9b2]">mart</span><span class="ml-2 hidden text-xs font-medium tracking-[0.12em] text-white/55 sm:inline">ADMIN</span></span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('storefront.index') }}" class="hidden rounded-lg px-3 py-2 text-sm text-white/80 transition hover:bg-white/10 hover:text-white sm:inline-flex">View storefront</a>
                <span class="hidden text-sm text-white/70 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-white/20 px-3 py-2 text-sm font-medium transition hover:bg-white/10">Sign out</button>
                </form>
            </div>
        </div>
    </header>

    <div class="mx-auto flex max-w-[1600px]">
        <aside class="sticky top-[68px] hidden h-[calc(100vh-68px)] w-64 shrink-0 flex-col border-r border-[#e5eae3] bg-white px-4 py-6 lg:flex">
            <p class="px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-[#9aa59a]">Workspace</p>
            <nav class="mt-3 flex flex-col gap-1">
                @php
                    $links = [
                        ['admin.dashboard', 'Overview', '⌂'],
                        ['admin.products.*', 'Products', '▦'],
                        ['admin.categories.*', 'Categories', '◫'],
                        ['admin.brands.*', 'Brands', '◇'],
                        ['admin.media-assets.*', 'Media repository', '▧'],
                        ['admin.integrations.*', 'API integrations', '⌘'],
                    ];
                @endphp
                @foreach ($links as [$pattern, $label, $icon])
                    <a href="{{ route(str_replace('.*', '.index', $pattern) === 'admin.dashboard' ? 'admin.dashboard' : str_replace('.*', '.index', $pattern)) }}"
                       class="{{ request()->routeIs($pattern) ? 'bg-[#eaf3ec] font-semibold text-[#176443]' : 'font-medium text-[#68746b] hover:bg-[#f4f7f3] hover:text-[#334d3b]' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition">
                        <span class="grid size-7 place-items-center rounded-lg bg-current/5 text-base">{{ $icon }}</span>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <div class="mt-auto rounded-2xl bg-[#f2f6f0] p-4">
                <p class="text-sm font-semibold text-[#31553b]">Catalog & media</p>
                <p class="mt-1 text-xs leading-5 text-[#758477]">Manage local product data and connect files from your central media repository.</p>
            </div>
        </aside>

        <main class="min-w-0 flex-1 px-4 pb-12 pt-6 sm:px-6 lg:px-9 lg:pt-8">
            <nav aria-label="Admin navigation" class="mb-5 flex gap-2 overflow-x-auto lg:hidden">
                <a href="{{ route('admin.dashboard') }}" class="shrink-0 rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#376246] shadow-sm">Overview</a>
                <a href="{{ route('admin.products.index') }}" class="shrink-0 rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#376246] shadow-sm">Products</a>
                <a href="{{ route('admin.categories.index') }}" class="shrink-0 rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#376246] shadow-sm">Categories</a>
                <a href="{{ route('admin.brands.index') }}" class="shrink-0 rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#376246] shadow-sm">Brands</a>
                <a href="{{ route('admin.media-assets.index') }}" class="shrink-0 rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#376246] shadow-sm">Media</a>
                <a href="{{ route('admin.integrations.index') }}" class="shrink-0 rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#376246] shadow-sm">API</a>
            </nav>

            @if (session('status'))
                <div role="status" class="mb-5 rounded-xl border border-[#c9e3ce] bg-[#eff8f0] px-4 py-3 text-sm font-medium text-[#285b38]">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Please review the following:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
