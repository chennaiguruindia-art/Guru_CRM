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
        $throttleKey = 'employee-login|' . strtolower($rawCode) . '|' . $request->ip();

        // 1. Rate Limiting Protection (Max 15 attempts per minute)
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 15)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'employee_code' => "Too many login attempts. Please wait {$seconds} seconds before trying again."
            ])->withInput();
        }

        // 2. High-Speed Indexed Match (Uses B-Tree index on employee_code)
        $paddedCode = 'EMP-' . str_pad($rawCode, 4, '0', STR_PAD_LEFT);
        $employee = \App\Models\Employee::where('employee_code', $rawCode)
            ->orWhere('employee_code', $paddedCode)
            ->first();

        if (! $employee) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
            return back()->withErrors(['employee_code' => 'Invalid Employee ID: "' . $rawCode . '". Please check with your branch admin.'])->withInput();
        }

        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

        if ($employee->status !== 'active') {
            return back()->withErrors(['employee_code' => 'This employee account is currently inactive.'])->withInput();
        }

        // Provision User account if not already linked
        if (! $employee->user_id) {
            $sanitizedCode = strtolower(preg_replace('/[^a-zA-Z0-9_\.]/', '-', $employee->employee_code));
            $email = $employee->email ?: ($sanitizedCode . '@horti.local');
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

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $address = $request->input('address');

        if ($latitude && $longitude) {
            $today = now('Asia/Kolkata')->toDateString();
            $nowTime = now('Asia/Kolkata')->format('H:i:s');

            $attendance = \App\Models\Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'date' => $today,
            ]);

            if (! $attendance->check_in) {
                $attendance->status = 'Present';
                $attendance->check_in = $nowTime;
                $attendance->check_in_latitude = $latitude;
                $attendance->check_in_longitude = $longitude;
                if ($address) $attendance->check_in_address = $address;
                $attendance->save();
            }

            session([
                'login_latitude' => $latitude,
                'login_longitude' => $longitude,
                'login_address' => $address,
            ]);
        }

        AuditLogger::log('login', 'employee_portal', $user->id, null, [
            'ip' => $request->ip(),
            'employee_code' => $employee->employee_code,
            'name' => $employee->name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
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
            'reason' => $request->filled('timeout') ? 'idle_timeout_30m' : 'manual_logout',
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($request->filled('timeout')) {
            return redirect()->route('login', ['timeout' => 1]);
        }

        return redirect('/');
    }
}
