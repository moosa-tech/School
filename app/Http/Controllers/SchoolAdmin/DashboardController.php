<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $school = School::with('currentSubscription.plan')->findOrFail($schoolId);

        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first()
            ?? AcademicYear::forSchool($schoolId)->latest()->first();

        // Key counts
        $studentCount = Student::forSchool($schoolId)->where('status', 'active')->count();
        $employeeCount = Employee::forSchool($schoolId)->where('status', 'active')->count();
        $teacherCount = Employee::forSchool($schoolId)->where('role', 'teacher')->where('status', 'active')->count();
        $classCount = SchoolClass::forSchool($schoolId)->count();
        $sectionCount = Section::forSchool($schoolId)->count();

        // Today's attendance
        $today = now()->toDateString();
        $todaySessions = AttendanceSession::forSchool($schoolId)->where('attendance_date', $today)->get();
        $todayAttendances = StudentAttendance::forSchool($schoolId)
            ->whereHas('session', fn ($q) => $q->where('attendance_date', $today))
            ->get();

        $attendanceStats = [
            'present' => $todayAttendances->where('status', 'present')->count(),
            'absent' => $todayAttendances->where('status', 'absent')->count(),
            'late' => $todayAttendances->where('status', 'late')->count(),
            'excused' => $todayAttendances->where('status', 'excused')->count(),
            'total_marked' => $todayAttendances->count(),
            'unfinalized_sessions' => $todaySessions->where('status', '!=', 'finalized')->count(),
        ];

        // Financial figures (calculated strictly server-side)
        $todayCollection = FeePayment::forSchool($schoolId)
            ->where('payment_date', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $monthCollection = FeePayment::forSchool($schoolId)
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->where('status', 'completed')
            ->sum('amount');

        $totalInvoiced = FeeInvoice::forSchool($schoolId)->sum('total_amount');
        $totalPaid = FeeInvoice::forSchool($schoolId)->sum('paid_amount');
        $totalDues = max(0, (float) $totalInvoiced - (float) $totalPaid);
        $overdueCount = FeeInvoice::forSchool($schoolId)->where('status', 'overdue')->count();

        // Upcoming events & notices
        $upcomingExams = Exam::forSchool($schoolId)->where('starts_on', '>=', $today)->orderBy('starts_on')->take(3)->get();
        $recentAnnouncements = Announcement::forSchool($schoolId)->where('status', 'published')->latest()->take(4)->get();
        $recentInvoices = FeeInvoice::forSchool($schoolId)->with('student')->latest()->take(5)->get();

        return view('school-admin.dashboard', compact(
            'school',
            'currentYear',
            'studentCount',
            'employeeCount',
            'teacherCount',
            'classCount',
            'sectionCount',
            'attendanceStats',
            'todayCollection',
            'monthCollection',
            'totalDues',
            'overdueCount',
            'upcomingExams',
            'recentAnnouncements',
            'recentInvoices'
        ));
    }
}
