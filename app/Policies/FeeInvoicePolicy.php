<?php

namespace App\Policies;

use App\Models\FeeInvoice;
use App\Models\Student;
use App\Models\User;
use App\Support\SchoolAccess;

class FeeInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        $schoolId = (int) session('current_school_id');

        return $schoolId > 0
            && $user->isActiveInSchool($schoolId)
            && SchoolAccess::hasSchoolRole($user, ['school_admin', 'accountant'], $schoolId);
    }

    public function view(User $user, FeeInvoice $invoice): bool
    {
        if (! SchoolAccess::canPerformSchoolOperations($user)) {
            return false;
        }

        if ((int) session('current_school_id') !== $invoice->school_id
            || ! $user->isActiveInSchool($invoice->school_id)) {
            return false;
        }

        $student = Student::query()
            ->withoutGlobalScope('school')
            ->whereKey($invoice->student_id)
            ->where('school_id', $invoice->school_id)
            ->first();

        if (! $student) {
            return false;
        }

        if ($user->hasSchoolRole('parent', $invoice->school_id)) {
            return $student->guardians()
                ->where('guardians.school_id', $invoice->school_id)
                ->wherePivot('school_id', $invoice->school_id)
                ->where('guardians.user_id', $user->id)
                ->exists();
        }

        if ($user->hasSchoolRole('student', $invoice->school_id)) {
            return $student->user_id === $user->id;
        }

        return $user->hasSchoolRole(['school_admin', 'accountant'], $invoice->school_id);
    }

    public function manage(User $user, ?FeeInvoice $invoice = null): bool
    {
        $schoolId = $invoice?->school_id ?? (int) session('current_school_id');

        return $schoolId > 0
            && (int) session('current_school_id') === $schoolId
            && $user->isActiveInSchool($schoolId)
            && SchoolAccess::hasSchoolRole($user, ['school_admin', 'accountant'], $schoolId);
    }
}
