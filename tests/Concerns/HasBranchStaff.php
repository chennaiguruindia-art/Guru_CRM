<?php

namespace Tests\Concerns;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;

/**
 * Branches only decide what somebody may see once there is an employee record
 * tying their login to one — that link is what RecordVisibilityScope reads.
 */
trait HasBranchStaff
{
    private function branch(string $name = 'Test Branch'): Branch
    {
        static $n = 0;
        $n++;

        return Branch::create([
            'code' => 'BR-TEST-' . $n,
            'name' => $name . ' ' . $n,
        ]);
    }

    /**
     * Put a login on a branch's roster. Without this the login has no branch,
     * and RecordVisibilityScope fails closed for it: it sees nothing.
     */
    private function putInBranch(User $user, int $branchId, string $designation = 'Field Marketer'): Employee
    {
        return Employee::create([
            'employee_code' => Employee::generateCode(),
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'name' => $user->name,
            // employees.phone is NOT NULL while a login's phone is optional.
            'phone' => '9000000000',
            'email' => $user->email,
            'designation' => $designation,
            'status' => 'active',
        ]);
    }
}
