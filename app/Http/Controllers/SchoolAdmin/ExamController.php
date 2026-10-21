<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\GradingScale;
use App\Models\Mark;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\AuditLogService;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamController extends Controller
{
    /**
     * Exams listing.
     */
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $exams = Exam::forSchool($schoolId)->with(['academicYear', 'examSubjects.classSubject.subject', 'examSubjects.classSubject.class'])->latest()->get();
        $academicYears = AcademicYear::forSchool($schoolId)->get();

        return view('school-admin.exams.index', compact('exams', 'academicYears'));
    }

    /**
     * Create exam.
     */
    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');
        if (! SchoolAccess::hasSchoolRole($request->user(), ['school_admin', 'principal'], (int) $schoolId)) {
            abort(403);
        }

        $validated = $request->validate([
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'name' => ['required', 'string', 'max:100'],
            'exam_type' => ['required', 'in:mid_term,final_term,quiz,unit_test,assignment'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);

        $exam = Exam::create([
            'school_id' => $schoolId,
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'exam_type' => $validated['exam_type'],
            'starts_on' => $validated['starts_on'],
            'ends_on' => $validated['ends_on'],
            'status' => 'draft',
        ]);

        AuditLogService::log('exam_created', 'Exam', $exam->id, ['name' => $exam->name], $schoolId);

        return back()->with('status', "Exam '{$exam->name}' created.");
    }

    /**
     * Manage exam subjects and marks entry.
     */
    public function show(Exam $exam): View
    {
        $schoolId = session('current_school_id');
        if ($exam->school_id !== $schoolId) {
            abort(403);
        }

        $exam->load(['academicYear', 'examSubjects.classSubject.class', 'examSubjects.classSubject.subject']);
        $classSubjects = ClassSubject::forSchool($schoolId)
            ->where('academic_year_id', $exam->academic_year_id)
            ->with(['class', 'subject'])
            ->get();

        return view('school-admin.exams.show', compact('exam', 'classSubjects'));
    }

    /**
     * Add subject to exam.
     */
    public function addSubject(Request $request, Exam $exam): RedirectResponse
    {
        $schoolId = session('current_school_id');
        if (
            $exam->school_id !== $schoolId
            || ! SchoolAccess::hasSchoolRole($request->user(), ['school_admin', 'principal'], (int) $schoolId)
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'class_subject_id' => [
                'required',
                SchoolAccess::existsInSchool('class_subjects', $schoolId)
                    ->where('academic_year_id', $exam->academic_year_id),
            ],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'passing_marks' => ['required', 'numeric', 'min:0'],
            'exam_date' => ['nullable', 'date'],
        ]);

        ExamSubject::firstOrCreate(
            [
                'school_id' => $schoolId,
                'exam_id' => $exam->id,
                'class_subject_id' => $validated['class_subject_id'],
            ],
            [
                'max_marks' => $validated['max_marks'],
                'passing_marks' => $validated['passing_marks'],
                'exam_date' => $validated['exam_date'] ?? null,
            ]
        );

        return back()->with('status', 'Subject added to exam schedule.');
    }

    /**
     * Marks Entry Sheet for an Exam Subject.
     */
    public function marksEntry(ExamSubject $examSubject): View
    {
        $schoolId = (int) session('current_school_id');
        if (
            $examSubject->school_id !== $schoolId
            || ! SchoolAccess::hasSchoolRole(auth()->user(), ['school_admin', 'principal', 'teacher'], $schoolId)
        ) {
            abort(403);
        }

        $examSubject->load(['exam', 'classSubject.class', 'classSubject.subject']);
        if (
            auth()->user()->schoolRole($schoolId) === 'teacher'
            && ! SchoolAccess::teacherIsAssignedToClassSubject(auth()->user(), $examSubject->classSubject, (int) $schoolId)
        ) {
            abort(403, 'Teachers may only access marks for their assigned classes and subjects.');
        }

        // Load enrolled students in this class
        $enrollments = StudentEnrollment::forSchool($schoolId)
            ->with('student')
            ->where('class_id', $examSubject->classSubject->class_id)
            ->where('academic_year_id', $examSubject->exam->academic_year_id)
            ->where('status', 'enrolled')
            ->get();

        $students = $enrollments->pluck('student')->filter();
        $existingMarks = Mark::forSchool($schoolId)
            ->where('exam_subject_id', $examSubject->id)
            ->get()
            ->keyBy('student_id');

        return view('school-admin.exams.marks-entry', compact('examSubject', 'students', 'existingMarks'));
    }

    /**
     * Store/update marks with strict validation (0 <= marks <= max_marks).
     */
    public function saveMarks(Request $request, ExamSubject $examSubject): RedirectResponse
    {
        $schoolId = (int) session('current_school_id');
        if (
            $examSubject->school_id !== $schoolId
            || ! SchoolAccess::hasSchoolRole($request->user(), ['school_admin', 'principal', 'teacher'], $schoolId)
        ) {
            abort(403);
        }

        $examSubject->loadMissing(['exam', 'classSubject']);
        if (
            $request->user()->schoolRole($schoolId) === 'teacher'
            && ! SchoolAccess::teacherIsAssignedToClassSubject($request->user(), $examSubject->classSubject, $schoolId)
        ) {
            abort(403, 'Teachers may only enter marks for their assigned classes and subjects.');
        }

        $validated = $request->validate([
            'marks' => ['required', 'array', 'min:1'],
            'marks.*' => ['required', 'array'],
            'marks.*.status' => ['required', 'in:graded,absent,exempt'],
            'marks.*.obtained' => ['nullable', 'numeric', 'min:0'],
        ]);

        $markErrors = [];
        foreach ($validated['marks'] as $studentId => $data) {
            if ($data['status'] === 'graded' && ! filled($data['obtained'] ?? null)) {
                $markErrors["marks.{$studentId}.obtained"] = 'Obtained marks are required for graded students.';
            } elseif ($data['status'] !== 'graded' && filled($data['obtained'] ?? null)) {
                $markErrors["marks.{$studentId}.obtained"] = 'Obtained marks must be blank for absent or exempt students.';
            }
        }

        if ($markErrors !== []) {
            throw ValidationException::withMessages($markErrors);
        }

        $studentIds = array_keys($validated['marks']);
        $hasInvalidStudentId = count(array_filter(
            $studentIds,
            fn ($studentId) => filter_var($studentId, FILTER_VALIDATE_INT) === false
        )) > 0;

        if ($hasInvalidStudentId) {
            throw ValidationException::withMessages([
                'marks' => 'The marks list contains an invalid student.',
            ]);
        }

        $classSubject = $examSubject->classSubject;
        $eligibleStudentCount = StudentEnrollment::forSchool($schoolId)
            ->where('class_id', $classSubject->class_id)
            ->where('academic_year_id', $examSubject->exam->academic_year_id)
            ->where('status', 'enrolled')
            ->whereIn('student_id', $studentIds)
            ->whereHas('student', fn ($studentQuery) => $studentQuery->where('status', 'active'))
            ->distinct()
            ->count('student_id');

        if ($eligibleStudentCount !== count($studentIds)) {
            throw ValidationException::withMessages([
                'marks' => 'Marks may only be entered for active students enrolled in this class and academic year.',
            ]);
        }

        DB::transaction(function () use ($schoolId, $examSubject, $validated) {
            $exam = Exam::query()
                ->whereKey($examSubject->exam_id)
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($exam->status === 'locked') {
                throw ValidationException::withMessages([
                    'error' => 'This exam results are locked. Marks cannot be changed.',
                ]);
            }

            $lockedExamSubject = ExamSubject::query()
                ->whereKey($examSubject->id)
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($validated['marks'] as $studentId => $data) {
                $status = $data['status'] ?? 'graded';
                $obtained = $status === 'graded' ? (float) $data['obtained'] : null;

                if ($obtained !== null && ($obtained < 0 || $obtained > (float) $lockedExamSubject->max_marks)) {
                    throw ValidationException::withMessages([
                        "marks.{$studentId}.obtained" => "Marks must be between 0 and {$lockedExamSubject->max_marks}.",
                    ]);
                }

                Mark::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'exam_subject_id' => $lockedExamSubject->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'marks_obtained' => $obtained,
                        'status' => $status,
                        'entered_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            AuditLogService::log('marks_entered', 'ExamSubject', $lockedExamSubject->id, [
                'exam_id' => $lockedExamSubject->exam_id,
            ], $schoolId);
        });

        return back()->with('status', 'Marks saved successfully.');
    }

    /**
     * Publish or Lock Exam.
     */
    public function togglePublish(Exam $exam): RedirectResponse
    {
        $schoolId = (int) session('current_school_id');
        if ($exam->school_id !== $schoolId) {
            abort(403);
        }

        if (! SchoolAccess::hasSchoolRole(auth()->user(), ['school_admin', 'principal'], $schoolId)) {
            abort(403);
        }

        $newStatus = DB::transaction(function () use ($exam, $schoolId): string {
            $lockedExam = Exam::query()
                ->whereKey($exam->id)
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->firstOrFail();
            if ($lockedExam->status === 'locked') {
                throw ValidationException::withMessages([
                    'status' => 'This exam is locked and cannot be unlocked from this screen.',
                ]);
            }

            $status = $lockedExam->status === 'draft' ? 'published' : 'locked';
            $lockedExam->update(['status' => $status]);

            AuditLogService::log('exam_status_toggled', 'Exam', $lockedExam->id, ['status' => $status], $schoolId);

            return $status;
        });

        return back()->with('status', "Exam status updated to '{$newStatus}'.");
    }

    /**
     * Printable Student Report Card.
     */
    public function reportCard(Exam $exam, Student $student): View
    {
        $schoolId = (int) session('current_school_id');
        if ($exam->school_id !== $schoolId || $student->school_id !== $schoolId) {
            abort(403);
        }

        if (! SchoolAccess::hasSchoolRole(auth()->user(), ['school_admin', 'principal'], $schoolId)) {
            abort(403);
        }

        $enrollments = StudentEnrollment::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->where('academic_year_id', $exam->academic_year_id)
            ->whereIn('status', ['enrolled', 'promoted', 'transferred'])
            ->with(['class', 'section'])
            ->get();

        if ($enrollments->count() !== 1) {
            abort(404);
        }

        $enrollment = $enrollments->first();
        $classId = $enrollment->class_id;
        $exam->load('academicYear');
        $examSubjects = ExamSubject::forSchool($schoolId)
            ->where('exam_id', $exam->id)
            ->whereHas('classSubject', fn ($classSubject) => $classSubject
                ->forSchool($schoolId)
                ->where('class_id', $classId)
                ->where('academic_year_id', $exam->academic_year_id))
            ->with([
                'classSubject' => fn ($classSubject) => $classSubject
                    ->forSchool($schoolId)
                    ->with(['class', 'subject']),
            ])
            ->get();

        if ($examSubjects->isEmpty()) {
            abort(404);
        }

        $marks = Mark::forSchool($schoolId)
            ->whereIn('exam_subject_id', $examSubjects->pluck('id'))
            ->where('student_id', $student->id)
            ->with('examSubject.classSubject.subject')
            ->get();
        $marksByExamSubject = $marks->keyBy('exam_subject_id');

        $totalMax = $examSubjects->sum('max_marks');
        $totalObtained = $marks->where('status', 'graded')->sum('marks_obtained');
        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
        $grade = GradingScale::getGradeForPercentage($schoolId, $percentage);

        return view('school-admin.exams.report-card', compact(
            'exam',
            'student',
            'enrollment',
            'examSubjects',
            'marksByExamSubject',
            'totalMax',
            'totalObtained',
            'percentage',
            'grade'
        ));
    }
}
