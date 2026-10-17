<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentQrScanEvent;
use App\Services\AuditLogService;
use App\Support\AttendanceAccess;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StudentQrController extends Controller
{
    public function scanner(): View
    {
        $school = School::query()->findOrFail((int) session('current_school_id'));

        $recentEvents = $this->qrScanEventsAvailable()
            ? StudentQrScanEvent::forSchool((int) $school->id)
                ->with(['student', 'session'])
                ->whereBetween('scanned_at', [
                    now($school->timezone ?: 'Asia/Karachi')->startOfDay()->utc(),
                    now($school->timezone ?: 'Asia/Karachi')->endOfDay()->utc(),
                ])
                ->latest('scanned_at')
                ->limit(10)
                ->get()
            : collect();

        return view('school-admin.attendance.qr-scanner', compact('recentEvents', 'school'));
    }

    public function manual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['required', 'string', 'max:100'],
        ]);
        $eligibleStudents = Student::forSchool((int) session('current_school_id'))
            ->where('status', 'active')
            ->whereHas('currentEnrollment', fn ($query) => $query->where('status', 'enrolled'));
        $exactAdmissionMatch = (clone $eligibleStudents)
            ->where('admission_number', trim($validated['search']))
            ->first();

        if ($exactAdmissionMatch) {
            $student = $exactAdmissionMatch;
        } else {
            $students = $eligibleStudents
                ->where(function ($query) use ($validated): void {
                    $search = '%'.trim($validated['search']).'%';
                    $query->where('admission_number', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search);
                })
                ->limit(2)
                ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'No active enrolled student matched that search.',
                ], 404);
            }

            if ($students->count() > 1) {
                return response()->json([
                    'status' => 'ambiguous',
                    'message' => 'More than one student matched. Search by the exact admission number.',
                ], 409);
            }

            $student = $students->first();
        }

        if (! $student->hasActiveQrToken()) {
            $student->issueQrToken();
        }

        return $this->recordToken($request, $student->qr_token);
    }

    public function record(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'],
        ]);

        return $this->recordToken($request, $validated['token']);
    }

    private function recordToken(Request $request, string $token): JsonResponse
    {
        if (! $this->qrScanEventsAvailable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The QR attendance event table has not been installed in this database yet. Please apply the QR migration before using scanner attendance.',
            ], 503);
        }

        $schoolId = (int) session('current_school_id');
        $user = $request->user();
        $student = Student::forSchool($schoolId)
            ->with(['currentEnrollment.class', 'currentEnrollment.section'])
            ->where('qr_token_hash', hash('sha256', $token))
            ->whereNull('qr_token_revoked_at')
            ->where('status', 'active')
            ->whereHas('currentEnrollment', fn ($query) => $query->where('status', 'enrolled'))
            ->first();

        if (! $student) {
            $tokenBelongsToAnotherSchool = Student::withoutGlobalScope('school')
                ->where('qr_token_hash', hash('sha256', $token))
                ->whereNull('qr_token_revoked_at')
                ->where('school_id', '!=', $schoolId)
                ->exists();

            if ($tokenBelongsToAnotherSchool) {
                return response()->json([
                    'status' => 'wrong_school',
                    'message' => 'This QR code belongs to a different school. No attendance was recorded.',
                ], 422);
            }

            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid or revoked QR code.',
            ], 422);
        }

        if (! hash_equals((string) $student->qr_token, $token)) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid or revoked QR code.',
            ], 422);
        }

        $enrollment = $student->currentEnrollment;
        if (! $enrollment
            || (int) $enrollment->school_id !== $schoolId
            || $enrollment->status !== 'enrolled'
            || ! $enrollment->academicYear
            || (int) $enrollment->academicYear->school_id !== $schoolId
            || $enrollment->academicYear->status !== 'active'
            || ! $enrollment->class
            || (int) $enrollment->class->school_id !== $schoolId
            || $enrollment->class->status !== 'active'
            || ! $enrollment->section
            || (int) $enrollment->section->school_id !== $schoolId
            || $enrollment->section->status !== 'active'
            || (int) $enrollment->section->class_id !== (int) $enrollment->class_id) {
            return response()->json([
                'status' => 'ineligible',
                'message' => 'The student has no complete current enrollment.',
            ], 422);
        }

        abort_unless(
            AttendanceAccess::canManageClass($user, $schoolId, (int) $enrollment->academic_year_id, (int) $enrollment->class_id),
            403,
            'You may only record attendance for an assigned class.'
        );

        $school = School::query()->findOrFail($schoolId);
        $now = now('UTC');
        $attendanceDate = $now->copy()->timezone($school->timezone ?: 'Asia/Karachi')->toDateString();
        $academicYear = $enrollment->academicYear;

        if ($attendanceDate < $academicYear->starts_on->toDateString() || $attendanceDate > $academicYear->ends_on->toDateString()) {
            return response()->json([
                'status' => 'ineligible',
                'message' => 'Today is outside the student’s current academic year.',
            ], 422);
        }

        $existingSession = AttendanceSession::forSchool($schoolId)
            ->where('academic_year_id', $academicYear->id)
            ->where('class_id', $enrollment->class_id)
            ->where('section_id', $enrollment->section_id)
            ->whereDate('attendance_date', $attendanceDate)
            ->first();

        if ($existingSession) {
            $existingEvent = StudentQrScanEvent::forSchool($schoolId)
                ->where('attendance_session_id', $existingSession->id)
                ->where('student_id', $student->id)
                ->where('event_type', 'entry')
                ->first();

            if ($existingEvent) {
                $existingAttendanceStatus = $existingEvent->processing_status === 'manual_attendance_exists'
                    ? StudentAttendance::forSchool($schoolId)
                        ->where('attendance_session_id', $existingSession->id)
                        ->where('student_id', $student->id)
                        ->value('status')
                    : null;

                return response()->json(array_merge($this->studentDetails($student), [
                    'status' => $existingEvent->processing_status === 'pending_review' ? 'review_required' : 'duplicate',
                    'message' => $existingEvent->processing_status === 'pending_review'
                        ? 'Entry is already recorded and is awaiting attendance review.'
                        : 'Attendance is already recorded for today; the existing entry was not changed.',
                    'attendance' => $existingEvent->processing_status === 'pending_review'
                        ? 'Awaiting review'
                        : ($existingAttendanceStatus ? ucfirst($existingAttendanceStatus) : 'Present'),
                ]), 200);
            }

            $existingAttendance = StudentAttendance::forSchool($schoolId)
                ->where('attendance_session_id', $existingSession->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingAttendance) {
                DB::transaction(function () use ($schoolId, $existingSession, $student, $user, $now): void {
                    $session = AttendanceSession::forSchool($schoolId)
                        ->whereKey($existingSession->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $hasEvent = StudentQrScanEvent::forSchool($schoolId)
                        ->where('attendance_session_id', $session->id)
                        ->where('student_id', $student->id)
                        ->where('event_type', 'entry')
                        ->exists();

                    if (! $hasEvent) {
                        StudentQrScanEvent::create([
                            'school_id' => $schoolId,
                            'student_id' => $student->id,
                            'attendance_session_id' => $session->id,
                            'scanned_by' => $user->id,
                            'scanned_at' => $now,
                            'event_type' => 'entry',
                            'processing_status' => 'manual_attendance_exists',
                        ]);
                    }
                });

                return response()->json(array_merge($this->studentDetails($student), [
                    'status' => 'duplicate',
                    'message' => 'Attendance is already recorded for today; the existing entry was not changed.',
                    'attendance' => ucfirst($existingAttendance->status),
                ]), 200);
            }
        }

        $attendancePolicy = config('attendance.qr_scan_policy', 'mark_present');
        if (! in_array($attendancePolicy, ['mark_present', 'review'], true)) {
            throw new \RuntimeException('The configured QR attendance policy is not supported.');
        }

        $result = DB::transaction(function () use ($schoolId, $student, $enrollment, $academicYear, $attendanceDate, $user, $now, $attendancePolicy): array {
            $session = AttendanceSession::forSchool($schoolId)
                ->where('academic_year_id', $academicYear->id)
                ->where('class_id', $enrollment->class_id)
                ->where('section_id', $enrollment->section_id)
                ->whereDate('attendance_date', $attendanceDate)
                ->lockForUpdate()
                ->first();

            if (! $session) {
                try {
                    $session = AttendanceSession::create([
                        'school_id' => $schoolId,
                        'academic_year_id' => $academicYear->id,
                        'class_id' => $enrollment->class_id,
                        'section_id' => $enrollment->section_id,
                        'attendance_date' => $attendanceDate,
                        'taken_by' => $user->id,
                        'status' => 'draft',
                    ]);
                } catch (QueryException $exception) {
                    $session = AttendanceSession::forSchool($schoolId)
                        ->where('academic_year_id', $academicYear->id)
                        ->where('class_id', $enrollment->class_id)
                        ->where('section_id', $enrollment->section_id)
                        ->whereDate('attendance_date', $attendanceDate)
                        ->lockForUpdate()
                        ->first();

                    if (! $session) {
                        throw $exception;
                    }
                }
            }

            $existingEvent = StudentQrScanEvent::forSchool($schoolId)
                ->where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->where('event_type', 'entry')
                ->first();

            if ($existingEvent) {
                return ['event' => $existingEvent, 'attendance' => null, 'duplicate' => true];
            }

            $attendance = StudentAttendance::forSchool($schoolId)
                ->where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();

            if ($attendance) {
                $event = StudentQrScanEvent::create([
                    'school_id' => $schoolId,
                    'student_id' => $student->id,
                    'attendance_session_id' => $session->id,
                    'scanned_by' => $user->id,
                    'scanned_at' => $now,
                    'event_type' => 'entry',
                    'processing_status' => 'manual_attendance_exists',
                ]);

                return ['event' => $event, 'attendance' => $attendance, 'duplicate' => true];
            }

            if ($attendancePolicy === 'review') {
                $event = StudentQrScanEvent::create([
                    'school_id' => $schoolId,
                    'student_id' => $student->id,
                    'attendance_session_id' => $session->id,
                    'scanned_by' => $user->id,
                    'scanned_at' => $now,
                    'event_type' => 'entry',
                    'processing_status' => 'pending_review',
                ]);

                return ['event' => $event, 'attendance' => null, 'duplicate' => false];
            }

            try {
                $attendance = StudentAttendance::create([
                    'school_id' => $schoolId,
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => 'present',
                    'marked_by' => $user->id,
                    'marked_at' => $now,
                    'remarks' => 'QR scan',
                ]);
            } catch (QueryException $exception) {
                $attendance = StudentAttendance::forSchool($schoolId)
                    ->where('attendance_session_id', $session->id)
                    ->where('student_id', $student->id)
                    ->first();

                if (! $attendance) {
                    throw $exception;
                }

                return ['event' => null, 'attendance' => $attendance, 'duplicate' => true];
            }

            $event = StudentQrScanEvent::create([
                'school_id' => $schoolId,
                'student_id' => $student->id,
                'attendance_session_id' => $session->id,
                'scanned_by' => $user->id,
                'scanned_at' => $now,
                'event_type' => 'entry',
                'processing_status' => 'attendance_recorded',
            ]);

            AuditLogService::log('attendance_marked', 'AttendanceSession', $session->id, [
                'date' => $attendanceDate,
                'class_id' => $enrollment->class_id,
                'status' => $session->status,
                'source' => 'qr_scan',
            ], $schoolId);

            return ['event' => $event, 'attendance' => $attendance, 'duplicate' => false];
        });

        if ($attendancePolicy === 'review') {
            return response()->json(array_merge($this->studentDetails($student), [
                'status' => $result['duplicate'] ? 'duplicate' : 'review_required',
                'message' => $result['duplicate']
                    ? 'Entry is already recorded and is awaiting attendance review.'
                    : 'Gate entry recorded and is awaiting attendance review.',
                'attendance' => $result['duplicate'] ? 'Awaiting review' : null,
                'recorded_at' => $result['event']?->scanned_at
                    ->copy()
                    ->timezone($school->timezone ?: 'Asia/Karachi')
                    ->format('g:i:s A'),
            ]), 200);
        }

        return response()->json(array_merge($this->studentDetails($student), [
            'status' => $result['duplicate'] ? 'duplicate' : 'success',
            'message' => $result['duplicate']
                ? 'Attendance is already recorded for today; the existing entry was not changed.'
                : 'Present recorded successfully.',
            'attendance' => ucfirst($result['attendance']->status),
            'recorded_at' => $result['attendance']->marked_at->copy()->timezone($school->timezone ?: 'Asia/Karachi')->format('g:i:s A'),
        ]), 200);
    }

    public function card(Student $student): View
    {
        $schoolId = (int) session('current_school_id');
        abort_unless((int) $student->school_id === $schoolId, 404);

        if (! $student->hasActiveQrToken()) {
            $student->issueQrToken();
        }

        $student->load('currentEnrollment.class', 'currentEnrollment.section');
        $school = School::query()->findOrFail($schoolId);
        $qrCode = new QrCode(
            data: $student->qr_token,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 240,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(15, 23, 42),
            backgroundColor: new Color(255, 255, 255),
        );
        $qrSvg = (new SvgWriter)->write($qrCode)->getString();

        return view('school-admin.students.qr-card', compact('student', 'school', 'qrSvg'));
    }

    public function rotateToken(Student $student): RedirectResponse
    {
        $schoolId = (int) session('current_school_id');
        abort_unless((int) $student->school_id === $schoolId, 404);

        $student->issueQrToken();

        AuditLogService::log('student_qr_token_rotated', 'Student', $student->id, [], $schoolId);

        return redirect()->route('school-admin.students.qr-card', $student)
            ->with('status', 'Student QR code replaced. Any old printed cards are now invalid.');
    }

    public function revokeToken(Student $student): RedirectResponse
    {
        $schoolId = (int) session('current_school_id');
        abort_unless((int) $student->school_id === $schoolId, 404);

        $student->revokeQrToken();
        AuditLogService::log('student_qr_token_revoked', 'Student', $student->id, [], $schoolId);

        return redirect()->route('school-admin.students.show', $student)
            ->with('status', 'Student QR code revoked.');
    }

    private function qrScanEventsAvailable(): bool
    {
        return Schema::hasTable('student_qr_scan_events');
    }

    private function studentDetails(Student $student): array
    {
        return [
            'name' => $student->full_name,
            'admission_number' => $student->admission_number,
            'class' => $student->currentEnrollment?->class?->name,
            'section' => $student->currentEnrollment?->section?->name,
        ];
    }
}
