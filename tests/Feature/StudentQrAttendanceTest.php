<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentQrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_qr_tokens_are_unique_secret_and_hidden_from_serialization(): void
    {
        $data = $this->createSchoolSetup();
        $firstStudent = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);
        $secondStudentId = $this->createStudent($data['school_id'], $data['year_id'], $data['class_id'], $data['section_id'], 'A-002');
        $secondStudent = Student::forSchool($data['school_id'])->findOrFail($secondStudentId);

        $this->assertSame(64, strlen($firstStudent->qr_token));
        $this->assertNotSame($firstStudent->qr_token, $secondStudent->qr_token);
        $this->assertSame(hash('sha256', $firstStudent->qr_token), $firstStudent->qr_token_hash);
        $this->assertArrayNotHasKey('qr_token', $firstStudent->toArray());
        $this->assertArrayNotHasKey('qr_token_hash', $firstStudent->toArray());
    }

    public function test_authorized_staff_can_print_same_qr_card_without_exposing_token_as_text(): void
    {
        $data = $this->createSchoolSetup();
        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_id']])
            ->get(route('school-admin.students.qr-card', $student));

        $response->assertOk()
            ->assertSee('North School')
            ->assertSee('Student A')
            ->assertSee('A-001')
            ->assertSee('Class 1')
            ->assertSee('Section A')
            ->assertSee('<svg', false)
            ->assertDontSee($student->qr_token);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'qr_token_hash' => hash('sha256', $student->qr_token),
        ]);
    }

    public function test_staff_can_replace_and_revoke_a_students_qr_token(): void
    {
        $data = $this->createSchoolSetup();
        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);
        $oldToken = $student->qr_token;
        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']]);

        $this->post(route('school-admin.students.qr-token.rotate', $student))
            ->assertRedirect(route('school-admin.students.qr-card', $student));

        $student->refresh();
        $this->assertNotSame($oldToken, $student->qr_token);
        $this->postJson(route('school-admin.attendance.qr-scan'), ['token' => $oldToken])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'invalid');

        $this->delete(route('school-admin.students.qr-token.revoke', $student))
            ->assertRedirect(route('school-admin.students.show', $student));

        $student->refresh();
        $this->assertNull($student->qr_token_hash);
        $this->assertNotNull($student->qr_token_revoked_at);
        $this->postJson(route('school-admin.attendance.qr-scan'), ['token' => $student->qr_token ?: str_repeat('a', 64)])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'invalid');
    }

    public function test_valid_scan_records_present_and_repeated_scan_is_idempotent(): void
    {
        $data = $this->createSchoolSetup();
        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);
        $payload = ['token' => $student->qr_token];
        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']]);

        $this->postJson(route('school-admin.attendance.qr-scan'), $payload)
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('name', 'Student A')
            ->assertJsonPath('admission_number', 'A-001')
            ->assertJsonPath('class', 'Class 1')
            ->assertJsonPath('section', 'Section A');

        $this->postJson(route('school-admin.attendance.qr-scan'), $payload)
            ->assertOk()
            ->assertJsonPath('status', 'duplicate');

        $this->assertDatabaseCount('attendance_sessions', 1);
        $this->assertDatabaseCount('student_attendance', 1);
        $this->assertDatabaseHas('student_attendance', [
            'school_id' => $data['school_id'],
            'student_id' => $student->id,
            'status' => 'present',
            'remarks' => 'QR scan',
        ]);
        $this->assertDatabaseCount('student_qr_scan_events', 1);
    }

    public function test_invalid_revoked_and_other_school_tokens_cannot_record_attendance(): void
    {
        $data = $this->createSchoolSetup();
        $otherSchool = $this->createSchoolSetup('Other School', 'other-admin@example.test');
        $foreignStudent = Student::forSchool($otherSchool['school_id'])->findOrFail($otherSchool['student_id']);

        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']])
            ->postJson(route('school-admin.attendance.qr-scan'), ['token' => str_repeat('0', 64)])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'invalid');

        $this->postJson(route('school-admin.attendance.qr-scan'), ['token' => $foreignStudent->qr_token])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'wrong_school')
            ->assertJsonPath('message', 'This QR code belongs to a different school. No attendance was recorded.');

        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);
        $revokedToken = $student->qr_token;
        $student->revokeQrToken();

        $this->postJson(route('school-admin.attendance.qr-scan'), ['token' => $revokedToken])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'invalid');

        $this->assertDatabaseCount('attendance_sessions', 0);
        $this->assertDatabaseCount('student_attendance', 0);
    }

    public function test_scan_never_overwrites_a_manually_entered_attendance_record(): void
    {
        $data = $this->createSchoolSetup();
        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);
        $sessionId = DB::table('attendance_sessions')->insertGetId([
            'school_id' => $data['school_id'],
            'academic_year_id' => $data['year_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'attendance_date' => now('Asia/Karachi')->toDateString(),
            'taken_by' => $data['user']->id,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_attendance')->insert([
            'school_id' => $data['school_id'],
            'attendance_session_id' => $sessionId,
            'student_id' => $student->id,
            'status' => 'late',
            'marked_by' => $data['user']->id,
            'marked_at' => now(),
            'remarks' => 'Manual correction',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']])
            ->postJson(route('school-admin.attendance.qr-scan'), ['token' => $student->qr_token])
            ->assertOk()
            ->assertJsonPath('status', 'duplicate')
            ->assertJsonPath('attendance', 'Late');

        $this->assertDatabaseHas('student_attendance', [
            'attendance_session_id' => $sessionId,
            'student_id' => $student->id,
            'status' => 'late',
            'remarks' => 'Manual correction',
        ]);
    }

    public function test_scan_does_not_overwrite_a_manual_attendance_record_created_after_the_session_was_loaded(): void
    {
        $data = $this->createSchoolSetup();
        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);
        $sessionId = DB::table('attendance_sessions')->insertGetId([
            'school_id' => $data['school_id'],
            'academic_year_id' => $data['year_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'attendance_date' => now('Asia/Karachi')->toDateString(),
            'taken_by' => $data['user']->id,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $operator = $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']]);
        $operator->get(route('school-admin.attendance.index', [
            'academic_year_id' => $data['year_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'date' => now('Asia/Karachi')->toDateString(),
        ]))->assertOk();

        DB::table('student_attendance')->insert([
            'school_id' => $data['school_id'],
            'attendance_session_id' => $sessionId,
            'student_id' => $student->id,
            'status' => 'absent',
            'marked_by' => $data['user']->id,
            'marked_at' => now(),
            'remarks' => 'Manual after scanner page loaded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $operator->postJson(route('school-admin.attendance.qr-scan'), ['token' => $student->qr_token])
            ->assertOk()
            ->assertJsonPath('status', 'duplicate')
            ->assertJsonPath('attendance', 'Absent');

        $this->assertDatabaseHas('student_attendance', [
            'attendance_session_id' => $sessionId,
            'student_id' => $student->id,
            'status' => 'absent',
            'remarks' => 'Manual after scanner page loaded',
        ]);
    }

    public function test_review_policy_persists_gate_entry_without_finalizing_attendance(): void
    {
        $data = $this->createSchoolSetup();
        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);
        config(['attendance.qr_scan_policy' => 'review']);

        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']])
            ->postJson(route('school-admin.attendance.qr-scan'), ['token' => $student->qr_token])
            ->assertOk()
            ->assertJsonPath('status', 'review_required');

        $this->postJson(route('school-admin.attendance.qr-scan'), ['token' => $student->qr_token])
            ->assertOk()
            ->assertJsonPath('status', 'review_required');

        $this->assertDatabaseCount('student_attendance', 0);
        $this->assertDatabaseCount('student_qr_scan_events', 1);
        $this->assertDatabaseHas('student_qr_scan_events', [
            'student_id' => $student->id,
            'processing_status' => 'pending_review',
        ]);
    }

    public function test_manual_search_can_record_attendance_without_camera_access(): void
    {
        $data = $this->createSchoolSetup();

        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']])
            ->postJson(route('school-admin.attendance.qr-manual'), ['search' => 'A-001'])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('name', 'Student A');

        $this->assertDatabaseHas('student_attendance', [
            'school_id' => $data['school_id'],
            'student_id' => $data['student_id'],
            'status' => 'present',
        ]);
    }

    public function test_manual_search_does_not_allow_ambiguous_student_name_matching(): void
    {
        $data = $this->createSchoolSetup();
        $this->createStudent($data['school_id'], $data['year_id'], $data['class_id'], $data['section_id'], 'A-002');

        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']])
            ->postJson(route('school-admin.attendance.qr-manual'), ['search' => 'Student'])
            ->assertStatus(409)
            ->assertJsonPath('status', 'ambiguous');

        $this->assertDatabaseCount('attendance_sessions', 0);
        $this->assertDatabaseCount('student_attendance', 0);
    }

    public function test_unprivileged_school_role_cannot_open_qr_attendance_scanner(): void
    {
        $data = $this->createSchoolSetup();
        $accountant = $this->createMember($data['school_id'], 'accountant@example.test', 'accountant');

        $this->actingAs($accountant)
            ->withSession(['current_school_id' => $data['school_id']])
            ->get(route('school-admin.attendance.qr-scanner'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_scan_for_a_class_they_are_not_assigned_to(): void
    {
        $data = $this->createSchoolSetup();
        $teacher = $this->createMember($data['school_id'], 'teacher@example.test', 'teacher');
        $student = Student::forSchool($data['school_id'])->findOrFail($data['student_id']);

        $this->actingAs($teacher)->withSession(['current_school_id' => $data['school_id']])
            ->postJson(route('school-admin.attendance.qr-scan'), ['token' => $student->qr_token])
            ->assertForbidden();

        $this->assertDatabaseCount('attendance_sessions', 0);
        $this->assertDatabaseCount('student_attendance', 0);
    }

    public function test_scanner_and_manual_search_require_authentication(): void
    {
        $this->get(route('school-admin.attendance.qr-scanner'))
            ->assertRedirect(route('login'));

        $this->postJson(route('school-admin.attendance.qr-scan'), ['token' => str_repeat('a', 64)])
            ->assertUnauthorized();

        $this->postJson(route('school-admin.attendance.qr-manual'), ['search' => 'A-001'])
            ->assertUnauthorized();
    }

    public function test_qr_card_and_token_management_hide_students_from_another_school(): void
    {
        $data = $this->createSchoolSetup();
        $otherSchool = $this->createSchoolSetup('Other School', 'other-admin@example.test');
        $foreignStudent = Student::forSchool($otherSchool['school_id'])->findOrFail($otherSchool['student_id']);

        $this->actingAs($data['user'])->withSession(['current_school_id' => $data['school_id']])
            ->get(route('school-admin.students.qr-card', $foreignStudent))
            ->assertNotFound();

        $this->post(route('school-admin.students.qr-token.rotate', $foreignStudent))
            ->assertNotFound();

        $this->delete(route('school-admin.students.qr-token.revoke', $foreignStudent))
            ->assertNotFound();

        $this->assertNotNull($foreignStudent->refresh()->qr_token_hash);
    }

    /**
     * @return array{user: User, school_id: int, year_id: int, class_id: int, section_id: int, student_id: int}
     */
    private function createSchoolSetup(string $schoolName = 'North School', string $adminEmail = 'admin@example.test'): array
    {
        $now = now();
        $schoolId = $this->createSchool($schoolName);
        $user = $this->createMember($schoolId, $adminEmail, 'school_admin');
        $planId = DB::table('plans')->insertGetId([
            'name' => 'QR Test Plan',
            'price' => 0,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'student_limit' => 50,
            'staff_limit' => 50,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('subscriptions')->insert([
            'school_id' => $schoolId,
            'plan_id' => $planId,
            'status' => 'active',
            'starts_at' => $now,
            'ends_at' => $now->copy()->addYear(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $yearId = DB::table('academic_years')->insertGetId([
            'school_id' => $schoolId,
            'name' => '2026-2030',
            'starts_on' => '2026-01-01',
            'ends_on' => '2030-12-31',
            'is_current' => true,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $classId = DB::table('classes')->insertGetId([
            'school_id' => $schoolId,
            'name' => 'Class 1',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'class_id' => $classId,
            'name' => 'Section A',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $studentId = $this->createStudent($schoolId, $yearId, $classId, $sectionId, 'A-001');

        return compact('user', 'schoolId', 'yearId', 'classId', 'sectionId', 'studentId') + [
            'school_id' => $schoolId,
            'year_id' => $yearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'student_id' => $studentId,
        ];
    }

    private function createSchool(string $name): int
    {
        return DB::table('schools')->insertGetId([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'status' => 'active',
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMember(int $schoolId, string $email, string $role): User
    {
        $user = User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        DB::table('school_user')->insert([
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'role' => $role,
            'status' => 'active',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function createStudent(int $schoolId, int $yearId, int $classId, int $sectionId, string $admissionNumber): int
    {
        $student = Student::create([
            'school_id' => $schoolId,
            'admission_number' => $admissionNumber,
            'first_name' => 'Student',
            'last_name' => $admissionNumber === 'A-001' ? 'A' : 'B',
            'gender' => 'other',
            'status' => 'active',
        ]);
        DB::table('student_enrollments')->insert([
            'school_id' => $schoolId,
            'student_id' => $student->id,
            'academic_year_id' => $yearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'roll_number' => $admissionNumber,
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) $student->id;
    }
}
