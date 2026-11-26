<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $query = Employee::with(['user', 'branch'])->latest();

        // Branch Admin scoping: only sees employees of their own branch
        if ($user && ! $user->canSeeAllBranches()) {
            $branchId = $user->branchId();
            if ($branchId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('branch_id', $branchId);
            }
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

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

        $branches = $user && $user->canSeeAllBranches() ? Branch::where('status', 'active')->orderBy('name')->get() : collect();

        return view('employees.index', compact('employees', 'branches'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $users = User::where('status', 'active')->get();
        $roles = Role::orderBy('name')->get();

        // Branch admin can only select their own branch
        if ($user && ! $user->canSeeAllBranches()) {
            $branches = Branch::where('id', $user->branchId())->get();
        } else {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        }

        return view('employees.create', compact('users', 'roles', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // The roster phone is also the sign-in identifier, so it is settled
        // into the form the login will store it in before anything is saved.
        $request->merge([
            'phone' => Phone::normalize($request->input('phone')),
            'salary' => $request->input('monthly_salary', $request->input('salary', 0)),
            'joining_date' => $request->input('date_of_joining', $request->input('joining_date')),
        ]);

        $validated = $request->validate([
            'employee_code' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('employees', 'employee_code')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\d{10,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'designation' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'joining_date' => ['nullable', 'date'],
            'employee_type' => ['required', 'string'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'dob' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:100'],
            'uan_number' => ['nullable', 'string', 'max:50'],
            'pf_number' => ['nullable', 'string', 'max:50'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'da' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'leave_encashment' => ['nullable', 'numeric', 'min:0'],
            'hra' => ['nullable', 'numeric', 'min:0'],
            'conveyance_allowance' => ['nullable', 'numeric', 'min:0'],
            'special_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowance' => ['nullable', 'numeric', 'min:0'],
            'custom_pf_deduction' => ['nullable', 'numeric', 'min:0'],
            'custom_esi_deduction' => ['nullable', 'numeric', 'min:0'],
            'custom_pt_deduction' => ['nullable', 'numeric', 'min:0'],

            // Login provisioning
            'create_login' => ['sometimes', 'boolean'],
            'role' => ['sometimes', 'nullable', 'exists:roles,slug'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (empty($validated['department'])) {
            $validated['department'] = 'General';
        }

        if (empty($validated['basic_salary']) && ! empty($validated['salary'])) {
            $validated['basic_salary'] = $validated['salary'];
        }

        // Enforce branch assignment for branch admins: cannot create employees for other branches
        if ($user && ! $user->canSeeAllBranches()) {
            $branchId = $user->branchId();
            if (! $branchId) {
                return back()->with('error', 'Your admin account has no assigned branch. Contact Super Admin.')->withInput();
            }
            $validated['branch_id'] = $branchId;
        }

        if (! empty($validated['employee_code'])) {
            $validated['employee_code'] = trim($validated['employee_code']);
            Employee::onlyTrashed()->where('employee_code', $validated['employee_code'])->forceDelete();
        } else {
            $validated['employee_code'] = Employee::generateCode();
        }

        if (! empty($validated['email'])) {
            Employee::onlyTrashed()->where('email', $validated['email'])->forceDelete();
        }

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
        $createLogin = $request->has('create_login') ? $request->boolean('create_login') : true;
        if (! $createLogin || $emp->user_id) {
            return null;
        }

        // employees.email is optional, but users.email is required and unique —
        // fall back to a non-routable placeholder keyed on the Employee ID.
        // Replace special characters like '/' with '-' for a valid email format.
        $sanitizedCode = strtolower(preg_replace('/[^a-zA-Z0-9_\.]/', '-', $emp->employee_code));
        $email = $emp->email ?: ($sanitizedCode . '@horti.local');

        $password = $request->input('password') ?: Str::random(10);
        $phone = Phone::normalize($emp->phone);

        // Check for existing user by email (including any soft-deleted from previous delete)
        $user = User::withTrashed()->where('email', $email)->first();

        // Clean up any soft-deleted user with the same username if it's a different row
        User::onlyTrashed()
            ->where('username', $emp->employee_code)
            ->when($user, fn ($q) => $q->where('id', '!=', $user->id))
            ->forceDelete();

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
            $user->update([
                'name' => $emp->name,
                'email' => $email,
                'username' => $emp->employee_code,
                'phone' => $phone,
                'password' => $password,
                'status' => 'active',
                'branch_id' => $emp->branch_id,
            ]);
        } else {
            $user = User::create([
                'name' => $emp->name,
                'email' => $email,
                'username' => $emp->employee_code,
                'phone' => $phone,
                'password' => $password,
                'status' => 'active',
                'branch_id' => $emp->branch_id,
            ]);
        }

        $role = $request->input('role');
        if (empty($role)) {
            $role = 'employee';
        }

        $user->assignRole($role);

        $emp->update(['user_id' => $user->id]);

        return [
            'username' => $emp->employee_code,
            'phone' => $phone,
            'password' => $password,
        ];
    }

    public function show(Request $request, Employee $employee): View
    {
        $user = $request->user();
        if ($user && ! $user->canSeeAllBranches() && $employee->branch_id !== $user->branchId()) {
            abort(403, 'Unauthorized access to employee in another branch.');
        }

        $employee->load('user', 'branch', 'attendances');
        return view('employees.show', compact('employee'));
    }

    public function edit(Request $request, Employee $employee): View
    {
        $user = $request->user();
        if ($user && ! $user->canSeeAllBranches() && $employee->branch_id !== $user->branchId()) {
            abort(403, 'Unauthorized access to employee in another branch.');
        }

        $branches = $user && ! $user->canSeeAllBranches()
            ? Branch::where('id', $user->branchId())->get()
            : Branch::where('status', 'active')->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'branches'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();
        if ($user && ! $user->canSeeAllBranches() && $employee->branch_id !== $user->branchId()) {
            abort(403, 'Unauthorized action.');
        }

        $request->merge([
            'phone' => Phone::normalize($request->input('phone')),
            'salary' => $request->input('monthly_salary', $request->input('salary', 0)),
            'joining_date' => $request->input('date_of_joining', $request->input('joining_date')),
        ]);

        $validated = $request->validate([
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:employees,employee_code,' . $employee->id],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\d{10,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'designation' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'joining_date' => ['nullable', 'date'],
            'employee_type' => ['required', 'string'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'dob' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:100'],
            'uan_number' => ['nullable', 'string', 'max:50'],
            'pf_number' => ['nullable', 'string', 'max:50'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'da' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'leave_encashment' => ['nullable', 'numeric', 'min:0'],
            'hra' => ['nullable', 'numeric', 'min:0'],
            'conveyance_allowance' => ['nullable', 'numeric', 'min:0'],
            'special_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowance' => ['nullable', 'numeric', 'min:0'],
            'custom_pf_deduction' => ['nullable', 'numeric', 'min:0'],
            'custom_esi_deduction' => ['nullable', 'numeric', 'min:0'],
            'custom_pt_deduction' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if (! empty($validated['employee_code'])) {
            $validated['employee_code'] = trim($validated['employee_code']);
            if ($employee->user && $employee->user->username === $employee->employee_code) {
                $employee->user->update(['username' => $validated['employee_code']]);
            }
        } else {
            unset($validated['employee_code']);
        }

        if (empty($validated['department'])) {
            $validated['department'] = $employee->department ?? 'General';
        }

        if (empty($validated['basic_salary']) && ! empty($validated['salary'])) {
            $validated['basic_salary'] = $validated['salary'];
        }

        if ($user && ! $user->canSeeAllBranches()) {
            $validated['branch_id'] = $user->branchId();
        }

        $employee->update($validated);
        AuditLogger::log('update', 'employees', $employee->id);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully!');
    }

    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();
        if ($user && ! $user->canSeeAllBranches() && $employee->branch_id !== $user->branchId()) {
            abort(403, 'Unauthorized action.');
        }

        AuditLogger::log('delete', 'employees', $employee->id);

        // Permanently remove linked user account so email and username are freed immediately
        if ($employee->user) {
            $employee->user->forceDelete();
        }

        // Also clean up user by email/username if it was unlinked
        if ($employee->email) {
            User::withTrashed()->where('email', $employee->email)->forceDelete();
        }
        User::withTrashed()->where('username', $employee->employee_code)->forceDelete();

        // Permanently remove employee record
        $employee->forceDelete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
    }
}
