<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.integrations.index', [
            'tokens' => $request->user()->tokens()->latest()->get(),
            'newToken' => $request->session()->pull('new_api_token'),
        ]);
    }

    public function storeToken(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $token = $request->user()->createToken(
            $data['name'],
            ['catalog:read', 'catalog:write'],
        );
        $request->session()->flash('new_api_token', $token->plainTextToken);

        return redirect()->route('admin.integrations.index')->with('status', 'API token created. Copy it now; it will only be shown once.');
    }

    public function destroyToken(Request $request, int $token): RedirectResponse
    {
        $personalAccessToken = $request->user()
            ->tokens()
            ->whereKey($token)
            ->firstOrFail();

        $personalAccessToken->delete();

        return redirect()->route('admin.integrations.index')->with('status', 'API token revoked.');
    }
}
