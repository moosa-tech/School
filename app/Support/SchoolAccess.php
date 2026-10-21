<?php

namespace App\Support;

use App\Models\ClassSubject;
use App\Models\School;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class SchoolAccess
{
    /**
     * @return list<string>
     */
    public static function schoolRoles(): array
    {
        return config('saas.school_roles', [
            'school_admin',
            'teacher',
            'accountant',
            'staff',
            'parent',
            'student',
        ]);
    }

    public static function supportAccessEnabled(): bool
    {
        return (bool) config('saas.support_access_enabled', false);
    }

    public static function portalForRole(?string $role): ?string
    {
        return match ($role) {
            'school_admin', 'principal' => 'school_admin',
            'teacher' => 'teacher',
            'accountant' => 'accountant',
            'staff' => 'staff',
            'student' => 'student',
            default => null,
        };
    }

    public static function roleCanAccessSchool(School $school, ?string $role): bool
    {
        if ($school->status !== 'active') {
            return false;
        }

        $portal = self::portalForRole($role);

        return $portal === null || $school->isPortalEnabled($portal);
    }

    public static function teacherIsAssignedToClassSubject(User $user, ClassSubject $classSubject, int $schoolId): bool
    {
        if ($user->schoolRole($schoolId) !== 'teacher' || $classSubject->school_id !== $schoolId) {
            return false;
        }

        return $classSubject->teacherAssignments()
            ->where('school_id', $schoolId)
            ->whereHas('employee', fn ($employee) => $employee
                ->where('school_id', $schoolId)
                ->where('user_id', $user->id)
                ->where('role', 'teacher')
                ->where('status', 'active'))
            ->exists();
    }

    /**
     * Platform Super Admins must not perform school operations by default.
     */
    public static function canPerformSchoolOperations(User $user): bool
    {
        if ($user->isSuperAdmin() && ! self::supportAccessEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * @param  list<string>  $roles
     */
    public static function hasSchoolRole(User $user, array $roles, ?int $schoolId = null): bool
    {
        if (! self::canPerformSchoolOperations($user)) {
            return false;
        }

        return $user->hasSchoolRole($roles, $schoolId);
    }

    public static function existsInSchool(string $table, int $schoolId): Exists
    {
        return Rule::exists($table, 'id')->where(fn ($query) => $query->where('school_id', $schoolId));
    }
}
