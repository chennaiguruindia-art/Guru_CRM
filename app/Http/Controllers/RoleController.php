<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Abilities reserved for Super Admin. They are withheld from the matrix
     * so nobody ticks a box the server would refuse anyway.
     */
    private const SUPER_ADMIN_ONLY = ['leads.convert'];

    public function index(Request $request): View|JsonResponse
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        $permissions = Permission::all()->groupBy('module');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'roles' => $roles]);
        }

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);
        $allPermissions = $this->availableTo($role);

        return view('roles.show', compact('role', 'allPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $permissions = $request->input('permissions', []);

        if ($role->slug !== 'super-admin') {
            // The posted values are IDs; the reserved list is slugs.
            $reservedIds = Permission::whereIn('slug', self::SUPER_ADMIN_ONLY)->pluck('id')->all();
            $permissions = array_values(array_diff($permissions, $reservedIds));
        }

        $role->permissions()->sync($permissions);

        AuditLogger::log('update', 'roles', $role->id);

        return back()->with('success', 'Role permissions updated successfully!');
    }

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    private function availableTo(Role $role): Collection
    {
        $permissions = Permission::all();

        if ($role->slug !== 'super-admin') {
            $permissions = $permissions->reject(
                fn (Permission $permission) => in_array($permission->slug, self::SUPER_ADMIN_ONLY, true)
            );
        }

        return $permissions->groupBy('module');
    }
}
