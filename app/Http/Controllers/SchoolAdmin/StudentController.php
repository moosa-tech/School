<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\WhatsAppCredentialDeliveryService;
use App\Support\AnyFilter;
use App\Support\SchoolAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StudentController extends Controller
{
    /**
     * Display filtered students list.
     */
    public function index(Request $request): View
    {
        $schoolId = session('current_school_id');

        $query = Student::forSchool($schoolId)
            ->with(['currentEnrollment.class', 'currentEnrollment.section', 'guardians', 'feeInvoices']);

        $search = $request->filled('search') ? '%'.trim($request->search).'%' : null;
        $hasDateFilter = $request->filled('date_from') || $request->filled('date_to');

        AnyFilter::apply($query, [
            $search ? function (Builder $filter) use ($search): void {
                $filter->where('admission_number', 'like', $search)
                    ->orWhere('first_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search)
                    ->orWhere('emergency_phone', 'like', $search)
                    ->orWhereHas('guardians', fn (Builder $guardian) => $guardian->where('name', 'like', $search)->orWhere('phone', 'like', $search));
            } : null,
            $request->filled('status') ? fn (Builder $filter) => $filter->where('status', $request->status) : null,
            $request->filled('class_id') ? function (Builder $filter) use ($request): void {
                $filter->whereHas('enrollments', function (Builder $enrollment) use ($request): void {
                    $enrollment->where('class_id', $request->class_id);
                    if ($request->filled('section_id')) {
                        $enrollment->where('section_id', $request->section_id);
                    }
                });
            } : null,
            $hasDateFilter ? function (Builder $filter) use ($request): void {
                if ($request->filled('date_from')) {
                    $filter->whereDate('admission_date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $filter->whereDate('admission_date', '<=', $request->date_to);
                }
            } : null,
        ]);

        // Whitelisted sorting
        $sortField = in_array($request->sort_by, ['admission_number', 'first_name', 'admission_date', 'created_at'], true)
            ? $request->sort_by
            : 'id';
        $sortDirection = $request->sort_dir === 'asc' ? 'asc' : 'desc';

        $students = $query->orderBy($sortField, $sortDirection)->paginate(25)->withQueryString();

        $classes = SchoolClass::forSchool($schoolId)->with('sections')->get();
        $academicYears = AcademicYear::forSchool($schoolId)->get();

        return view('school-admin.students.index', compact('students', 'classes', 'academicYears'));
    }

    /**
     * Show form for creating a new student.
     */
    public function create(): View
    {
        $schoolId = session('current_school_id');
        $classes = SchoolClass::forSchool($schoolId)->with('sections')->get();
        $academicYears = AcademicYear::forSchool($schoolId)->where('status', 'active')->get();
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();

        return view('school-admin.students.create', compact('classes', 'academicYears', 'currentYear'));
    }

    /**
     * Store student, guardian, and initial academic year enrollment within a transaction.
     */
    public function store(Request $request, WhatsAppCredentialDeliveryService $deliveryService): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'admission_number' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'admission_date' => ['required', 'date'],
            'address' => ['nullable', 'string'],
            'emergency_phone' => ['nullable', 'string', 'max:30'],
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'class_id' => ['required', SchoolAccess::existsInSchool('classes', $schoolId)],
            'section_id' => [
                'required',
                SchoolAccess::existsInSchool('sections', $schoolId)->where('class_id', $request->input('class_id')),
            ],
            'roll_number' => ['nullable', 'string', 'max:30'],
            // Guardian info
            'guardian_name' => ['required', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:100'],
            'guardian_relation' => ['required', 'string', 'max:50'],
            'whatsapp_opt_in' => ['sometimes', 'accepted'],
        ]);

        // Uniqueness check for admission_number in this school (only when manually provided)
        if (! empty($validated['admission_number'])) {
            $exists = Student::forSchool($schoolId)->where('admission_number', $validated['admission_number'])->exists();
            if ($exists) {
                return back()->withErrors(['admission_number' => 'This admission number already exists in this school.'])->withInput();
            }
        }

        $studentPassword = null;
        $student = DB::transaction(function () use ($schoolId, $validated, &$studentPassword) {
            $subscription = Subscription::with('plan')
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->latest()
                ->first();

            if ($subscription && Student::forSchool($schoolId)->count() >= $subscription->plan->student_limit) {
                throw ValidationException::withMessages([
                    'student_limit' => 'The student limit for this school subscription has been reached.',
                ]);
            }

            // 1. Auto-generate unique admission_number if not provided
            if (empty($validated['admission_number'])) {
                $prefix = 'ADM-'.$schoolId.'-';
                $lastStudent = Student::forSchool($schoolId)
                    ->where('admission_number', 'like', $prefix.'%')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();
                $lastId = $lastStudent ? $lastStudent->id : 0;
                $validated['admission_number'] = $prefix.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }

            // 2. Auto-generate unique roll_number for the class/section/academic_year
            if (empty($validated['roll_number'])) {
                $existingCount = StudentEnrollment::where('school_id', $schoolId)
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('class_id', $validated['class_id'])
                    ->where('section_id', $validated['section_id'])
                    ->lockForUpdate()
                    ->count();
                $validated['roll_number'] = str_pad($existingCount + 1, 3, '0', STR_PAD_LEFT);
            }

            // 3. Auto-create student portal user account with a unique temporary password
            $studentEmail = ! empty($validated['guardian_email'])
                ? strtolower(explode('@', $validated['guardian_email'])[0]).'.'.strtolower($validated['first_name']).'@beaconhouse.edu'
                : strtolower(str_replace(['-', ' '], '', $validated['admission_number'])).'@beaconhouse.edu';

            $user = User::where('email', $studentEmail)->first();
            if ($user && ($user->isSuperAdmin() || $user->status !== 'active')) {
                throw ValidationException::withMessages([
                    'guardian_email' => 'The generated student login belongs to an unavailable account.',
                ]);
            }

            if (! $user) {
                $studentPassword = Str::random(40);
                $user = User::create([
                    'name' => trim($validated['first_name'].' '.($validated['last_name'] ?? '')),
                    'email' => $studentEmail,
                    'password' => $studentPassword,
                    'must_change_password' => true,
                    'is_super_admin' => false,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
            }

            $membership = $user->schools()->where('schools.id', $schoolId)->first();
            if ($membership && (
                $membership->pivot->status !== 'active'
                || $membership->pivot->role !== 'student'
            )) {
                throw ValidationException::withMessages([
                    'guardian_email' => 'The generated student login already has a different or inactive role in this school.',
                ]);
            }

            if (! $membership) {
                $user->schools()->syncWithoutDetaching([
                    $schoolId => [
                        'role' => 'student',
                        'status' => 'active',
                        'joined_at' => now(),
                    ],
                ]);
            }

            $student = Student::create([
                'school_id' => $schoolId,
                'user_id' => $user->id,
                'admission_number' => $validated['admission_number'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'],
                'admission_date' => $validated['admission_date'],
                'address' => $validated['address'] ?? null,
                'emergency_phone' => $validated['emergency_phone'] ?? null,
                'status' => 'active',
            ]);

            // Create Guardian
            $guardian = Guardian::create([
                'school_id' => $schoolId,
                'name' => $validated['guardian_name'],
                'phone' => $validated['guardian_phone'] ?? null,
                'email' => $validated['guardian_email'] ?? null,
                'relationship' => $validated['guardian_relation'],
                'address' => $validated['address'] ?? null,
            ]);

            // Link Student to Guardian
            $student->guardians()->attach($guardian->id, [
                'school_id' => $schoolId,
                'is_primary' => true,
            ]);

            // Create Academic Year Enrollment with auto roll_number
            StudentEnrollment::create([
                'school_id' => $schoolId,
                'student_id' => $student->id,
                'academic_year_id' => $validated['academic_year_id'],
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'roll_number' => $validated['roll_number'],
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);

            AuditLogService::log('student_admitted', 'Student', $student->id, [
                'admission_number' => $student->admission_number,
                'name' => $student->full_name,
                'roll_number' => $validated['roll_number'],
                'login_email' => $studentEmail,
            ], $schoolId);

            return $student;
        });

        $whatsAppStatus = 'WhatsApp delivery was not requested; no message was sent.';
        if ($studentPassword && ($missing = $deliveryService->missingConfiguration()) !== []) {
            $whatsAppStatus = 'WhatsApp message not queued: missing configuration for '.implode(', ', $missing).'.';
        } elseif ($studentPassword && ! empty($validated['whatsapp_opt_in']) && filled($validated['guardian_phone'])) {
            if (! $deliveryService->isValidRecipient($validated['guardian_phone'])) {
                $whatsAppStatus = 'WhatsApp message not queued: enter the guardian number in international format, such as +15551234567.';
            } elseif ($deliveryService->queueCredentials(
                (int) $schoolId,
                (int) $student->user_id,
                $validated['guardian_phone'],
                (string) $student->user?->email,
                $studentPassword
            )) {
                $whatsAppStatus = 'WhatsApp credential message queued; delivery is not yet confirmed.';
            }
        } elseif ($studentPassword && ! empty($validated['whatsapp_opt_in'])) {
            $whatsAppStatus = 'WhatsApp message not queued: a guardian phone number is required.';
        }

        $response = redirect()->route('school-admin.students.show', $student)
            ->with('status', "Student {$student->full_name} enrolled! Roll Number: {$student->currentEnrollment?->roll_number}");

        if ($studentPassword) {
            $response->with('account_credentials', [
                'school_id' => (int) $schoolId,
                'username' => $student->user?->email,
                'encrypted_password' => Crypt::encryptString($studentPassword),
                'whatsapp_status' => $whatsAppStatus,
            ]);
        } else {
            $response->with('status', "Student {$student->full_name} enrolled with an existing login; password was not reset.");
        }

        return $response;
    }

    /**
     * Show student profile with all tabs:
     * Details, Guardians, Enrollment History, Attendance, Fee Ledger, Exams/Marks, Documents.
     */
    public function show(Student $student): View|Response
    {
        $schoolId = session('current_school_id');

        // Multi-tenant check
        if ($student->school_id !== $schoolId) {
            abort(403, 'Cross-school access is strictly prohibited.');
        }

        $student->load([
            'guardians',
            'enrollments.academicYear',
            'enrollments.class',
            'enrollments.section',
            'attendances.session',
            'feeInvoices.items',
            'feeInvoices.allocations.payment',
            'feePayments',
            'feeAdjustments',
            'marks.examSubject.exam',
            'marks.examSubject.classSubject.subject',
            'reportCards.exam',
            'documents',
        ]);

        // Attendance percentage calculation
        $totalSessions = $student->attendances->count();
        $presentCount = $student->attendances->where('status', 'present')->count();
        $lateCount = $student->attendances->where('status', 'late')->count();
        $attendancePercentage = $totalSessions > 0 ? round((($presentCount + ($lateCount * 0.5)) / $totalSessions) * 100, 1) : 0;

        return response()
            ->view('school-admin.students.show', compact('student', 'attendancePercentage'))
            ->header('Cache-Control', 'private, no-store');
    }

    /**
     * Update student status (e.g. transfer, withdraw, graduate).
     */
    public function updateStatus(Request $request, Student $student): RedirectResponse
    {
        $schoolId = session('current_school_id');
        if ($student->school_id !== $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,pending,transferred,withdrawn,graduated'],
        ]);

        $old = $student->status;
        $student->update(['status' => $validated['status']]);

        AuditLogService::log('student_status_changed', 'Student', $student->id, [
            'old' => $old,
            'new' => $validated['status'],
        ], $schoolId);

        return back()->with('status', "Student status updated to {$validated['status']}.");
    }
}
