<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Support\SchoolAccess;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin']);
    }

    public function create(User $user): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin']);
    }

    public function update(User $user, Employee $employee): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin'], $employee->school_id);
    }
}
