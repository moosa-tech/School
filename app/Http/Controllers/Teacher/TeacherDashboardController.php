<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Homework;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        /** @var User $user */
        $user = Auth::user();
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();

        $employee = Employee::forSchool($schoolId)
            ->where('user_id', $user->id)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->first();
        $assignments = collect();

        if ($employee && $currentYear) {
            $assignments = TeacherAssignment::forSchool($schoolId)
                ->where('employee_id', $employee->id)
                ->where('academic_year_id', $currentYear->id)
                ->with(['classSubject.class', 'classSubject.subject', 'classSubject.class.sections'])
                ->get();
        }

        // Assigned class subjects
        $myClassSubjectIds = $assignments->pluck('class_subject_id');
        $recentHomework = Homework::forSchool($schoolId)
            ->whereIn('class_subject_id', $myClassSubjectIds)
            ->with(['classSubject.class', 'classSubject.subject'])
            ->latest('due_on')
            ->take(5)
            ->get();

        return view('teacher.dashboard', compact('assignments', 'recentHomework', 'currentYear', 'employee'));
    }
}
