<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Homework;
use App\Models\Mark;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use App\Models\TimetableEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $student = $this->studentForCurrentSchool();

        $timetable = collect();
        $homework = collect();
        $attendanceStats = ['total' => 0, 'present' => 0, 'absent' => 0, 'rate' => 0];
        $recentAttendance = collect();
        $marks = collect();
        $feeInvoices = collect();
        $feeStats = ['total' => 0, 'paid' => 0, 'pending' => 0];

        if ($student) {
            $student->setRelation('currentEnrollment', $this->currentEnrollment($student, $schoolId));

            // Student attendance history & stats
            $allAtt = StudentAttendance::forSchool($schoolId)
                ->where('student_id', $student->id)
                ->whereHas('session', fn ($query) => $query->forSchool($schoolId))
                ->with([
                    'session' => fn ($query) => $query->forSchool($schoolId)->with([
                        'class' => fn ($class) => $class->forSchool($schoolId),
                        'section' => fn ($section) => $section->forSchool($schoolId),
                    ]),
                ])
                ->latest('id')
                ->get();
            $recentAttendance = $allAtt->take(15);
            $totalDays = $allAtt->count();
            $presentDays = $allAtt->whereIn('status', ['present', 'late'])->count();
            $attendanceStats = [
                'total' => $totalDays,
                'present' => $allAtt->where('status', 'present')->count(),
                'absent' => $allAtt->where('status', 'absent')->count(),
                'late' => $allAtt->where('status', 'late')->count(),
                'rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
            ];

            // Student exam marks
            $marks = Mark::forSchool($schoolId)
                ->where('student_id', $student->id)
                ->whereHas('examSubject.exam', fn ($exam) => $exam
                    ->forSchool($schoolId)
                    ->whereIn('status', ['published', 'locked']))
                ->with([
                    'examSubject' => fn ($examSubject) => $examSubject->forSchool($schoolId)->with([
                        'exam' => fn ($exam) => $exam->forSchool($schoolId),
                        'classSubject' => fn ($classSubject) => $classSubject->forSchool($schoolId)->with([
                            'subject' => fn ($subject) => $subject->forSchool($schoolId),
                        ]),
                    ]),
                ])
                ->latest('id')
                ->take(15)
                ->get();

            // Student fee invoices
            $feeInvoices = FeeInvoice::forSchool($schoolId)
                ->where('student_id', $student->id)
                ->latest('issue_date')
                ->get();
            $totalFee = $feeInvoices->sum('total_amount');
            $paidFee = $feeInvoices->sum('paid_amount');
            $feeStats = [
                'total' => $totalFee,
                'paid' => $paidFee,
                'pending' => max(0, $totalFee - $paidFee),
            ];

            if ($student->currentEnrollment) {
                $timetable = TimetableEntry::forSchool($schoolId)
                    ->where('section_id', $student->currentEnrollment->section_id)
                    ->with([
                        'classSubject' => fn ($classSubject) => $classSubject->forSchool($schoolId)->with([
                            'subject' => fn ($subject) => $subject->forSchool($schoolId),
                            'teacher' => fn ($teacher) => $teacher->whereHas('schools', fn ($school) => $school
                                ->where('schools.id', $schoolId)
                                ->where('school_user.status', 'active')),
                        ]),
                    ])
                    ->orderBy('starts_at')
                    ->get()
                    ->groupBy('weekday');

                $homework = Homework::forSchool($schoolId)
                    ->with([
                        'classSubject' => fn ($classSubject) => $classSubject->forSchool($schoolId)->with([
                            'subject' => fn ($subject) => $subject->forSchool($schoolId),
                            'class' => fn ($class) => $class->forSchool($schoolId),
                        ]),
                    ])
                    ->whereHas('classSubject', fn ($classSubject) => $classSubject
                        ->forSchool($schoolId)
                        ->where('class_id', $student->currentEnrollment->class_id)
                        ->where('academic_year_id', $student->currentEnrollment->academic_year_id))
                    ->latest('due_on')
                    ->take(6)
                    ->get();
            }
        }

        $announcements = Announcement::forSchool($schoolId)
            ->whereIn('audience', ['all', 'students'])
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        return view('student.dashboard', compact(
            'student',
            'timetable',
            'homework',
            'announcements',
            'attendanceStats',
            'recentAttendance',
            'marks',
            'feeInvoices',
            'feeStats'
        ));
    }

    public function profile(): View
    {
        $schoolId = (int) session('current_school_id');
        $student = $this->requireStudent();
        $student->setRelation('currentEnrollment', $this->currentEnrollment($student, $schoolId));
        $guardians = $student->guardians()
            ->where('guardians.school_id', $schoolId)
            ->wherePivot('school_id', $schoolId)
            ->get();

        return view('student.profile', compact('student', 'guardians'));
    }

    public function attendance(Request $request): View
    {
        $schoolId = (int) session('current_school_id');
        $student = $this->requireStudent();
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = $validated['month'] ?? now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $attendanceQuery = StudentAttendance::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->whereHas('session', fn ($query) => $query
                ->forSchool($schoolId)
                ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()]));

        $monthlyStats = (clone $attendanceQuery)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
            ")
            ->first();

        $attendance = $attendanceQuery
            ->with([
                'session' => fn ($query) => $query->forSchool($schoolId)->with([
                    'class' => fn ($class) => $class->forSchool($schoolId),
                    'section' => fn ($section) => $section->forSchool($schoolId),
                ]),
            ])
            ->get()
            ->sortByDesc(fn (StudentAttendance $record) => $record->session?->attendance_date)
            ->values();

        return view('student.attendance', compact('student', 'attendance', 'month', 'monthlyStats'));
    }

    public function fees(): View
    {
        $schoolId = (int) session('current_school_id');
        $student = $this->requireStudent();
        $invoiceScope = FeeInvoice::forSchool($schoolId)->where('student_id', $student->id);
        $totals = (clone $invoiceScope)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(paid_amount), 0) as paid')
            ->first();

        $invoices = (clone $invoiceScope)
            ->with([
                'academicYear' => fn ($query) => $query->forSchool($schoolId),
                'items' => fn ($query) => $query->forSchool($schoolId),
                'allocations' => fn ($query) => $query->forSchool($schoolId)
                    ->whereHas('payment', fn ($payment) => $payment
                        ->forSchool($schoolId)
                        ->where('student_id', $student->id))
                    ->with(['payment' => fn ($payment) => $payment
                        ->forSchool($schoolId)
                        ->where('student_id', $student->id)]),
            ])
            ->latest('issue_date')
            ->paginate(20, ['*'], 'invoice_page')
            ->withQueryString();

        $payments = FeePayment::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->with([
                'allocations' => fn ($query) => $query->forSchool($schoolId)
                    ->whereHas('invoice', fn ($invoice) => $invoice
                        ->forSchool($schoolId)
                        ->where('student_id', $student->id))
                    ->with(['invoice' => fn ($invoice) => $invoice
                        ->forSchool($schoolId)
                        ->where('student_id', $student->id)]),
            ])
            ->latest('payment_date')
            ->paginate(20, ['*'], 'payment_page')
            ->withQueryString();

        return view('student.fees', compact('student', 'invoices', 'payments', 'totals'));
    }

    public function receipt(int $payment): View
    {
        $schoolId = (int) session('current_school_id');
        $student = $this->requireStudent();
        $payment = FeePayment::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->with([
                'allocations' => fn ($query) => $query->forSchool($schoolId)
                    ->whereHas('invoice', fn ($invoice) => $invoice
                        ->forSchool($schoolId)
                        ->where('student_id', $student->id))
                    ->with(['invoice' => fn ($invoice) => $invoice
                        ->forSchool($schoolId)
                        ->where('student_id', $student->id)]),
            ])
            ->findOrFail($payment);

        return view('student.receipt', compact('student', 'payment'));
    }

    public function results(): View
    {
        $schoolId = (int) session('current_school_id');
        $student = $this->requireStudent();
        $marks = Mark::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->whereHas('examSubject.exam', fn ($exam) => $exam
                ->forSchool($schoolId)
                ->whereIn('status', ['published', 'locked']))
            ->with([
                'examSubject' => fn ($examSubject) => $examSubject->forSchool($schoolId)->with([
                    'exam' => fn ($exam) => $exam->forSchool($schoolId),
                    'classSubject' => fn ($classSubject) => $classSubject->forSchool($schoolId)->with([
                        'subject' => fn ($subject) => $subject->forSchool($schoolId),
                    ]),
                ]),
            ])
            ->latest('id')
            ->paginate(25);

        return view('student.results', compact('student', 'marks'));
    }

    private function studentForCurrentSchool(): ?Student
    {
        return Student::forSchool((int) session('current_school_id'))
            ->where('user_id', Auth::id())
            ->first();
    }

    private function requireStudent(): Student
    {
        return $this->studentForCurrentSchool() ?? abort(404);
    }

    private function currentEnrollment(Student $student, int $schoolId): ?StudentEnrollment
    {
        return StudentEnrollment::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->whereHas('academicYear', fn ($query) => $query
                ->forSchool($schoolId)
                ->where('is_current', true))
            ->with([
                'class' => fn ($query) => $query->forSchool($schoolId),
                'section' => fn ($query) => $query->forSchool($schoolId),
                'academicYear' => fn ($query) => $query->forSchool($schoolId),
            ])
            ->latest('id')
            ->first();
    }
}
