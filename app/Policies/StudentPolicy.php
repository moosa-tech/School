<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Support\SchoolAccess;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        $schoolId = (int) session('current_school_id');

        return $schoolId > 0
            && $user->isActiveInSchool($schoolId)
            && SchoolAccess::hasSchoolRole($user, ['school_admin', 'teacher', 'staff'], $schoolId);
    }

    public function view(User $user, Student $student): bool
    {
        if (! SchoolAccess::canPerformSchoolOperations($user)) {
            return false;
        }

        if ((int) session('current_school_id') !== $student->school_id
            || ! $user->isActiveInSchool($student->school_id)) {
            return false;
        }

        if ($user->hasSchoolRole('parent', $student->school_id)) {
            return $student->guardians()
                ->where('guardians.school_id', $student->school_id)
                ->wherePivot('school_id', $student->school_id)
                ->where('guardians.user_id', $user->id)
                ->exists();
        }

        if ($user->hasSchoolRole('student', $student->school_id)) {
            return $student->user_id === $user->id;
        }

        return $user->hasSchoolRole(['school_admin', 'teacher', 'staff'], $student->school_id);
    }

    public function create(User $user): bool
    {
        $schoolId = (int) session('current_school_id');

        return $schoolId > 0
            && $user->isActiveInSchool($schoolId)
            && SchoolAccess::hasSchoolRole($user, ['school_admin', 'staff'], $schoolId);
    }

    public function update(User $user, Student $student): bool
    {
        return (int) session('current_school_id') === $student->school_id
            && $user->isActiveInSchool($student->school_id)
            && SchoolAccess::hasSchoolRole($user, ['school_admin', 'staff'], $student->school_id);
    }

    public function delete(User $user, Student $student): bool
    {
        return (int) session('current_school_id') === $student->school_id
            && $user->isActiveInSchool($student->school_id)
            && SchoolAccess::hasSchoolRole($user, ['school_admin'], $student->school_id);
    }
}
