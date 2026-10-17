<?php

namespace App\Support;

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
