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

        $user = Auth::user();
        if ($user && $user->hasRole('employee') && ! $user->hasAnyRole(['super-admin', 'admin', 'fieldmarketer'])) {
            return redirect()->intended(route('portal.punch'));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Handle fast employee-only login using Employee ID
     */
    public function employeeLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_code' => ['required', 'string', 'max:50'],
        ]);

        $rawCode = trim($request->input('employee_code'));

        // Match direct code (e.g. EMP-0001) or short digits (e.g. 1 -> EMP-0001)
        $employee = \App\Models\Employee::where('employee_code', $rawCode)
            ->orWhere('employee_code', 'EMP-' . str_pad($rawCode, 4, '0', STR_PAD_LEFT))
            ->first();

        if (! $employee) {
            return back()->withErrors(['employee_code' => 'Invalid Employee ID: "' . $rawCode . '". Please check with your branch admin.'])->withInput();
        }

        if ($employee->status !== 'active') {
            return back()->withErrors(['employee_code' => 'This employee account is currently inactive.'])->withInput();
        }

        // Provision User account if not already linked
        if (! $employee->user_id) {
            $email = $employee->email ?: ($employee->employee_code . '@horti.local');
            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $employee->name,
                    'username' => $employee->employee_code,
                    'phone' => \App\Support\Phone::normalize($employee->phone) ?: '0000000000',
                    'password' => \Illuminate\Support\Str::random(12),
                    'status' => 'active',
                ]
            );
            $user->assignRole('employee');
            $employee->update(['user_id' => $user->id]);
        } else {
            $user = \App\Models\User::find($employee->user_id);
            if (! $user) {
                return back()->withErrors(['employee_code' => 'Linked user profile not found.'])->withInput();
            }
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        AuditLogger::log('login', 'employee_portal', $user->id, null, [
            'ip' => $request->ip(),
            'employee_code' => $employee->employee_code,
            'name' => $employee->name,
        ]);

        return redirect()->route('portal.punch');
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
