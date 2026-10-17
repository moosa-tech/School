<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;
use App\Support\SchoolAccess;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin', 'teacher', 'staff']);
    }

    public function mark(User $user, ?AttendanceSession $session = null): bool
    {
        $schoolId = $session?->school_id;

        return SchoolAccess::hasSchoolRole($user, ['school_admin', 'teacher', 'staff'], $schoolId);
    }

    public function correct(User $user, AttendanceSession $session): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin'], $session->school_id);
    }
}
