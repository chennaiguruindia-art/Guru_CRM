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
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Employee::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        $employees = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $employees]);
        }

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        $users = User::where('status', 'active')->get();
        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('employees.create', compact('users', 'roles', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'designation' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:100'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'joining_date' => ['nullable', 'date'],
            'employee_type' => ['required', 'string'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'user_id' => ['nullable', 'exists:users,id'],
            'address' => ['nullable', 'string'],

            // Login provisioning — see buildLogin() below.
            'create_login' => ['sometimes', 'boolean'],
            'role' => ['required_if:create_login,1', 'nullable', 'exists:roles,slug'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $validated['employee_code'] = Employee::generateCode();
        $validated['status'] = 'active';

        $emp = Employee::create($validated);
        AuditLogger::log('create', 'employees', $emp->id);

        // Assigning a person provisions their login: the username is always the
        // Employee ID (EMP-0001), so they sign in with that instead of an email.
        $credentials = $this->buildLogin($emp, $request);

        return redirect()->route('employees.index')
            ->with('success', 'Employee registered successfully!')
            ->with('login_credentials', $credentials);
    }

    /**
     * Create the system account for a newly assigned person.
     *
     * Returns the one-time credentials so the Super Admin can pass them on, or
     * null when login creation was skipped (an existing user was linked, or the
     * box was unticked).
     */
    private function buildLogin(Employee $emp, Request $request): ?array
    {
        if (! $request->boolean('create_login') || $emp->user_id) {
            return null;
        }

        // employees.email is optional, but users.email is required and unique —
        // fall back to a non-routable placeholder keyed on the Employee ID.
        $email = $emp->email ?: ($emp->employee_code . '@horti.local');

        if (User::where('email', $email)->exists()) {
            return null;
        }

        $password = $request->input('password') ?: Str::random(10);

        $user = User::create([
            'name' => $emp->name,
            'email' => $email,
            'username' => $emp->employee_code,
            'password' => $password,
            'status' => 'active',
        ]);

        if ($role = $request->input('role')) {
            $user->assignRole($role);
        }

        $emp->update(['user_id' => $user->id]);

        return ['username' => $emp->employee_code, 'password' => $password];
    }

    public function show(Employee $employee): View
    {
        $employee->load('user', 'attendances');
        return view('employees.show', compact('employee'));
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        AuditLogger::log('delete', 'employees', $employee->id);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted!');
    }
}
