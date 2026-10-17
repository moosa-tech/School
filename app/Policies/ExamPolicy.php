<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;
use App\Support\SchoolAccess;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin', 'teacher']);
    }

    public function view(User $user, Exam $exam): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin', 'teacher'], $exam->school_id);
    }

    public function manage(User $user, ?Exam $exam = null): bool
    {
        return SchoolAccess::hasSchoolRole($user, ['school_admin'], $exam?->school_id);
    }
}
