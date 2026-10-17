<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceChangeLog;
use App\Models\AttendanceSession;
use App\Models\ClassSubject;
use App\Models\Employee;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\SchoolAccess;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * Show attendance session selector and roll call sheet.
     */
    public function index(Request $request): View
    {
        $schoolId = session('current_school_id');
        $filters = $request->validate([
            'academic_year_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'class_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('classes', $schoolId)],
            'section_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('sections', $schoolId)],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $academicYears = AcademicYear::forSchool($schoolId)->get();
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();
        $selectedYearId = (int) ($filters['academic_year_id'] ?? $currentYear?->id ?? 0);
        $isTeacher = $request->user()->hasSchoolRole('teacher', $schoolId);
        $classes = $this->classesAvailableTo($request->user(), $schoolId, $selectedYearId);
        $selectedClassId = (int) ($filters['class_id'] ?? $classes->first()?->id ?? 0);
        if ($selectedClassId && ! $classes->contains('id', $selectedClassId)) {
            if ($isTeacher) {
                $selectedClassId = 0;
            } else {
                abort(404);
            }
        }
        $sections = Section::forSchool($schoolId)
            ->whereIn('class_id', $classes->pluck('id'))
            ->orderBy('name')
            ->get();
        $selectedSectionId = (int) ($filters['section_id'] ?? $sections->firstWhere('class_id', $selectedClassId)?->id ?? 0);
        if ($selectedSectionId && (! $sections->contains('id', $selectedSectionId)
            || (int) $sections->firstWhere('id', $selectedSectionId)->class_id !== $selectedClassId)) {
            abort(404);
        }
        $selectedDate = $filters['date'] ?? now()->toDateString();

        $session = null;
        $students = collect();
        $existingRecords = collect();

        if ($selectedYearId && $selectedClassId && $selectedSectionId && $selectedDate) {
            $session = AttendanceSession::forSchool($schoolId)
                ->where('academic_year_id', $selectedYearId)
                ->where('class_id', $selectedClassId)
                ->where('section_id', $selectedSectionId)
                ->whereDate('attendance_date', $selectedDate)
                ->first();

            $savedStudentIds = $session
                ? StudentAttendance::forSchool($schoolId)
                    ->where('attendance_session_id', $session->id)
                    ->pluck('student_id')
                : collect();
            $enrollments = StudentEnrollment::forSchool($schoolId)
                ->where('academic_year_id', $selectedYearId)
                ->where('class_id', $selectedClassId)
                ->where('section_id', $selectedSectionId)
                ->where(function (Builder $query) use ($savedStudentIds): void {
                    $query->where('status', 'enrolled');
                    if ($savedStudentIds->isNotEmpty()) {
                        $query->orWhereIn('student_id', $savedStudentIds);
                    }
                })
                ->whereHas('student')
                ->get();

            $recordedStudents = $session
                ? Student::forSchool($schoolId)->whereIn('id', $savedStudentIds)->get()
                : collect();
            $students = $enrollments->pluck('student')
                ->filter()
                ->merge($recordedStudents)
                ->unique('id')
                ->values();

            if ($session) {
                $existingRecords = StudentAttendance::forSchool($schoolId)
                    ->where('attendance_session_id', $session->id)
                    ->get()
                    ->keyBy('student_id');
            }
        }
        $noAssignments = $isTeacher && $classes->isEmpty();
        $canManageTeacherAttendance = $request->user()->hasSchoolRole(['school_admin', 'principal'], $schoolId);

        return view('school-admin.attendance.index', compact(
            'classes',
            'academicYears',
            'sections',
            'selectedYearId',
            'selectedClassId',
            'selectedSectionId',
            'selectedDate',
            'session',
            'students',
            'existingRecords',
            'isTeacher',
            'noAssignments',
            'canManageTeacherAttendance'
        ));
    }

    /**
     * Save roll call draft or submit/finalize.
     */
    public function saveRollCall(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');
        $user = $request->user();

        $validated = $request->validate([
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'class_id' => ['required', SchoolAccess::existsInSchool('classes', $schoolId)],
            'section_id' => ['required', SchoolAccess::existsInSchool('sections', $schoolId)],
            'date' => ['required', 'date_format:Y-m-d'],
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'array'],
            'attendance.*.status' => ['required', 'in:present,absent,late,leave,excused'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:255'],
            'action' => ['required', 'in:save_draft,finalize'],
            'correction_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $academicYear = AcademicYear::forSchool($schoolId)->findOrFail($validated['academic_year_id']);
        $class = SchoolClass::forSchool($schoolId)->findOrFail($validated['class_id']);
        $this->authorizeClass($user, $schoolId, $academicYear->id, $class->id);
        if ($validated['date'] < $academicYear->starts_on->toDateString() || $validated['date'] > $academicYear->ends_on->toDateString()) {
            throw ValidationException::withMessages([
                'date' => 'The attendance date must fall within the selected academic year.',
            ]);
        }

        $sectionBelongsToClass = Section::forSchool($schoolId)
            ->whereKey($validated['section_id'])
            ->where('class_id', $validated['class_id'])
            ->exists();

        if (! $sectionBelongsToClass) {
            throw ValidationException::withMessages([
                'section_id' => 'The selected section does not belong to the selected class.',
            ]);
        }

        $submittedStudentIds = array_keys($validated['attendance']);
        $hasInvalidStudentId = count(array_filter(
            $submittedStudentIds,
            fn ($studentId) => filter_var($studentId, FILTER_VALIDATE_INT) === false
        )) > 0;

        if ($hasInvalidStudentId) {
            throw ValidationException::withMessages([
                'attendance' => 'The attendance list contains an invalid student.',
            ]);
        }

        $eligibleStudentIds = StudentEnrollment::forSchool($schoolId)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('status', 'enrolled')
            ->whereHas('student', fn ($studentQuery) => $studentQuery->where('status', 'active'))
            ->distinct()
            ->pluck('student_id');
        $existingSession = AttendanceSession::forSchool($schoolId)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->whereDate('attendance_date', $validated['date'])
            ->first();
        if ($existingSession) {
            $eligibleStudentIds = $eligibleStudentIds->merge(
                StudentAttendance::forSchool($schoolId)
                    ->where('attendance_session_id', $existingSession->id)
                    ->pluck('student_id')
            )->unique()->values();
        }
        $eligibleIds = $eligibleStudentIds->map(fn ($id): int => (int) $id)->sort()->values()->all();
        $submittedIds = collect($submittedStudentIds)->map(fn ($id): int => (int) $id)->sort()->values()->all();

        if ($eligibleIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance may only be recorded for active students enrolled in this class and section.',
            ]);
        }

        DB::transaction(function () use ($schoolId, $validated, $user): void {
            $isFinalizing = $validated['action'] === 'finalize';

            $session = AttendanceSession::forSchool($schoolId)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('class_id', $validated['class_id'])
                ->where('section_id', $validated['section_id'])
                ->whereDate('attendance_date', $validated['date'])
                ->lockForUpdate()
                ->first();

            if (! $session) {
                try {
                    $session = AttendanceSession::create([
                        'school_id' => $schoolId,
                        'academic_year_id' => $validated['academic_year_id'],
                        'class_id' => $validated['class_id'],
                        'section_id' => $validated['section_id'],
                        'attendance_date' => $validated['date'],
                        'taken_by' => $user->id,
                        'status' => 'draft',
                    ]);
                } catch (QueryException $exception) {
                    $session = AttendanceSession::forSchool($schoolId)
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('class_id', $validated['class_id'])
                        ->where('section_id', $validated['section_id'])
                        ->whereDate('attendance_date', $validated['date'])
                        ->lockForUpdate()
                        ->first();

                    if (! $session) {
                        throw $exception;
                    }
                }
            }

            $wasFinalized = in_array($session->status, ['finalized', 'corrected'], true);

            foreach ($validated['attendance'] as $studentId => $data) {
                $status = $data['status'] === 'leave' ? 'excused' : $data['status'];
                $remarks = $data['remarks'] ?? null;

                $attendance = StudentAttendance::forSchool($schoolId)
                    ->where('attendance_session_id', $session->id)
                    ->where('student_id', $studentId)
                    ->first();

                if ($attendance) {
                    if ($wasFinalized && ($attendance->status !== $status || $attendance->remarks !== $remarks)) {
                        abort_unless(
                            SchoolAccess::hasSchoolRole($user, ['school_admin'], $schoolId),
                            403,
                            'Only a school administrator can correct finalized attendance.'
                        );
                        if (blank($validated['correction_reason'] ?? null)) {
                            throw ValidationException::withMessages([
                                'correction_reason' => 'A reason is required when correcting finalized attendance.',
                            ]);
                        }

                        AttendanceChangeLog::create([
                            'school_id' => $schoolId,
                            'student_attendance_id' => $attendance->id,
                            'old_status' => $attendance->status,
                            'new_status' => $status,
                            'changed_by' => $user->id,
                            'reason' => $validated['correction_reason'] ?? 'Administrative correction',
                            'created_at' => now(),
                        ]);
                    }

                    $attendance->update([
                        'status' => $status,
                        'remarks' => $remarks,
                        'marked_by' => $user->id,
                        'marked_at' => now(),
                    ]);
                } else {
                    StudentAttendance::create([
                        'school_id' => $schoolId,
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                        'status' => $status,
                        'remarks' => $remarks,
                        'marked_by' => $user->id,
                        'marked_at' => now(),
                    ]);
                }
            }

            if ($isFinalizing) {
                $session->update([
                    'status' => $wasFinalized ? 'corrected' : 'finalized',
                    'finalized_at' => now(),
                ]);
            }

            AuditLogService::log('attendance_marked', 'AttendanceSession', $session->id, [
                'date' => $validated['date'],
                'class_id' => $validated['class_id'],
                'status' => $session->status,
            ], $schoolId);
        });

        return redirect()->route('school-admin.attendance.index', [
            'academic_year_id' => $validated['academic_year_id'],
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
            'date' => $validated['date'],
        ])->with('status', 'Attendance successfully saved.');
    }

    /**
     * Export attendance to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $schoolId = session('current_school_id');
        $filters = $request->validate([
            'academic_year_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'class_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('classes', $schoolId)],
            'section_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('sections', $schoolId)],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        if ($request->user()->hasSchoolRole('teacher', $schoolId)) {
            abort_unless(
                ! empty($filters['academic_year_id']) && ! empty($filters['class_id']) && ! empty($filters['section_id']),
                403,
                'Teachers may only export attendance for an assigned class and section.'
            );
            $this->authorizeClass($request->user(), $schoolId, (int) $filters['academic_year_id'], (int) $filters['class_id']);
            abort_unless(
                Section::forSchool($schoolId)->whereKey($filters['section_id'])->where('class_id', $filters['class_id'])->exists(),
                404
            );
        }
        $query = StudentAttendance::forSchool($schoolId)
            ->with(['student', 'session.class', 'session.section']);

        if (! empty($filters['date'])) {
            $query->whereHas('session', fn (Builder $sessions) => $sessions->whereDate('attendance_date', $filters['date']));
        }
        if (! empty($filters['academic_year_id'])) {
            $query->whereHas('session', fn (Builder $sessions) => $sessions->where('academic_year_id', $filters['academic_year_id']));
        }
        if (! empty($filters['class_id'])) {
            $query->whereHas('session', fn (Builder $sessions) => $sessions->where('class_id', $filters['class_id']));
        }
        if (! empty($filters['section_id'])) {
            $query->whereHas('session', fn (Builder $sessions) => $sessions->where('section_id', $filters['section_id']));
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student-attendance-'.date('Y-m-d').'.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student ID', 'Admission No', 'Student Name', 'Date', 'Class', 'Section', 'Status', 'Remarks']);

            $query->chunk(100, function ($records) use ($handle) {
                foreach ($records as $record) {
                    fputcsv($handle, [
                        $record->student_id,
                        $record->student->admission_number ?? 'N/A',
                        $record->student->full_name ?? 'N/A',
                        $record->session->attendance_date?->format('Y-m-d') ?? 'N/A',
                        $record->session->class->name ?? 'N/A',
                        $record->session->section->name ?? 'N/A',
                        ucfirst($record->status),
                        $record->remarks ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function register(Request $request): View|StreamedResponse
    {
        $schoolId = session('current_school_id');
        $filters = $request->validate([
            'academic_year_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'class_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('classes', $schoolId)],
            'section_id' => ['nullable', 'integer', SchoolAccess::existsInSchool('sections', $schoolId)],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'export' => ['nullable', Rule::in(['csv'])],
        ]);
        $academicYears = AcademicYear::forSchool($schoolId)->get();
        $school = School::findOrFail($schoolId);
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();
        $selectedYearId = (int) ($filters['academic_year_id'] ?? $currentYear?->id ?? 0);
        $isTeacher = $request->user()->hasSchoolRole('teacher', $schoolId);
        $classes = $this->classesAvailableTo($request->user(), $schoolId, $selectedYearId);
        $selectedClassId = (int) ($filters['class_id'] ?? 0);
        if ($selectedClassId && ! $classes->contains('id', $selectedClassId)) {
            if ($isTeacher) {
                $selectedClassId = 0;
            } else {
                abort(404);
            }
        }
        $sections = Section::forSchool($schoolId)->whereIn('class_id', $classes->pluck('id'))->orderBy('name')->get();
        $selectedSectionId = (int) ($filters['section_id'] ?? 0);
        if ($selectedSectionId && (! $sections->contains('id', $selectedSectionId)
            || (int) $sections->firstWhere('id', $selectedSectionId)->class_id !== $selectedClassId)) {
            abort(404);
        }
        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $startDate = CarbonImmutable::create($year, $month, 1)->startOfMonth();
        $dates = collect(range(0, $startDate->daysInMonth - 1))
            ->map(fn (int $offset): CarbonImmutable => $startDate->addDays($offset));
        $students = collect();
        $records = collect();
        $summary = ['students' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'leave' => 0, 'recorded' => 0, 'percentage' => 0];

        if ($selectedClassId && $selectedYearId) {
            $enrollments = StudentEnrollment::forSchool($schoolId)
                ->with('student')
                ->where('academic_year_id', $selectedYearId)
                ->where('class_id', $selectedClassId)
                ->when($selectedSectionId, fn (Builder $query) => $query->where('section_id', $selectedSectionId))
                ->whereHas('student')
                ->get();

            $sessions = AttendanceSession::forSchool($schoolId)
                ->where('academic_year_id', $selectedYearId)
                ->where('class_id', $selectedClassId)
                ->when($selectedSectionId, fn (Builder $query) => $query->where('section_id', $selectedSectionId))
                ->whereBetween('attendance_date', [$startDate->toDateString(), $startDate->endOfMonth()->toDateString()])
                ->get();
            $records = StudentAttendance::forSchool($schoolId)
                ->whereIn('attendance_session_id', $sessions->pluck('id'))
                ->get()
                ->groupBy(fn (StudentAttendance $record): int => (int) $record->student_id);
            $students = Student::forSchool($schoolId)
                ->whereIn('id', $enrollments->pluck('student_id')->merge($records->keys())->unique())
                ->orderBy('admission_number')
                ->get();
            $summary['students'] = $students->count();
            $sessionDates = $sessions->mapWithKeys(
                fn (AttendanceSession $session): array => [$session->id => $session->attendance_date->toDateString()]
            );

            foreach ($students as $student) {
                $studentRecords = $records->get($student->id, collect());
                $student->monthlyAttendance = $studentRecords->mapWithKeys(
                    fn (StudentAttendance $record): array => [
                        $sessionDates->get($record->attendance_session_id) => $record,
                    ]
                );
                $monthlyTotals = [
                    'present' => $studentRecords->where('status', 'present')->count(),
                    'absent' => $studentRecords->where('status', 'absent')->count(),
                    'late' => $studentRecords->where('status', 'late')->count(),
                    'leave' => $studentRecords->where('status', 'excused')->count(),
                    'recorded' => $studentRecords->count(),
                ];
                $monthlyTotals['percentage'] = $monthlyTotals['recorded'] > 0
                    ? round(($monthlyTotals['present'] + $monthlyTotals['late']) / $monthlyTotals['recorded'] * 100, 1)
                    : null;
                $student->monthlyTotals = $monthlyTotals;
            }

            foreach (['present', 'absent', 'late'] as $status) {
                $summary[$status] = $records->flatten()->where('status', $status)->count();
            }
            $summary['leave'] = $records->flatten()->where('status', 'excused')->count();
            $summary['recorded'] = $records->flatten()->count();
            $summary['percentage'] = $summary['recorded'] > 0
                ? round(($summary['present'] + $summary['late']) / $summary['recorded'] * 100, 1)
                : 0;

            if (($filters['export'] ?? null) === 'csv') {
                return $this->exportMonthlyCsv($students, $dates, $summary, $month, $year);
            }
        }

        $noAssignments = $isTeacher && $classes->isEmpty();

        return view('school-admin.attendance.register', compact(
            'academicYears',
            'school',
            'classes',
            'sections',
            'selectedYearId',
            'selectedClassId',
            'selectedSectionId',
            'month',
            'year',
            'dates',
            'students',
            'summary',
            'noAssignments'
        ));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, SchoolClass>
     */
    private function classesAvailableTo(User $user, int $schoolId, int $academicYearId): \Illuminate\Database\Eloquent\Collection
    {
        $query = SchoolClass::forSchool($schoolId)->with('sections')->orderBy('sort_order')->orderBy('name');

        if ($user->hasSchoolRole('teacher', $schoolId)) {
            if (! $academicYearId) {
                return $query->whereRaw('1 = 0')->get();
            }

            $classIds = $this->assignedClassIds($user, $schoolId, $academicYearId);
            $query->whereIn('id', $classIds);
        }

        return $query->get();
    }

    /**
     * @return list<int>
     */
    private function assignedClassIds(User $user, int $schoolId, int $academicYearId): array
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

    private function authorizeClass(User $user, int $schoolId, int $academicYearId, int $classId): void
    {
        if ($user->hasSchoolRole('teacher', $schoolId)
            && ! in_array($classId, $this->assignedClassIds($user, $schoolId, $academicYearId), true)) {
            abort(403, 'Attendance may only be managed for assigned classes.');
        }
    }

    /**
     * @param  Collection<int, Student>  $students
     * @param  Collection<int, CarbonImmutable>  $dates
     * @param  array{students: int, present: int, absent: int, late: int, leave: int, recorded: int, percentage: int|float}  $summary
     */
    private function exportMonthlyCsv($students, $dates, array $summary, int $month, int $year): StreamedResponse
    {
        return response()->streamDownload(function () use ($students, $dates): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_merge(
                ['Admission number', 'Student name'],
                $dates->map(fn (CarbonImmutable $date): string => $date->format('Y-m-d'))->all(),
                ['Present', 'Absent', 'Late', 'Leave', 'Recorded', 'Attendance %']
            ));

            foreach ($students as $student) {
                $row = [$student->admission_number, $student->full_name];
                foreach ($dates as $date) {
                    $status = $student->monthlyAttendance->get($date->toDateString())?->status;
                    $row[] = match ($status) {
                        'excused' => 'Leave',
                        null => '-',
                        default => ucfirst($status),
                    };
                }
                $row = array_merge($row, array_values($student->monthlyTotals));
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, sprintf('student-attendance-%04d-%02d.csv', $year, $month), ['Content-Type' => 'text/csv']);
    }
}
