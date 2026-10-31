<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Mobile login: accepts phone, email, or username + password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['required_without:phone', 'string'],
            'phone' => ['required_without:login', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $rawIdentifier = $request->input('phone') ?? $request->input('login');
        $normalizedPhone = Phone::normalize($rawIdentifier);

        // Find user by normalized phone, or fallback to email / username
        $user = null;
        if ($normalizedPhone) {
            $user = User::where('phone', $normalizedPhone)->first();
        }

        if (! $user) {
            $user = User::where('email', $rawIdentifier)
                ->orWhere('username', $rawIdentifier)
                ->first();
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid login credentials provided.', 401, [
                'login' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('This account has been deactivated. Please contact your administrator.', 403);
        }

        $deviceName = $request->input('device_name', $request->header('User-Agent', 'MobileApp'));
        $token = $user->createToken($deviceName)->plainTextToken;

        // Log audit event
        AuditLogger::log('api_login', 'authentication', $user->id, null, [
            'ip' => $request->ip(),
            'user' => $user->name,
            'device' => $deviceName,
        ]);

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserData($user),
        ], 'Login successful');
    }

    /**
     * Return authenticated user profile and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse($this->formatUserData($user), 'User profile loaded');
    }

    /**
     * Revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Revoke the current access token
        $user->currentAccessToken()?->delete();

        AuditLogger::log('api_logout', 'authentication', $user->id, null, [
            'ip' => $request->ip(),
            'user' => $user->name,
        ]);

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Update current user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $normalizedPhone = Phone::normalize($validated['phone']);
        if ($normalizedPhone) {
            $validated['phone'] = $normalizedPhone;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return $this->successResponse($this->formatUserData($user->fresh()), 'Profile updated successfully');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password does not match.', 422, [
                'current_password' => ['The provided current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLogger::log('password_change', 'authentication', $user->id);

        return $this->successResponse(null, 'Password updated successfully');
    }

    /**
     * Build rich representation of user for mobile client.
     */
    private function formatUserData(User $user): array
    {
        $user->loadMissing(['roles.permissions', 'employee.branch']);

        $isSuperAdmin = $user->hasRole('super-admin');

        $permissions = [];
        if ($isSuperAdmin) {
            $modules = config('permissions.modules', []);
            foreach ($modules as $module => $actions) {
                foreach ($actions as $action) {
                    $permissions[] = "{$module}.{$action}";
                }
            }
        } else {
            $permissions = $user->roles
                ->flatMap(fn ($role) => $role->permissions)
                ->pluck('slug')
                ->unique()
                ->values()
                ->all();
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'status' => $user->status,
            'roles' => $user->roles->pluck('slug')->all(),
            'is_super_admin' => $isSuperAdmin,
            'branch' => [
                'id' => $user->branchId(),
                'label' => $user->branchLabel(),
                'name' => $user->employee?->branch?->name,
                'code' => $user->employee?->branch?->code,
            ],
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'employee_code' => $user->employee->employee_code,
                'designation' => $user->employee->designation,
                'department' => $user->employee->department,
            ] : null,
            'permissions' => $permissions,
        ];
    }
}
