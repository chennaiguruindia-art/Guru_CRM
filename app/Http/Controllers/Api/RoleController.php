<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends BaseApiController
{
    private const SUPER_ADMIN_ONLY = ['leads.convert'];

    public function index(Request $request): JsonResponse
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        $permissions = Permission::all()->groupBy('module');

        return $this->successResponse([
            'roles' => $roles,
            'permissions' => $permissions,
        ], 'Roles retrieved');
    }

    public function show(Role $role): JsonResponse
    {
        $role->load(['permissions', 'users']);

        return $this->successResponse($role, 'Role retrieved');
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        if (isset($validated['name']) || isset($validated['description'])) {
            $role->update([
                'name' => $validated['name'] ?? $role->name,
                'description' => $validated['description'] ?? $role->description,
            ]);
        }

        if (isset($validated['permissions'])) {
            $perms = $validated['permissions'];
            if ($role->slug !== 'super-admin') {
                $reservedIds = Permission::whereIn('slug', self::SUPER_ADMIN_ONLY)->pluck('id')->all();
                $perms = array_values(array_diff($perms, $reservedIds));
            }
            $role->permissions()->sync($perms);
        }

        AuditLogger::log('update', 'roles', $role->id, null, ['role' => $role->slug]);

        return $this->successResponse($role->fresh(['permissions', 'users']), 'Role updated successfully');
    }
}
