<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in — Picklymart</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f6f3] font-sans text-[#26332b] antialiased">
    <header class="bg-[#124638] text-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 lg:px-8">
            <a href="{{ route('storefront.index') }}" class="flex items-center gap-3 text-lg font-semibold">pickly<span class="text-[#a7d9b2]">mart</span></a>
            <a href="{{ route('storefront.index') }}" class="text-sm text-white/75 hover:text-white">Back to store</a>
        </div>
    </header>
    <main class="mx-auto grid min-h-[calc(100vh-64px)] max-w-6xl items-center gap-12 px-5 py-12 lg:grid-cols-2 lg:px-8">
        <section class="hidden lg:block">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#508064]">Picklymart workspace</p>
            <h1 class="mt-4 max-w-xl text-5xl font-semibold leading-tight tracking-tight text-[#173c2a]">Your catalog,<br>all in one place.</h1>
            <p class="mt-5 max-w-lg text-lg leading-8 text-[#6b786c]">Keep your local product directory, category tree, and central media mappings in sync.</p>
            <div class="mt-10 flex items-center gap-4 rounded-2xl border border-[#e3e9df] bg-white p-5 shadow-sm">
                <span class="grid size-12 place-items-center rounded-xl bg-[#eaf3ec] text-[#256444]">
                    <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-11Z" stroke="currentColor" stroke-width="1.6"/><path d="M8 8h8M8 12h8M8 16h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <div><p class="font-semibold">Local catalog management</p><p class="mt-1 text-sm text-[#778278]">Structured data. Connected media. Ready APIs.</p></div>
            </div>
        </section>
        <section class="mx-auto w-full max-w-md rounded-[1.75rem] border border-[#e8ece6] bg-white p-7 shadow-[0_24px_70px_-42px_rgba(20,63,41,0.4)] sm:p-9">
            <span class="grid size-12 place-items-center rounded-2xl bg-[#eaf3ec] text-[#246b43]">
                <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true"><path d="M12 3 5.5 6v5.3c0 4.3 2.7 7.9 6.5 9.7 3.8-1.8 6.5-5.4 6.5-9.7V6L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <h2 class="mt-5 text-2xl font-semibold tracking-tight">Welcome back</h2>
            <p class="mt-1 text-sm text-[#778278]">Sign in to your Picklymart admin account.</p>

            @if ($errors->any())
                <div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-7 flex flex-col gap-5">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-[#425046]">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="w-full rounded-xl border border-[#dfe5dd] bg-[#fbfcfa] px-4 py-3 text-sm outline-none transition focus:border-[#42845a] focus:ring-4 focus:ring-[#42845a]/10">
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-[#425046]">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border border-[#dfe5dd] bg-[#fbfcfa] px-4 py-3 text-sm outline-none transition focus:border-[#42845a] focus:ring-4 focus:ring-[#42845a]/10">
                </div>
                <label class="flex items-center gap-2 text-sm text-[#657166]">
                    <input type="checkbox" name="remember" value="1" class="rounded border-[#cbd4c9] text-[#246b43] focus:ring-[#246b43]">
                    Remember me
                </label>
                <button class="rounded-xl bg-[#176243] px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#104d33] focus:outline-none focus:ring-4 focus:ring-[#176243]/20">Sign in to admin</button>
            </form>
            <p class="mt-6 text-center text-xs leading-5 text-[#919a90]">Administrator accounts are provisioned locally by the system owner.</p>
        </section>
    </main>
</body>
</html>
