<?php

namespace Tests\Feature;

use App\Models\FeeInvoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_only_the_logged_in_students_record(): void
    {
        $portal = $this->createPortalUser('student', 'student@example.test');
        $academicYearId = $this->createAcademicYear($portal['school_id']);
        $classId = $this->createClass($portal['school_id'], 'Year 7');
        $sectionId = $this->createSection($portal['school_id'], $classId, 'Blue');
        $studentId = $this->createStudent($portal['school_id'], $portal['user']->id, 'ST-001', 'Amina Student', $academicYearId, $classId, $sectionId);
        $otherStudentId = $this->createStudent($portal['school_id'], null, 'ST-002', 'Private Student', $academicYearId, $classId, $sectionId);

        $attendanceSessionId = DB::table('attendance_sessions')->insertGetId([
            'school_id' => $portal['school_id'],
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'attendance_date' => '2026-10-12',
            'status' => 'finalized',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach ([
            [$studentId, 'OWN_ATTENDANCE_MARKER'],
            [$otherStudentId, 'PRIVATE_ATTENDANCE_MARKER'],
        ] as [$recordStudentId, $remarks]) {
            DB::table('student_attendance')->insert([
                'school_id' => $portal['school_id'],
                'attendance_session_id' => $attendanceSessionId,
                'student_id' => $recordStudentId,
                'status' => 'present',
                'remarks' => $remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->createFeeInvoice($portal['school_id'], $academicYearId, $studentId, 'OWN-INVOICE');
        $this->createFeeInvoice($portal['school_id'], $academicYearId, $otherStudentId, 'PRIVATE-INVOICE');

        $subjectId = DB::table('subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'name' => 'Portal Mathematics',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $publishedExamSubjectId = $this->createExamSubject(
            $portal['school_id'],
            $academicYearId,
            $classSubjectId,
            'Published Exam',
            'published'
        );
        $draftExamSubjectId = $this->createExamSubject(
            $portal['school_id'],
            $academicYearId,
            $classSubjectId,
            'Draft Exam',
            'draft'
        );
        foreach ([[$studentId, $publishedExamSubjectId], [$otherStudentId, $publishedExamSubjectId], [$studentId, $draftExamSubjectId]] as [$recordStudentId, $examSubjectId]) {
            DB::table('marks')->insert([
                'school_id' => $portal['school_id'],
                'exam_subject_id' => $examSubjectId,
                'student_id' => $recordStudentId,
                'marks_obtained' => 85,
                'status' => 'graded',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('student.dashboard', ['student_id' => $otherStudentId]))
            ->assertOk()
            ->assertSee('Student Dashboard')
            ->assertSee('Amina Student')
            ->assertSee('Year 7')
            ->assertSee('OWN_ATTENDANCE_MARKER')
            ->assertSee('OWN-INVOICE')
            ->assertSee('Published Exam')
            ->assertDontSee('Private Student')
            ->assertDontSee('PRIVATE_ATTENDANCE_MARKER')
            ->assertDontSee('PRIVATE-INVOICE')
            ->assertDontSee('Draft Exam')
            ->assertDontSee('Students & Admissions');
    }

    public function test_unlinked_student_account_does_not_fall_back_to_another_student(): void
    {
        $portal = $this->createPortalUser('student', 'unlinked-student@example.test');
        $academicYearId = $this->createAcademicYear($portal['school_id']);
        $classId = $this->createClass($portal['school_id'], 'Year 8');
        $sectionId = $this->createSection($portal['school_id'], $classId, 'Green');
        $this->createStudent($portal['school_id'], null, 'ST-PRIVATE', 'Not This User', $academicYearId, $classId, $sectionId);

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('not linked to a student record in the active school')
            ->assertDontSee('Not This User');
    }

    public function test_student_cannot_switch_to_another_school_or_open_another_student_profile(): void
    {
        $portal = $this->createPortalUser('student', 'cross-school-student@example.test');
        $ownStudentId = $this->createStudent($portal['school_id'], $portal['user']->id, 'OWN-001', 'Own Student');
        $otherStudentId = $this->createStudent($portal['school_id'], null, 'SAME-SCHOOL-002', 'Same School Private Student');
        $otherSchoolId = $this->createSchool('Other School');
        $otherSchoolStudentId = $this->createStudent($otherSchoolId, null, 'OTHER-001', 'Other School Private Student');

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('student.dashboard', ['student_id' => $otherStudentId]))
            ->assertOk()
            ->assertSee('Own Student')
            ->assertDontSee('Same School Private Student')
            ->assertDontSee('Other School Private Student');

        $this->get(route('student.dashboard', [
            'student_id' => $otherSchoolStudentId,
            'school_id' => $otherSchoolId,
        ]))->assertNotFound();

        $this->get(route('school-admin.students.show', $otherStudentId))
            ->assertForbidden();

        $this->assertNotSame($ownStudentId, $otherStudentId);
    }

    public function test_student_profile_attendance_fees_and_results_are_scoped_to_their_own_record(): void
    {
        $portal = $this->createPortalUser('student', 'self-service@example.test');
        $academicYearId = $this->createAcademicYear($portal['school_id']);
        $classId = $this->createClass($portal['school_id'], 'Year 9');
        $sectionId = $this->createSection($portal['school_id'], $classId, 'Gold');
        $studentId = $this->createStudent(
            $portal['school_id'],
            $portal['user']->id,
            'SELF-001',
            'Self Service Student',
            $academicYearId,
            $classId,
            $sectionId
        );
        $privateStudentId = $this->createStudent(
            $portal['school_id'],
            null,
            'PRIVATE-001',
            'Other Private Student',
            $academicYearId,
            $classId,
            $sectionId
        );

        $guardianId = DB::table('guardians')->insertGetId([
            'school_id' => $portal['school_id'],
            'name' => 'Self Service Guardian',
            'phone' => '+15551234567',
            'relationship' => 'Parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_guardian')->insert([
            'school_id' => $portal['school_id'],
            'student_id' => $studentId,
            'guardian_id' => $guardianId,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $attendanceSessionId = DB::table('attendance_sessions')->insertGetId([
            'school_id' => $portal['school_id'],
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'attendance_date' => now()->startOfMonth()->toDateString(),
            'status' => 'finalized',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach ([
            [$studentId, 'OWN_MONTHLY_ATTENDANCE'],
            [$privateStudentId, 'PRIVATE_MONTHLY_ATTENDANCE'],
        ] as [$recordStudentId, $remarks]) {
            DB::table('student_attendance')->insert([
                'school_id' => $portal['school_id'],
                'attendance_session_id' => $attendanceSessionId,
                'student_id' => $recordStudentId,
                'status' => 'present',
                'remarks' => $remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $ownInvoiceId = $this->createFeeInvoice(
            $portal['school_id'],
            $academicYearId,
            $studentId,
            'OWN-PORTAL-INVOICE'
        );
        $this->createFeeInvoice($portal['school_id'], $academicYearId, $privateStudentId, 'PRIVATE-PORTAL-INVOICE');
        DB::table('fee_invoices')->where('id', $ownInvoiceId)->update([
            'paid_amount' => 40,
            'status' => 'partial',
        ]);
        $ownPaymentId = DB::table('fee_payments')->insertGetId([
            'school_id' => $portal['school_id'],
            'student_id' => $studentId,
            'receipt_number' => 'OWN-PORTAL-RECEIPT',
            'payment_date' => now()->toDateString(),
            'amount' => 40,
            'method' => 'cash',
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('fee_payment_allocations')->insert([
            'school_id' => $portal['school_id'],
            'fee_payment_id' => $ownPaymentId,
            'fee_invoice_id' => $ownInvoiceId,
            'amount_applied' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subjectId = DB::table('subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'name' => 'Portal Science',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $publishedExamSubjectId = $this->createExamSubject(
            $portal['school_id'],
            $academicYearId,
            $classSubjectId,
            'Own Published Result',
            'published'
        );
        $privateExamSubjectId = $this->createExamSubject(
            $portal['school_id'],
            $academicYearId,
            $classSubjectId,
            'Private Published Result',
            'published'
        );
        $draftExamSubjectId = $this->createExamSubject(
            $portal['school_id'],
            $academicYearId,
            $classSubjectId,
            'Own Draft Result',
            'draft'
        );
        foreach ([
            [$studentId, $publishedExamSubjectId],
            [$studentId, $draftExamSubjectId],
            [$privateStudentId, $privateExamSubjectId],
        ] as [$recordStudentId, $examSubjectId]) {
            DB::table('marks')->insert([
                'school_id' => $portal['school_id'],
                'exam_subject_id' => $examSubjectId,
                'student_id' => $recordStudentId,
                'marks_obtained' => 87,
                'status' => 'graded',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('student.profile', ['student_id' => $privateStudentId]))
            ->assertOk()
            ->assertSee('Self Service Student')
            ->assertSee('Year 9')
            ->assertSee('Self Service Guardian')
            ->assertDontSee('Other Private Student');

        $this->get(route('student.attendance', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertSee('OWN_MONTHLY_ATTENDANCE')
            ->assertDontSee('PRIVATE_MONTHLY_ATTENDANCE');

        $this->get(route('student.fees'))
            ->assertOk()
            ->assertSee('OWN-PORTAL-INVOICE')
            ->assertSee('partial')
            ->assertSee('60.00')
            ->assertSee('OWN-PORTAL-RECEIPT')
            ->assertDontSee('PRIVATE-PORTAL-INVOICE');
        $this->get(route('student.fees.receipt', $ownPaymentId))
            ->assertOk()
            ->assertSee('OWN-PORTAL-RECEIPT')
            ->assertSee('Self Service Student');

        $this->get(route('student.results'))
            ->assertOk()
            ->assertSee('Own Published Result')
            ->assertDontSee('Own Draft Result')
            ->assertDontSee('Private Published Result');
    }

    public function test_student_cannot_view_another_students_fee_receipt(): void
    {
        $portal = $this->createPortalUser('student', 'receipt-owner@example.test');
        $academicYearId = $this->createAcademicYear($portal['school_id']);
        $ownStudentId = $this->createStudent($portal['school_id'], $portal['user']->id, 'RECEIPT-OWN', 'Receipt Owner');
        $otherStudentId = $this->createStudent($portal['school_id'], null, 'RECEIPT-OTHER', 'Receipt Other');
        $otherInvoiceId = $this->createFeeInvoice($portal['school_id'], $academicYearId, $otherStudentId, 'OTHER-RECEIPT-INVOICE');
        $paymentId = DB::table('fee_payments')->insertGetId([
            'school_id' => $portal['school_id'],
            'student_id' => $otherStudentId,
            'receipt_number' => 'PRIVATE-RECEIPT',
            'payment_date' => now()->toDateString(),
            'amount' => 100,
            'method' => 'cash',
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('fee_payment_allocations')->insert([
            'school_id' => $portal['school_id'],
            'fee_payment_id' => $paymentId,
            'fee_invoice_id' => $otherInvoiceId,
            'amount_applied' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('student.fees.receipt', $paymentId))
            ->assertNotFound();

        $this->assertNotSame($ownStudentId, $otherStudentId);
    }

    public function test_student_self_service_pages_do_not_include_records_from_another_school(): void
    {
        $portal = $this->createPortalUser('student', 'other-school-self-service@example.test');
        $ownStudentId = $this->createStudent($portal['school_id'], $portal['user']->id, 'TENANT-OWN', 'Tenant Own Student');
        $otherSchoolId = $this->createSchool('Other Student Portal School');
        $otherYearId = $this->createAcademicYear($otherSchoolId);
        $otherClassId = $this->createClass($otherSchoolId, 'Private Year');
        $otherSectionId = $this->createSection($otherSchoolId, $otherClassId, 'Private Section');
        $otherStudentId = $this->createStudent(
            $otherSchoolId,
            null,
            'TENANT-PRIVATE',
            'Foreign School Student',
            $otherYearId,
            $otherClassId,
            $otherSectionId
        );
        $otherSessionId = DB::table('attendance_sessions')->insertGetId([
            'school_id' => $otherSchoolId,
            'academic_year_id' => $otherYearId,
            'class_id' => $otherClassId,
            'section_id' => $otherSectionId,
            'attendance_date' => now()->toDateString(),
            'status' => 'finalized',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_attendance')->insert([
            'school_id' => $otherSchoolId,
            'attendance_session_id' => $otherSessionId,
            'student_id' => $otherStudentId,
            'status' => 'present',
            'remarks' => 'FOREIGN_SCHOOL_ATTENDANCE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createFeeInvoice($otherSchoolId, $otherYearId, $otherStudentId, 'FOREIGN-SCHOOL-INVOICE');

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('student.profile'))
            ->assertOk()
            ->assertSee('Tenant Own Student')
            ->assertDontSee('Foreign School Student');
        $this->get(route('student.attendance', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertDontSee('FOREIGN_SCHOOL_ATTENDANCE');
        $this->get(route('student.fees'))
            ->assertOk()
            ->assertDontSee('FOREIGN-SCHOOL-INVOICE');
        $this->assertNotSame($ownStudentId, $otherStudentId);
    }

    public function test_parent_cannot_open_student_self_service_routes(): void
    {
        $portal = $this->createPortalUser('parent', 'parent-outside-student@example.test');

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('student.fees'))
            ->assertForbidden();
    }

    public function test_student_cannot_open_parent_dashboard_even_with_a_linked_guardian_record(): void
    {
        $portal = $this->createPortalUser('student', 'student-with-guardian@example.test');
        $studentId = $this->createStudent($portal['school_id'], $portal['user']->id, 'ST-GUARDIAN', 'Student Record');
        $guardianId = DB::table('guardians')->insertGetId([
            'school_id' => $portal['school_id'],
            'user_id' => $portal['user']->id,
            'name' => 'Student Guardian',
            'relationship' => 'Parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_guardian')->insert([
            'school_id' => $portal['school_id'],
            'student_id' => $studentId,
            'guardian_id' => $guardianId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('parent.dashboard'))
            ->assertForbidden();
    }

    public function test_student_cannot_open_staff_attendance_fee_result_or_receipt_routes(): void
    {
        $portal = $this->createPortalUser('student', 'student-staff-route@example.test');
        $academicYearId = $this->createAcademicYear($portal['school_id']);
        $classId = $this->createClass($portal['school_id'], 'Protected Class');
        $studentId = $this->createStudent($portal['school_id'], null, 'PROTECTED-STUDENT', 'Protected Student');
        $invoiceId = $this->createFeeInvoice($portal['school_id'], $academicYearId, $studentId, 'PROTECTED-INVOICE');
        $paymentId = DB::table('fee_payments')->insertGetId([
            'school_id' => $portal['school_id'],
            'student_id' => $studentId,
            'receipt_number' => 'PROTECTED-RECEIPT',
            'payment_date' => '2026-10-12',
            'amount' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $subjectId = DB::table('subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'name' => 'Protected Subject',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $examSubjectId = $this->createExamSubject(
            $portal['school_id'],
            $academicYearId,
            $classSubjectId,
            'Protected Exam',
            'published'
        );
        $examId = DB::table('exam_subjects')->where('id', $examSubjectId)->value('exam_id');

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('school-admin.students.show', $studentId))
            ->assertForbidden();

        $this->get(route('school-admin.attendance.register'))
            ->assertForbidden();

        $this->get(route('school-admin.fees.invoices.show', $invoiceId))
            ->assertForbidden();

        $this->get(route('school-admin.fees.receipt', $paymentId))
            ->assertForbidden();

        $this->get(route('school-admin.exams.report-card', [$examId, $studentId]))
            ->assertForbidden();
    }

    public function test_parent_cannot_switch_to_another_school_to_view_children(): void
    {
        $portal = $this->createPortalUser('parent', 'cross-school-parent@example.test');
        $otherSchoolId = $this->createSchool('Parent Other School');

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('parent.dashboard', ['school_id' => $otherSchoolId]))
            ->assertNotFound();
    }

    public function test_student_record_policy_requires_an_active_school_membership_and_ownership(): void
    {
        $portal = $this->createPortalUser('student', 'student-policy@example.test');
        $ownStudent = Student::query()->withoutGlobalScope('school')->find(
            $this->createStudent($portal['school_id'], $portal['user']->id, 'POLICY-OWN', 'Policy Owner')
        );
        $sameSchoolStudent = Student::query()->withoutGlobalScope('school')->find(
            $this->createStudent($portal['school_id'], null, 'POLICY-OTHER', 'Policy Other')
        );
        $otherSchoolId = $this->createSchool('Policy Other School');
        $otherSchoolStudent = Student::query()->withoutGlobalScope('school')->find(
            $this->createStudent($otherSchoolId, $portal['user']->id, 'POLICY-FOREIGN', 'Policy Foreign')
        );
        session(['current_school_id' => $portal['school_id']]);

        $this->assertTrue(Gate::forUser($portal['user'])->allows('view', $ownStudent));
        $this->assertFalse(Gate::forUser($portal['user'])->allows('view', $sameSchoolStudent));
        $this->assertFalse(Gate::forUser($portal['user'])->allows('view', $otherSchoolStudent));

        DB::table('school_user')
            ->where('school_id', $portal['school_id'])
            ->where('user_id', $portal['user']->id)
            ->update(['status' => 'inactive']);

        $this->assertFalse(Gate::forUser($portal['user'])->allows('view', $ownStudent));
    }

    public function test_student_fee_invoice_policy_allows_only_their_own_active_school_invoice(): void
    {
        $portal = $this->createPortalUser('student', 'student-invoice-policy@example.test');
        $academicYearId = $this->createAcademicYear($portal['school_id']);
        $ownStudentId = $this->createStudent($portal['school_id'], $portal['user']->id, 'INVOICE-OWN', 'Invoice Owner');
        $otherStudentId = $this->createStudent($portal['school_id'], null, 'INVOICE-OTHER', 'Invoice Other');
        $ownInvoiceId = $this->createFeeInvoice($portal['school_id'], $academicYearId, $ownStudentId, 'POLICY-OWN-INVOICE');
        $otherInvoiceId = $this->createFeeInvoice($portal['school_id'], $academicYearId, $otherStudentId, 'POLICY-OTHER-INVOICE');
        $otherSchoolId = $this->createSchool('Invoice Policy Other School');
        $otherSchoolYearId = $this->createAcademicYear($otherSchoolId);
        $otherSchoolStudentId = $this->createStudent($otherSchoolId, $portal['user']->id, 'INVOICE-FOREIGN', 'Invoice Foreign');
        $otherSchoolInvoiceId = $this->createFeeInvoice(
            $otherSchoolId,
            $otherSchoolYearId,
            $otherSchoolStudentId,
            'POLICY-FOREIGN-INVOICE'
        );
        session(['current_school_id' => $portal['school_id']]);

        $this->assertTrue(Gate::forUser($portal['user'])->allows(
            'view',
            FeeInvoice::query()->withoutGlobalScope('school')->find($ownInvoiceId)
        ));
        $this->assertFalse(Gate::forUser($portal['user'])->allows(
            'view',
            FeeInvoice::query()->withoutGlobalScope('school')->find($otherInvoiceId)
        ));
        $this->assertFalse(Gate::forUser($portal['user'])->allows(
            'view',
            FeeInvoice::query()->withoutGlobalScope('school')->find($otherSchoolInvoiceId)
        ));
    }

    public function test_teacher_dashboard_displays_only_current_school_assignments(): void
    {
        $portal = $this->createPortalUser('teacher', 'teacher@example.test');
        $academicYearId = $this->createAcademicYear($portal['school_id']);
        $classId = $this->createClass($portal['school_id'], 'Year 9');
        $subjectId = DB::table('subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'name' => 'Mathematics',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $employeeId = DB::table('employees')->insertGetId([
            'school_id' => $portal['school_id'],
            'user_id' => $portal['user']->id,
            'employee_number' => 'T-001',
            'name' => 'Teacher One',
            'job_title' => 'Teacher',
            'role' => 'teacher',
            'salary' => 0,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'school_id' => $portal['school_id'],
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'teacher_id' => $portal['user']->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('teacher_assignments')->insert([
            'school_id' => $portal['school_id'],
            'employee_id' => $employeeId,
            'class_subject_id' => $classSubjectId,
            'academic_year_id' => $academicYearId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Teacher Dashboard')
            ->assertSee('Mathematics')
            ->assertSee('Year 9')
            ->assertDontSee('Students & Admissions');
    }

    public function test_parent_dashboard_shows_only_linked_children(): void
    {
        $portal = $this->createPortalUser('parent', 'parent@example.test');
        $guardianId = DB::table('guardians')->insertGetId([
            'school_id' => $portal['school_id'],
            'user_id' => $portal['user']->id,
            'name' => 'Parent One',
            'email' => 'parent@example.test',
            'relationship' => 'Parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $childId = $this->createStudent($portal['school_id'], null, 'CH-001', 'Linked Child');
        $privateChildId = $this->createStudent($portal['school_id'], null, 'CH-002', 'Unlinked Child');
        $wrongTenantPivotSchoolId = $this->createSchool('Wrong Pivot School');
        $wrongTenantPivotChildId = $this->createStudent(
            $portal['school_id'],
            null,
            'CH-003',
            'Wrong Tenant Pivot Child'
        );
        DB::table('student_guardian')->insert([
            'school_id' => $portal['school_id'],
            'student_id' => $childId,
            'guardian_id' => $guardianId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_guardian')->insert([
            'school_id' => $wrongTenantPivotSchoolId,
            'student_id' => $wrongTenantPivotChildId,
            'guardian_id' => $guardianId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($portal['user'])
            ->withSession(['current_school_id' => $portal['school_id']])
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('Parent Dashboard')
            ->assertSee('Linked Child')
            ->assertDontSee('Unlinked Child')
            ->assertDontSee('Wrong Tenant Pivot Child');
    }

    public function test_student_and_teacher_cannot_open_school_admin_dashboard(): void
    {
        foreach (['student', 'teacher'] as $role) {
            $portal = $this->createPortalUser($role, $role.'-blocked@example.test');

            $this->actingAs($portal['user'])
                ->withSession(['current_school_id' => $portal['school_id']])
                ->get(route('school-admin.dashboard'))
                ->assertForbidden();
        }
    }

    /**
     * @return array{user: User, school_id: int}
     */
    private function createPortalUser(string $role, string $email): array
    {
        $now = now();
        $user = User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => Hash::make('portal-password'),
            'status' => 'active',
        ]);
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Portal School',
            'slug' => str_replace(['@', '.'], '-', $email),
            'status' => 'active',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('school_user')->insert([
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'role' => $role,
            'status' => 'active',
            'joined_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return ['user' => $user, 'school_id' => $schoolId];
    }

    private function createSchool(string $name): int
    {
        return DB::table('schools')->insertGetId([
            'name' => $name,
            'slug' => str_replace(' ', '-', strtolower($name)),
            'status' => 'active',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createFeeInvoice(int $schoolId, int $academicYearId, int $studentId, string $invoiceNumber): int
    {
        return DB::table('fee_invoices')->insertGetId([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'academic_year_id' => $academicYearId,
            'invoice_number' => $invoiceNumber,
            'issue_date' => '2026-10-01',
            'due_date' => '2026-10-30',
            'total_amount' => 100,
            'paid_amount' => 0,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createExamSubject(
        int $schoolId,
        int $academicYearId,
        int $classSubjectId,
        string $examName,
        string $status
    ): int {
        $examId = DB::table('exams')->insertGetId([
            'school_id' => $schoolId,
            'academic_year_id' => $academicYearId,
            'name' => $examName,
            'exam_type' => 'mid_term',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-02',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('exam_subjects')->insertGetId([
            'school_id' => $schoolId,
            'exam_id' => $examId,
            'class_subject_id' => $classSubjectId,
            'max_marks' => 100,
            'passing_marks' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAcademicYear(int $schoolId): int
    {
        return DB::table('academic_years')->insertGetId([
            'school_id' => $schoolId,
            'name' => '2026-2027',
            'starts_on' => '2026-01-01',
            'ends_on' => '2027-12-31',
            'is_current' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createClass(int $schoolId, string $name): int
    {
        return DB::table('classes')->insertGetId([
            'school_id' => $schoolId,
            'name' => $name,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSection(int $schoolId, int $classId, string $name): int
    {
        return DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'class_id' => $classId,
            'name' => $name,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createStudent(
        int $schoolId,
        ?int $userId,
        string $admissionNumber,
        string $firstName,
        ?int $academicYearId = null,
        ?int $classId = null,
        ?int $sectionId = null
    ): int {
        $studentId = DB::table('students')->insertGetId([
            'school_id' => $schoolId,
            'user_id' => $userId,
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'gender' => 'other',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($academicYearId && $classId && $sectionId) {
            DB::table('student_enrollments')->insert([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'status' => 'enrolled',
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $studentId;
    }
}
