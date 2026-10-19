@extends('admin.layout')

@section('title', 'API integrations')

@section('content')
    <div><p class="text-xs font-bold uppercase tracking-[0.17em] text-[#62816a]">API integration</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Connect your catalog</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-[#778278]">Issue a personal access token for a trusted API client. Catalog API endpoints require an administrator-owned Sanctum token.</p></div>

    @if ($newToken)
        <section class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5">
            <p class="font-semibold text-amber-950">Copy your new token now</p>
            <p class="mt-1 text-sm text-amber-900">For safety, the raw token is shown once and is not stored for display again.</p>
            <code class="mt-3 block overflow-x-auto rounded-xl bg-[#26332b] p-4 text-sm text-[#e5f6e8]">{{ $newToken }}</code>
        </section>
    @endif

    <div class="mt-6 grid gap-5 xl:grid-cols-[0.8fr_1.2fr]">
        <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
            <h2 class="font-semibold">Create API token</h2>
            <p class="mt-1 text-sm leading-6 text-[#7e897e]">Tokens receive catalog read/write access and can be revoked at any time.</p>
            <form method="POST" action="{{ route('admin.integrations.tokens.store') }}" class="mt-5 flex flex-col gap-3">
                @csrf
                <label for="token-name" class="text-sm font-medium">Client name</label>
                <input id="token-name" name="name" value="{{ old('name') }}" maxlength="80" required placeholder="e.g. Catalog sync service" class="rounded-xl border border-[#dfe5dd] px-3.5 py-2.5 text-sm outline-none focus:border-[#42845a]">
                <button class="rounded-xl bg-[#176243] px-4 py-3 text-sm font-semibold text-white hover:bg-[#104d33]">Generate token</button>
            </form>
        </section>
        <section class="rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
            <h2 class="font-semibold">API endpoints</h2>
            <p class="mt-1 text-sm text-[#7e897e]">Base URL: <code class="rounded bg-[#f3f6f1] px-1.5 py-0.5 text-[#31553b]">{{ url('/api/v1') }}</code></p>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[460px] text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-[#899389]"><tr><th class="py-2 font-semibold">Resource</th><th class="py-2 font-semibold">Endpoints</th><th class="py-2 font-semibold">Purpose</th></tr></thead>
                    <tbody class="divide-y divide-[#edf0eb]">
                        <tr><td class="py-3 font-mono text-xs">products</td><td class="py-3 text-xs">GET, POST, GET/{id}, PUT/{id}, DELETE/{id}</td><td class="py-3 text-xs text-[#788378]">Catalog products</td></tr>
                        <tr><td class="py-3 font-mono text-xs">categories</td><td class="py-3 text-xs">GET, POST, GET/{id}, PUT/{id}, DELETE/{id}</td><td class="py-3 text-xs text-[#788378]">Hierarchical directory</td></tr>
                        <tr><td class="py-3 font-mono text-xs">brands</td><td class="py-3 text-xs">GET, POST, GET/{id}, PUT/{id}, DELETE/{id}</td><td class="py-3 text-xs text-[#788378]">Brand directory</td></tr>
                        <tr><td class="py-3 font-mono text-xs">media-assets</td><td class="py-3 text-xs">GET, POST, GET/{id}</td><td class="py-3 text-xs text-[#788378]">Upload or register repository assets</td></tr>
                        <tr><td class="py-3 font-mono text-xs">products/{id}/media</td><td class="py-3 text-xs">POST, DELETE/{asset}</td><td class="py-3 text-xs text-[#788378]">Map central assets to products</td></tr>
                        <tr><td class="py-3 font-mono text-xs">categories/{id}/media</td><td class="py-3 text-xs">POST, DELETE/{asset}</td><td class="py-3 text-xs text-[#788378]">Map assets to categories</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 rounded-xl bg-[#f4f7f3] p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-[#68766a]">Authentication</p>
                <code class="mt-2 block break-all text-xs text-[#31553b]">Authorization: Bearer &lt;ADMIN_TOKEN&gt;</code>
                <code class="mt-1 block text-xs text-[#31553b]">Accept: application/json</code>
                <p class="mt-3 text-xs leading-5 text-[#657365]">To register an existing repository file, POST JSON to <code>/api/v1/media-assets</code> with <code>source_id</code> (optional), <code>disk</code>, and a disk-relative <code>path</code>. For uploads, send the <code>file</code> field as multipart form data.</p>
            </div>
        </section>
    </div>

    <section class="mt-5 rounded-2xl border border-[#e6ebe4] bg-white p-5 sm:p-6">
        <div><h2 class="font-semibold">Active tokens</h2><p class="mt-1 text-sm text-[#7e897e]">Revoke any token that is no longer in use.</p></div>
        <div class="mt-4 divide-y divide-[#edf0eb]">
            @forelse ($tokens as $token)
                <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div><p class="text-sm font-semibold">{{ $token->name }}</p><p class="mt-1 text-xs text-[#899389]">Created {{ $token->created_at?->format('M j, Y') }} · Last used {{ $token->last_used_at?->diffForHumans() ?? 'Never' }} · {{ implode(', ', $token->abilities ?? []) }}</p></div>
                    <form method="POST" action="{{ route('admin.integrations.tokens.destroy', $token->id) }}" onsubmit="return confirm('Revoke this API token?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Revoke</button></form>
                </div>
            @empty
                <p class="py-6 text-sm text-[#7b867b]">No API tokens have been created.</p>
            @endforelse
        </div>
    </section>
@endsection
