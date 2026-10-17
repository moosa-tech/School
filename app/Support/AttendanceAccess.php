<?php

namespace App\Support;

use App\Models\ClassSubject;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AttendanceAccess
{
    /**
     * @return list<int>
     */
    public static function assignedClassIds(User $user, int $schoolId, int $academicYearId): array
    {
        $employee = Employee::forSchool($schoolId)
            ->where('user_id', $user->id)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->first();

        if (! $employee) {
            return [];
        }

        return ClassSubject::forSchool($schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where(function (Builder $query) use ($employee, $user, $schoolId, $academicYearId): void {
                $query->where('teacher_id', $user->id)
                    ->orWhereHas('teacherAssignments', fn (Builder $assignments) => $assignments
                        ->where('school_id', $schoolId)
                        ->where('employee_id', $employee->id)
                        ->where('academic_year_id', $academicYearId));
            })
            ->distinct()
            ->pluck('class_id')
            ->map(fn ($classId): int => (int) $classId)
            ->all();
    }

    public static function canManageClass(User $user, int $schoolId, int $academicYearId, int $classId): bool
    {
        return ! $user->hasSchoolRole('teacher', $schoolId)
            || in_array($classId, self::assignedClassIds($user, $schoolId, $academicYearId), true);
    }
}
