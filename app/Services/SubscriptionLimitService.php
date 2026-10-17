<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\School;
use App\Models\Student;

class SubscriptionLimitService
{
    public function studentLimitReached(School $school): bool
    {
        $limit = $this->studentLimit($school);
        if ($limit === null) {
            return false;
        }

        $count = Student::withoutGlobalScope('school')
            ->where('school_id', $school->id)
            ->whereIn('status', ['active', 'pending'])
            ->count();

        return $count >= $limit;
    }

    public function staffLimitReached(School $school): bool
    {
        $limit = $this->staffLimit($school);
        if ($limit === null) {
            return false;
        }

        $count = Employee::withoutGlobalScope('school')
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->count();

        return $count >= $limit;
    }

    public function studentLimit(School $school): ?int
    {
        $plan = $school->currentSubscription?->plan;

        return $plan?->student_limit;
    }

    public function staffLimit(School $school): ?int
    {
        $plan = $school->currentSubscription?->plan;

        return $plan?->staff_limit;
    }
}
