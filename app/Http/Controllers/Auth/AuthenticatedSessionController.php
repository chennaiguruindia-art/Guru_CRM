<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * $needsBootstrap tells the view to offer signup — which only exists to
     * create the very first account, and disappears as soon as one does.
     */
    public function create(): View
    {
        $needsBootstrap = false;
        try {
            $needsBootstrap = ! User::query()->exists();
        } catch (\Throwable) {
            try {
                $needsBootstrap = ! User::withoutGlobalScopes()->exists();
            } catch (\Throwable) {
                $needsBootstrap = false;
            }
        }

        return view('auth.login', [
            'needsBootstrap' => $needsBootstrap,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Audit Trail
        AuditLogger::log('login', 'authentication', Auth::id(), null, [
            'ip' => $request->ip(),
            'user' => Auth::user()->name,
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $userName = Auth::user()?->name;

        // Audit Trail
        AuditLogger::log('logout', 'authentication', $userId, null, [
            'ip' => $request->ip(),
            'user' => $userName,
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
