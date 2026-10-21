<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExamMarksImportService
{
    private const REQUIRED_HEADERS = [
        'admission_number',
        'student_name',
        'section',
        'obtained_marks',
        'status',
    ];

    /**
     * @param  list<list<bool|float|int|string|null>>  $spreadsheetRows
     * @return array{total: int, valid: int, conflicts: int, rejected: int, rows: list<array<string, mixed>>}
     */
    public function analyze(
        int $schoolId,
        ExamSubject $examSubject,
        ?int $sectionId,
        array $spreadsheetRows,
        bool $replaceExisting = false
    ): array {
        if (count($spreadsheetRows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'The spreadsheet must include a header row and at least one student row.',
            ]);
        }

        $header = array_map(
            fn (mixed $value): string => str_replace([' ', '-'], '_', Str::lower(trim((string) $value))),
            $spreadsheetRows[0]
        );
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]) ?? $header[0];
        }

        if (count($header) !== count(array_unique($header))) {
            throw ValidationException::withMessages([
                'file' => 'The spreadsheet contains duplicate column headings.',
            ]);
        }

        $columnIndexes = [];
        foreach (self::REQUIRED_HEADERS as $requiredHeader) {
            $index = array_search($requiredHeader, $header, true);
            if ($index === false) {
                throw ValidationException::withMessages([
                    'file' => "The spreadsheet is missing the required '{$requiredHeader}' column.",
                ]);
            }

            $columnIndexes[$requiredHeader] = $index;
        }

        $classSubject = $examSubject->classSubject;
        $exam = $examSubject->exam;
        if (
            $examSubject->school_id !== $schoolId
            || $exam->school_id !== $schoolId
            || $classSubject->school_id !== $schoolId
            || $exam->academic_year_id !== $classSubject->academic_year_id
        ) {
            abort(404);
        }

        $inputRows = [];
        foreach (array_slice($spreadsheetRows, 1, null, true) as $rowIndex => $values) {
            $input = [];
            foreach ($columnIndexes as $column => $columnIndex) {
                $input[$column] = $this->cellToString($values[$columnIndex] ?? null);
            }

            if (count(array_filter($input, fn (?string $value): bool => filled($value))) === 0) {
                continue;
            }

            $inputRows[] = [
                'row_number' => $rowIndex + 1,
                'admission_number' => trim($input['admission_number'] ?? ''),
                'student_name' => trim($input['student_name'] ?? ''),
                'section' => trim($input['section'] ?? ''),
                'obtained_marks' => trim($input['obtained_marks'] ?? ''),
                'status' => Str::lower(trim($input['status'] ?? '')),
            ];
        }

        if ($inputRows === []) {
            throw ValidationException::withMessages([
                'file' => 'The spreadsheet does not contain any student rows.',
            ]);
        }

        $identifierCounts = array_count_values(array_filter(array_column($inputRows, 'admission_number')));
        $identifiers = array_values(array_unique(array_filter(array_column($inputRows, 'admission_number'))));
        $studentsByAdmission = $identifiers === []
            ? collect()
            : Student::forSchool($schoolId)
                ->whereIn('admission_number', $identifiers)
                ->get()
                ->groupBy('admission_number');
        $studentIds = $studentsByAdmission->flatten()->pluck('id')->unique()->values();

        $enrollmentsByStudent = $studentIds->isEmpty()
            ? collect()
            : StudentEnrollment::forSchool($schoolId)
                ->whereIn('student_id', $studentIds)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('class_id', $classSubject->class_id)
                ->where('status', 'enrolled')
                ->when($sectionId !== null, fn ($query) => $query->where('section_id', $sectionId))
                ->with('section')
                ->get()
                ->groupBy('student_id');

        $existingMarks = $studentIds->isEmpty()
            ? collect()
            : Mark::forSchool($schoolId)
                ->where('exam_subject_id', $examSubject->id)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id');

        $analyzedRows = [];
        foreach ($inputRows as $inputRow) {
            $errors = [];
            $admissionNumber = $inputRow['admission_number'];
            $student = $studentsByAdmission->get($admissionNumber)?->first();
            $enrollments = $student ? $enrollmentsByStudent->get($student->id, collect()) : collect();
            $enrollment = $enrollments->count() === 1 ? $enrollments->first() : null;

            if ($admissionNumber === '') {
                $errors[] = 'Admission number is required.';
            } elseif (($identifierCounts[$admissionNumber] ?? 0) > 1) {
                $errors[] = 'Admission number appears more than once in this file.';
            } elseif ($studentsByAdmission->get($admissionNumber)?->count() !== 1) {
                $errors[] = 'Admission number does not match exactly one student in this school.';
            } elseif ($student->status !== 'active') {
                $errors[] = 'The matched student is not active.';
            } elseif ($enrollments->count() === 0) {
                $errors[] = 'Student is not actively enrolled in the selected class, year, and section.';
            } elseif ($enrollments->count() > 1) {
                $errors[] = 'Student has multiple matching enrollments and cannot be matched safely.';
            }

            if ($inputRow['student_name'] === '') {
                $errors[] = 'Student name is required for verification.';
            } elseif ($student && Str::lower($inputRow['student_name']) !== Str::lower($student->full_name)) {
                $errors[] = 'Student name does not match the admission number.';
            }

            if ($inputRow['section'] === '') {
                $errors[] = 'Section is required for verification.';
            } elseif ($enrollment && Str::lower($inputRow['section']) !== Str::lower((string) $enrollment->section?->name)) {
                $errors[] = 'Section name does not match the student enrollment.';
            }

            $status = $inputRow['status'];
            $obtainedMarks = null;
            if (! in_array($status, ['graded', 'absent', 'exempt'], true)) {
                $errors[] = 'Status must be graded, absent, or exempt.';
            } elseif ($status === 'graded') {
                if ($inputRow['obtained_marks'] === '') {
                    $errors[] = 'Obtained marks are required for graded students.';
                } elseif (! preg_match('/^\d+(?:\.\d{1,2})?$/', $inputRow['obtained_marks'])) {
                    $errors[] = 'Obtained marks must be a non-negative number with at most two decimal places.';
                } else {
                    $obtainedMarks = (float) $inputRow['obtained_marks'];
                    if ($obtainedMarks > (float) $examSubject->max_marks) {
                        $errors[] = "Obtained marks cannot exceed {$examSubject->max_marks}.";
                    }
                }
            } elseif ($inputRow['obtained_marks'] !== '') {
                $errors[] = 'Obtained marks must be blank when status is absent or exempt.';
            }

            $existingMark = $student ? $existingMarks->get($student->id) : null;
            $hasConflict = $existingMark !== null;
            if ($hasConflict && ! $replaceExisting) {
                $errors[] = 'Marks already exist. Select the explicit replace option to update them.';
            }

            $analyzedRows[] = [
                'row_number' => $inputRow['row_number'],
                'admission_number' => $admissionNumber,
                'uploaded_name' => $inputRow['student_name'],
                'matched_name' => $student?->full_name,
                'section_name' => $enrollment?->section?->name,
                'status' => $status,
                'obtained_marks' => $obtainedMarks,
                'existing_marks' => $existingMark?->marks_obtained,
                'student_id' => $student?->id,
                'has_conflict' => $hasConflict,
                'errors' => $errors,
            ];
        }

        return [
            'total' => count($analyzedRows),
            'valid' => count(array_filter($analyzedRows, fn (array $row): bool => $row['errors'] === [])),
            'conflicts' => count(array_filter($analyzedRows, fn (array $row): bool => $row['has_conflict'])),
            'rejected' => count(array_filter($analyzedRows, fn (array $row): bool => $row['errors'] !== [])),
            'rows' => $analyzedRows,
        ];
    }

    /**
     * @param  list<list<bool|float|int|string|null>>  $spreadsheetRows
     * @return array{created: int, updated: int, skipped: int, rejected: int, errors: list<array{row_number: int, admission_number: string, reason: string}>}
     */
    public function import(
        int $schoolId,
        ExamSubject $examSubject,
        ?int $sectionId,
        array $spreadsheetRows,
        bool $replaceExisting,
        int $actorId
    ): array {
        return DB::transaction(function () use ($schoolId, $examSubject, $sectionId, $spreadsheetRows, $replaceExisting, $actorId): array {
            $exam = Exam::query()
                ->whereKey($examSubject->exam_id)
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($exam->status === 'locked') {
                throw ValidationException::withMessages([
                    'file' => 'This exam is locked. Marks cannot be changed.',
                ]);
            }

            $lockedExamSubject = ExamSubject::query()
                ->whereKey($examSubject->id)
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->with(['exam', 'classSubject'])
                ->firstOrFail();

            $analysis = $this->analyze(
                $schoolId,
                $lockedExamSubject,
                $sectionId,
                $spreadsheetRows,
                $replaceExisting
            );

            $records = [];
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $rejected = 0;
            $errors = [];
            $now = now();

            foreach ($analysis['rows'] as $row) {
                if ($row['errors'] !== []) {
                    if ($row['has_conflict'] && ! $replaceExisting && count($row['errors']) === 1) {
                        $skipped++;
                        $errors[] = [
                            'row_number' => $row['row_number'],
                            'admission_number' => $row['admission_number'],
                            'reason' => implode(' ', $row['errors']),
                        ];

                        continue;
                    }

                    $rejected++;
                    $errors[] = [
                        'row_number' => $row['row_number'],
                        'admission_number' => $row['admission_number'],
                        'reason' => implode(' ', $row['errors']),
                    ];

                    continue;
                }

                if ($row['has_conflict']) {
                    $updated++;
                } else {
                    $created++;
                }

                $records[] = [
                    'school_id' => $schoolId,
                    'exam_subject_id' => $lockedExamSubject->id,
                    'student_id' => $row['student_id'],
                    'marks_obtained' => $row['obtained_marks'],
                    'status' => $row['status'],
                    'entered_by' => $actorId,
                    'updated_by' => $actorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($records, 500) as $batch) {
                DB::table('marks')->upsert(
                    $batch,
                    ['exam_subject_id', 'student_id'],
                    ['marks_obtained', 'status', 'updated_by', 'updated_at']
                );
            }

            AuditLogService::log('marks_bulk_imported', 'ExamSubject', $lockedExamSubject->id, [
                'exam_id' => $lockedExamSubject->exam_id,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'rejected' => $rejected,
            ], $schoolId);

            return compact('created', 'updated', 'skipped', 'rejected', 'errors');
        });
    }

    private function cellToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_bool($value) ? ($value ? '1' : '0') : trim((string) $value);
    }
}
