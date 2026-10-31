<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles', 'employee.branch'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Users retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $normalizedPhone = Phone::normalize($validated['phone']);
        if ($normalizedPhone) {
            $validated['phone'] = $normalizedPhone;
        }

        $user = DB::transaction(function () use ($validated) {
            $u = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'username' => $validated['username'] ?? null,
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
            ]);

            $u->roles()->sync($validated['roles']);

            if (! empty($validated['branch_id'])) {
                Employee::create([
                    'employee_code' => Employee::generateCode(),
                    'user_id' => $u->id,
                    'branch_id' => $validated['branch_id'],
                    'name' => $u->name,
                    'phone' => $u->phone,
                    'email' => $u->email,
                    'status' => 'active',
                ]);
            }

            return $u;
        });

        AuditLogger::log('create', 'users', $user->id, null, ['name' => $user->name]);

        return $this->successResponse(
            $user->fresh(['roles', 'employee.branch']),
            'User created successfully',
            201
        );
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'employee.branch']);

        return $this->successResponse($user, 'User retrieved');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ]);

        if (! empty($validated['phone'])) {
            $normalizedPhone = Phone::normalize($validated['phone']);
            if ($normalizedPhone) {
                $validated['phone'] = $normalizedPhone;
            }
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $roles = $validated['roles'] ?? null;
        unset($validated['roles']);

        $branchId = $validated['branch_id'] ?? null;
        unset($validated['branch_id']);

        $user->update($validated);

        if ($roles !== null) {
            $user->roles()->sync($roles);
        }

        if ($branchId !== null) {
            $user->employee()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'branch_id' => $branchId,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'employee_code' => $user->employee?->employee_code ?? Employee::generateCode(),
                    'status' => 'active',
                ]
            );
        }

        AuditLogger::log('update', 'users', $user->id);

        return $this->successResponse($user->fresh(['roles', 'employee.branch']), 'User updated');
    }

    public function destroy(User $user): JsonResponse
    {
        $id = $user->id;
        $user->delete();

        AuditLogger::log('delete', 'users', $id);

        return $this->successResponse(null, 'User deleted');
    }
}
