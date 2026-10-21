<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\ExamMarksImportService;
use App\Services\SpreadsheetService;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamMarksImportController extends Controller
{
    private const MAXIMUM_ROWS = 5000;

    public function __construct(
        private readonly ExamMarksImportService $marksImport,
        private readonly SpreadsheetService $spreadsheets
    ) {}

    public function create(ExamSubject $examSubject, Request $request): View
    {
        $classSubject = $this->authorizeExamSubject($request->user(), $examSubject);
        $sections = Section::forSchool((int) session('current_school_id'))
            ->where('class_id', $classSubject->class_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $report = null;
        $reportToken = $request->session()->get('marks_import_report_token');
        if (is_string($reportToken)) {
            $report = $this->cachedImport($reportToken, $request->user(), $examSubject, true);
            $request->session()->forget('marks_import_report_token');
        }

        return view('school-admin.exams.marks-import', compact('examSubject', 'sections', 'report', 'reportToken'));
    }

    public function downloadTemplate(ExamSubject $examSubject, Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $classSubject = $this->authorizeExamSubject($request->user(), $examSubject);
        $schoolId = (int) session('current_school_id');
        $sectionId = $this->validateSection($request, $classSubject, $schoolId);
        $format = $request->validate([
            'format' => ['nullable', Rule::in(['xlsx', 'csv'])],
        ])['format'] ?? 'xlsx';

        $examSubject->loadMissing(['exam.academicYear', 'classSubject.class', 'classSubject.subject']);
        $enrollments = StudentEnrollment::forSchool($schoolId)
            ->where('academic_year_id', $examSubject->exam->academic_year_id)
            ->where('class_id', $classSubject->class_id)
            ->where('status', 'enrolled')
            ->when($sectionId !== null, fn ($query) => $query->where('section_id', $sectionId))
            ->whereHas('student', fn ($student) => $student->forSchool($schoolId)->where('status', 'active'))
            ->with([
                'student' => fn ($student) => $student->forSchool($schoolId),
                'section' => fn ($section) => $section->forSchool($schoolId),
            ])
            ->orderBy('student_id')
            ->lazy(500);

        $rows = (function () use ($enrollments): \Generator {
            yield ['admission_number', 'student_name', 'section', 'obtained_marks', 'status'];

            foreach ($enrollments as $enrollment) {
                if (! $enrollment->student || ! $enrollment->section) {
                    continue;
                }

                yield [
                    $enrollment->student->admission_number,
                    $enrollment->student->full_name,
                    $enrollment->section->name,
                    '',
                    'graded',
                ];
            }
        })();

        return $this->spreadsheets->download(
            $rows,
            $this->filename($examSubject, 'marksheet-template', $format)
        );
    }

    public function preview(Request $request, ExamSubject $examSubject): View
    {
        $classSubject = $this->authorizeExamSubject($request->user(), $examSubject);
        $this->ensureExamCanBeEdited($examSubject);
        $schoolId = (int) session('current_school_id');
        $sectionId = $this->validateSection($request, $classSubject, $schoolId);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'extensions:csv,xlsx', 'max:10240'],
        ]);

        $spreadsheetRows = $this->spreadsheets->readUploadedFile($validated['file'], self::MAXIMUM_ROWS);
        $analysis = $this->marksImport->analyze($schoolId, $examSubject, $sectionId, $spreadsheetRows);
        $token = Str::random(64);

        Cache::put($this->cacheKey($token), [
            'status' => 'preview',
            'user_id' => $request->user()->id,
            'school_id' => $schoolId,
            'exam_subject_id' => $examSubject->id,
            'section_id' => $sectionId,
            'spreadsheet_rows' => $spreadsheetRows,
        ], now()->addMinutes(30));

        return view('school-admin.exams.marks-import-preview', compact('examSubject', 'analysis', 'token'));
    }

    public function confirm(Request $request, ExamSubject $examSubject): RedirectResponse
    {
        $validated = $request->validate([
            'import_token' => ['required', 'string', 'size:64', 'regex:/^[A-Za-z0-9]+$/'],
            'confirm_import' => ['required', 'accepted'],
            'replace_existing' => ['nullable', 'boolean'],
        ]);

        $cached = $this->cachedImport($validated['import_token'], $request->user(), $examSubject);
        $this->authorizeExamSubject($request->user(), $examSubject);

        $result = $this->marksImport->import(
            (int) session('current_school_id'),
            $examSubject,
            $cached['section_id'],
            $cached['spreadsheet_rows'],
            $request->boolean('replace_existing'),
            $request->user()->id
        );

        Cache::put($this->cacheKey($validated['import_token']), [
            ...$cached,
            'status' => 'completed',
            'result' => $result,
        ], now()->addMinutes(30));

        $request->session()->flash('marks_import_report_token', $validated['import_token']);

        return redirect()->route('school-admin.exams.marks.import', $examSubject)
            ->with('status', 'Marks import completed.');
    }

    public function downloadErrors(Request $request, ExamSubject $examSubject): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorizeExamSubject($request->user(), $examSubject);
        $validated = $request->validate([
            'import_token' => ['required', 'string', 'size:64', 'regex:/^[A-Za-z0-9]+$/'],
        ]);
        $cached = $this->cachedImport($validated['import_token'], $request->user(), $examSubject, true);
        $errors = $cached['result']['errors'] ?? [];

        $rows = (function () use ($errors): \Generator {
            yield ['spreadsheet_row', 'admission_number', 'reason'];

            foreach ($errors as $error) {
                yield [$error['row_number'], $error['admission_number'], $error['reason']];
            }
        })();

        return $this->spreadsheets->download(
            $rows,
            $this->filename($examSubject, 'marks-import-errors', 'csv')
        );
    }

    public function export(Request $request, ExamSubject $examSubject): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $classSubject = $this->authorizeExamSubject($request->user(), $examSubject);
        $schoolId = (int) session('current_school_id');
        $sectionId = $this->validateSection($request, $classSubject, $schoolId);
        $validated = $request->validate([
            'format' => ['nullable', Rule::in(['xlsx', 'csv'])],
        ]);

        $marks = Mark::forSchool($schoolId)
            ->where('exam_subject_id', $examSubject->id)
            ->whereHas('student', fn ($student) => $student->forSchool($schoolId))
            ->when($sectionId !== null, fn ($query) => $query->whereHas('student.enrollments', fn ($enrollment) => $enrollment
                ->forSchool($schoolId)
                ->where('academic_year_id', $examSubject->exam->academic_year_id)
                ->where('class_id', $classSubject->class_id)
                ->where('section_id', $sectionId)
                ->where('status', 'enrolled')))
            ->with([
                'student' => fn ($student) => $student->forSchool($schoolId),
                'examSubject' => fn ($subject) => $subject->forSchool($schoolId)->with([
                    'exam' => fn ($exam) => $exam->forSchool($schoolId),
                ]),
            ])
            ->orderBy('student_id')
            ->lazy(500);

        $rows = (function () use ($marks): \Generator {
            yield ['admission_number', 'student_name', 'marks_obtained', 'status', 'maximum_marks'];

            foreach ($marks as $mark) {
                if (! $mark->student || ! $mark->examSubject) {
                    continue;
                }

                yield [
                    $mark->student->admission_number,
                    $mark->student->full_name,
                    $mark->marks_obtained,
                    $mark->status,
                    $mark->examSubject->max_marks,
                ];
            }
        })();

        $extension = $validated['format'] ?? 'xlsx';

        return $this->spreadsheets->download(
            $rows,
            $this->filename($examSubject, 'marks', $extension)
        );
    }

    /**
     * @return \App\Models\ClassSubject
     */
    private function authorizeExamSubject(?User $user, ExamSubject $examSubject): ClassSubject
    {
        $schoolId = (int) session('current_school_id');
        if (! $user || $user->isSuperAdmin() || ! SchoolAccess::hasSchoolRole($user, ['school_admin', 'principal', 'teacher'], $schoolId)) {
            abort(403);
        }

        $examSubject->loadMissing(['exam', 'classSubject.class', 'classSubject.subject']);
        $classSubject = $examSubject->classSubject;
        $exam = $examSubject->exam;

        if (
            $examSubject->school_id !== $schoolId
            || $exam->school_id !== $schoolId
            || $classSubject->school_id !== $schoolId
            || $exam->academic_year_id !== $classSubject->academic_year_id
            || $classSubject->class?->school_id !== $schoolId
            || $classSubject->subject?->school_id !== $schoolId
        ) {
            abort(404);
        }

        if ($user->schoolRole($schoolId) === 'teacher') {
            if (! SchoolAccess::teacherIsAssignedToClassSubject($user, $classSubject, $schoolId)) {
                abort(403, 'Teachers may only access marks for their assigned classes and subjects.');
            }
        }

        return $classSubject;
    }

    private function ensureExamCanBeEdited(ExamSubject $examSubject): void
    {
        if ($examSubject->exam->status === 'locked') {
            throw ValidationException::withMessages([
                'file' => 'This exam is locked. Marks cannot be changed.',
            ]);
        }
    }

    private function validateSection(Request $request, ClassSubject $classSubject, int $schoolId): ?int
    {
        $validated = $request->validate([
            'section_id' => [
                'nullable',
                'integer',
                Rule::exists('sections', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('class_id', $classSubject->class_id)),
            ],
        ]);

        return isset($validated['section_id']) ? (int) $validated['section_id'] : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function cachedImport(string $token, ?User $user, ExamSubject $examSubject, bool $completed = false): array
    {
        $cached = Cache::get($this->cacheKey($token));
        if (
            ! is_array($cached)
            || ! $user
            || $cached['user_id'] !== $user->id
            || $cached['school_id'] !== (int) session('current_school_id')
            || $cached['exam_subject_id'] !== $examSubject->id
            || ($completed && ($cached['status'] ?? null) !== 'completed')
        ) {
            abort(404, 'The import preview has expired or is unavailable.');
        }

        if (! $completed && ($cached['status'] ?? null) !== 'preview') {
            throw ValidationException::withMessages([
                'import_token' => 'This import has already been completed.',
            ]);
        }

        return $cached;
    }

    private function cacheKey(string $token): string
    {
        return 'marks-import:'.$token;
    }

    private function filename(ExamSubject $examSubject, string $purpose, string $extension): string
    {
        $examSubject->loadMissing(['exam.academicYear', 'classSubject.class', 'classSubject.subject']);
        $context = implode('-', [
            $examSubject->exam->academicYear->name,
            $examSubject->exam->name,
            $examSubject->classSubject->class->name,
            $examSubject->classSubject->subject->name,
            $purpose,
        ]);

        return Str::slug($context).'.'.$extension;
    }
}
