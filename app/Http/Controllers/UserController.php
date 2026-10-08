<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = User::with('roles')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $users]);
        }

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Roles an Admin may hand out when creating a sub-user.
     *
     * Super Admin is deliberately absent: it belongs to whoever signed up
     * first and can never be assigned to anyone else.
     *
     * @var list<string>
     */
    private const ASSIGNABLE_ROLES = ['admin', 'fieldmarketer'];

    public function create(): View
    {
        $roles = Role::whereIn('slug', self::ASSIGNABLE_ROLES)->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            // Not merely exists:roles,id — a hand-crafted request must not be
            // able to attach the Super Admin role by guessing its id.
            'role_id' => [
                'required',
                'exists:roles,id',
                Rule::in(Role::whereIn('slug', self::ASSIGNABLE_ROLES)->pluck('id')->all()),
            ],
            // Optional: the branch the new employee reports to.
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $user->roles()->attach($validated['role_id']);

        // A login is also a person on the roster: provision the Employee row
        // so the branch chosen above has somewhere to live. Without this the
        // branch_id dropdown would have no column to write to on `users`.
        $employee = Employee::create([
            'employee_code' => Employee::generateCode(),
            'user_id' => $user->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $user->name,
            // employees.phone is NOT NULL while the phone above is optional.
            'phone' => $validated['phone'] ?? '',
            'email' => $user->email,
            'designation' => $role->name,
            'department' => $role->slug === 'admin' ? 'Administration' : 'Sales',
            'status' => 'active',
        ]);

        AuditLogger::log('create', 'users', $user->id);
        AuditLogger::log('create', 'employees', $employee->id);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user): View
    {
        abort_unless($this->mayEdit($user), 403);

        $roles = Role::whereIn('slug', self::ASSIGNABLE_ROLES)->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('users.edit', [
            'user' => $user->load('roles', 'employee'),
            'roles' => $roles,
            'branches' => $branches,
            'currentRole' => $user->roles()->first(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->mayEdit($user), 403);

        // Super Admin is not assignable, but a user who already holds it must
        // still be savable — so the allowed set is "assignable" plus whatever
        // this user has today. Nothing else can slip in.
        $allowedRoleIds = Role::whereIn('slug', self::ASSIGNABLE_ROLES)->pluck('id')
            ->merge($user->roles()->pluck('roles.id')->all())
            ->unique();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
            // Optional: blank means "keep the existing password".
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id', Rule::in($allowedRoleIds->all())],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ], [], ['role_id' => 'role']);

        $currentRole = $user->roles()->first();

        if ($user->id === auth()->id() && (int) $validated['role_id'] !== (int) $currentRole?->id) {
            return back()->withInput()->with('error', 'You cannot change your own role.');
        }

        if ($user->id === auth()->id() && $validated['status'] === 'inactive') {
            return back()->withInput()->with('error', 'You cannot deactivate your own account.');
        }

        $role = Role::findOrFail($validated['role_id']);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'password' => $request->filled('password') ? Hash::make($validated['password']) : $user->password,
        ]);

        $user->roles()->sync([$role->id]);

        // Keep the roster row in step with the login it backs.
        $employee = Employee::withTrashed()->firstWhere('user_id', $user->id);
        if ($employee && $employee->trashed()) {
            $employee->restore();
        }

        $roster = [
            'name' => $user->name,
            // employees.phone is NOT NULL while the phone above is optional.
            'phone' => $user->phone ?? '',
            'email' => $user->email,
            'branch_id' => $validated['branch_id'] ?? null,
            'designation' => $role->name,
            'department' => $role->slug === 'admin' ? 'Administration' : 'Sales',
            'status' => $validated['status'],
        ];

        if ($employee) {
            $employee->update($roster);
        } else {
            // Legacy logins have no roster row yet.
            $employee = Employee::create($roster + [
                'employee_code' => Employee::generateCode(),
                'user_id' => $user->id,
            ]);
            AuditLogger::log('create', 'employees', $employee->id);
        }

        AuditLogger::log('update', 'users', $user->id, null, [
            'role_id' => $role->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    /**
     * The `permission:users.edit` route middleware already checks the actor's
     * ability; this guards the one case middleware cannot express — an Admin
     * demoting the Super Admin account out from under everyone.
     */
    private function mayEdit(User $user): bool
    {
        $actor = auth()->user();

        if ($user->hasRole('super-admin') && ! $actor->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself!');
        }

        if (! $this->mayEdit($user)) {
            return back()->with('error', 'Only a Super Admin can delete the Super Admin account.');
        }

        AuditLogger::log('delete', 'users', $user->id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User removed!');
    }
}
