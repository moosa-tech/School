<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_cannot_record_attendance_for_another_schools_student(): void
    {
        $data = $this->createTwoSchoolSetup();

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-10-09',
                'attendance' => [
                    $data['student_b'] => ['status' => 'present'],
                ],
                'action' => 'save_draft',
            ]);

        $response->assertSessionHasErrors('attendance');
        $this->assertDatabaseCount('attendance_sessions', 0);
        $this->assertDatabaseCount('student_attendance', 0);
    }

    public function test_school_admin_can_record_attendance_for_an_enrolled_student(): void
    {
        $data = $this->createTwoSchoolSetup();

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-10-09',
                'attendance' => [
                    $data['student_a'] => ['status' => 'present'],
                ],
                'action' => 'save_draft',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_attendance', [
            'school_id' => $data['school_a'],
            'student_id' => $data['student_a'],
            'status' => 'present',
        ]);
    }

    public function test_school_admin_can_update_and_reopen_a_students_daily_attendance(): void
    {
        $data = $this->createTwoSchoolSetup();
        $payload = [
            'academic_year_id' => $data['academic_year_a'],
            'class_id' => $data['class_a'],
            'section_id' => $data['section_a'],
            'date' => '2026-10-09',
            'attendance' => [
                $data['student_a'] => ['status' => 'present', 'remarks' => 'On time'],
            ],
            'action' => 'save_draft',
        ];

        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), $payload)
            ->assertSessionHasNoErrors();

        $payload['attendance'][$data['student_a']] = ['status' => 'late', 'remarks' => 'Bus delay'];
        $response = $this->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), $payload)
            ->assertSessionHasNoErrors();
        $response->assertRedirect(route('school-admin.attendance.index', [
            'academic_year_id' => $data['academic_year_a'],
            'class_id' => $data['class_a'],
            'section_id' => $data['section_a'],
            'date' => '2026-10-09',
        ]));

        $this->assertDatabaseCount('attendance_sessions', 1);
        $this->assertDatabaseCount('student_attendance', 1);
        $this->assertDatabaseHas('student_attendance', [
            'school_id' => $data['school_a'],
            'student_id' => $data['student_a'],
            'status' => 'late',
            'remarks' => 'Bus delay',
        ]);

        $this->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.index', [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-10-09',
            ]))
            ->assertOk()
            ->assertSee('Bus delay')
            ->assertSee('Late');
    }

    public function test_student_monthly_register_shows_month_days_and_calculated_totals(): void
    {
        $data = $this->createTwoSchoolSetup();
        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-02-02',
                'attendance' => [
                    $data['student_a'] => ['status' => 'present'],
                ],
                'action' => 'save_draft',
            ])
            ->assertSessionHasNoErrors();

        $this->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.register', [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'month' => 2,
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertSee('February 2026')
            ->assertSee('100%')
            ->assertSee('Unrecorded dates are shown')
            ->assertSee('overflow-x-auto')
            ->assertSee('min-w-max')
            ->assertSee('Print / Save PDF');

        $leapYearId = DB::table('academic_years')->insertGetId([
            'school_id' => $data['school_a'],
            'name' => '2024 Leap Year',
            'starts_on' => '2024-01-01',
            'ends_on' => '2024-12-31',
            'is_current' => false,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.register', [
                'academic_year_id' => $leapYearId,
                'class_id' => $data['class_a'],
                'month' => 2,
                'year' => 2024,
            ]))
            ->assertOk()
            ->assertSee('Thursday, February 29, 2024');
    }

    public function test_student_monthly_register_includes_records_from_each_section(): void
    {
        $data = $this->createTwoSchoolSetup();
        $sectionB = $this->createSection($data['school_a'], $data['class_a'], 'Section B');
        $studentB = $this->createStudent($data['school_a'], 'A-002');
        $this->createEnrollment($data['school_a'], $studentB, $data['academic_year_a'], $data['class_a'], $sectionB);
        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-03-02',
                'attendance' => [$data['student_a'] => ['status' => 'present']],
                'action' => 'save_draft',
            ])
            ->assertSessionHasNoErrors();

        $this->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $sectionB,
                'date' => '2026-03-02',
                'attendance' => [$studentB => ['status' => 'absent']],
                'action' => 'save_draft',
            ])
            ->assertSessionHasNoErrors();

        $this->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.register', [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'month' => 3,
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertSee('A-001')
            ->assertSee('A-002')
            ->assertSee('50%');
    }

    public function test_student_attendance_remains_visible_after_enrollment_moves(): void
    {
        $data = $this->createTwoSchoolSetup();
        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-10-09',
                'attendance' => [$data['student_a'] => ['status' => 'absent']],
                'action' => 'save_draft',
            ])
            ->assertSessionHasNoErrors();

        $newClassId = $this->createClass($data['school_a'], 'Class Moved');
        $newSectionId = $this->createSection($data['school_a'], $newClassId, 'Section Moved');
        DB::table('student_enrollments')
            ->where('student_id', $data['student_a'])
            ->update(['class_id' => $newClassId, 'section_id' => $newSectionId]);

        $this->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.index', [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-10-09',
            ]))
            ->assertOk()
            ->assertSee('A-001')
            ->assertSee('Absent');

        $this->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.register', [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'month' => 10,
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertSee('A-001')
            ->assertSee('Absent');
    }

    public function test_teacher_without_assignments_sees_message_instead_of_school_classes(): void
    {
        $data = $this->createTwoSchoolSetup();
        $teacher = $this->createSchoolMember($data['school_a'], 'teacher-no-classes@example.test', 'teacher');

        $this->actingAs($teacher)
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.index'))
            ->assertOk()
            ->assertSee('No classes have been assigned to you yet.');

        $this->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2026-10-09',
                'attendance' => [
                    $data['student_a'] => ['status' => 'present'],
                ],
                'action' => 'save_draft',
            ])
            ->assertForbidden();
    }

    public function test_school_admin_can_update_teacher_attendance_without_duplicates(): void
    {
        $data = $this->createTwoSchoolSetup();
        $employeeId = DB::table('employees')->insertGetId([
            'school_id' => $data['school_a'],
            'employee_number' => 'T-001',
            'name' => 'Teacher One',
            'job_title' => 'Teacher',
            'role' => 'teacher',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.staff-attendance.save'), [
                'date' => '2026-10-09',
                'attendance' => [
                    $employeeId => ['status' => 'present', 'notes' => 'On time'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $response = $this->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.staff-attendance.save'), [
                'date' => '2026-10-09',
                'attendance' => [
                    $employeeId => ['status' => 'leave', 'notes' => 'Approved leave'],
                ],
            ])
            ->assertSessionHasNoErrors();
        $response->assertRedirect(route('school-admin.staff-attendance.index', ['date' => '2026-10-09']));

        $this->assertDatabaseCount('staff_attendance', 1);
        $this->assertDatabaseHas('staff_attendance', [
            'school_id' => $data['school_a'],
            'employee_id' => $employeeId,
            'status' => 'excused',
            'notes' => 'Approved leave',
        ]);

        $this->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.staff-attendance.register', ['month' => 10, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Teacher One')
            ->assertSee('100%')
            ->assertSee('overflow-x-auto')
            ->assertSee('Print / Save PDF');
    }

    public function test_school_admin_can_assign_and_unassign_a_teacher_from_a_class_subject(): void
    {
        $data = $this->createTwoSchoolSetup();
        $teacherUser = $this->createSchoolMember($data['school_a'], 'assigned-teacher@example.test', 'teacher');
        $employeeId = DB::table('employees')->insertGetId([
            'school_id' => $data['school_a'],
            'user_id' => $teacherUser->id,
            'employee_number' => 'T-002',
            'name' => 'Assigned Teacher',
            'job_title' => 'Teacher',
            'role' => 'teacher',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $subjectId = DB::table('subjects')->insertGetId([
            'school_id' => $data['school_a'],
            'name' => 'Mathematics',
            'code' => 'MATH',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.subjects.assign'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'subject_id' => $subjectId,
                'employee_id' => $employeeId,
            ])
            ->assertRedirect();

        $classSubjectId = (int) DB::table('class_subjects')
            ->where('school_id', $data['school_a'])
            ->value('id');
        $this->assertDatabaseHas('teacher_assignments', [
            'school_id' => $data['school_a'],
            'employee_id' => $employeeId,
            'class_subject_id' => $classSubjectId,
            'academic_year_id' => $data['academic_year_a'],
        ]);

        $unassignedClassId = $this->createClass($data['school_a'], 'Class Not Assigned');
        $unassignedSectionId = $this->createSection($data['school_a'], $unassignedClassId, 'Section Not Assigned');
        $unassignedStudentId = $this->createStudent($data['school_a'], 'A-003');
        $this->createEnrollment(
            $data['school_a'],
            $unassignedStudentId,
            $data['academic_year_a'],
            $unassignedClassId,
            $unassignedSectionId
        );

        $this->actingAs($teacherUser)
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.index', [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
            ]))
            ->assertOk()
            ->assertSee('A-001')
            ->assertDontSee('A-003')
            ->assertDontSee('No classes have been assigned to you yet.');

        $this->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'), [
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $unassignedClassId,
                'section_id' => $unassignedSectionId,
                'date' => '2026-10-09',
                'attendance' => [$unassignedStudentId => ['status' => 'present']],
                'action' => 'save_draft',
            ])
            ->assertForbidden();

        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.subjects.teacher.update', $classSubjectId), ['employee_id' => ''])
            ->assertRedirect();

        $this->assertDatabaseMissing('teacher_assignments', ['class_subject_id' => $classSubjectId]);
        $this->assertDatabaseHas('class_subjects', ['id' => $classSubjectId, 'teacher_id' => null]);
    }

    public function test_accountant_cannot_mark_attendance(): void
    {
        $data = $this->createTwoSchoolSetup();
        $accountant = $this->createSchoolMember($data['school_a'], 'accountant@example.test', 'accountant');

        $this->actingAs($accountant)
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.attendance.save'))
            ->assertForbidden();
    }

    public function test_staff_cannot_access_fee_operations(): void
    {
        $data = $this->createTwoSchoolSetup();
        $staff = $this->createSchoolMember($data['school_a'], 'staff@example.test', 'staff');

        $this->actingAs($staff)
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.fees.invoices'))
            ->assertForbidden();
    }

    public function test_super_admin_cannot_access_school_operations(): void
    {
        $data = $this->createTwoSchoolSetup();
        $superAdmin = User::create([
            'name' => 'Platform Admin',
            'email' => 'platform@example.test',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        config(['saas.support_access_enabled' => false]);

        $this->actingAs($superAdmin)
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.dashboard'))
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_school_dashboard(): void
    {
        $this->get(route('school-admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_cannot_enroll_a_student_using_another_schools_class(): void
    {
        $data = $this->createTwoSchoolSetup();

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.students.store'), [
                'admission_number' => 'A-NEW',
                'first_name' => 'New Student',
                'gender' => 'other',
                'admission_date' => '2026-10-09',
                'academic_year_id' => $data['academic_year_b'],
                'class_id' => $data['class_b'],
                'section_id' => $data['section_b'],
                'guardian_name' => 'Guardian',
                'guardian_relation' => 'Parent',
            ]);

        $response->assertSessionHasErrors(['academic_year_id', 'class_id', 'section_id']);
        $this->assertDatabaseMissing('students', ['admission_number' => 'A-NEW']);
    }

    public function test_school_admin_can_admit_a_student_and_create_their_portal_account(): void
    {
        $data = $this->createTwoSchoolSetup();

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.students.store'), [
                'admission_number' => 'A-NEW',
                'first_name' => 'New Student',
                'gender' => 'other',
                'admission_date' => '2026-10-09',
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'guardian_name' => 'Guardian',
                'guardian_relation' => 'Parent',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'school_id' => $data['school_a'],
            'admission_number' => 'A-NEW',
            'first_name' => 'New Student',
        ]);
        $this->assertDatabaseHas('school_user', [
            'school_id' => $data['school_a'],
            'role' => 'student',
            'status' => 'active',
        ]);

        $studentUser = DB::table('users')
            ->join('school_user', 'users.id', '=', 'school_user.user_id')
            ->where('school_user.school_id', $data['school_a'])
            ->where('school_user.role', 'student')
            ->first();

        $this->assertNotNull($studentUser);
        $this->assertFalse(Hash::check('password123', $studentUser->password));
        $this->assertTrue((bool) DB::table('users')->where('id', $studentUser->id)->value('must_change_password'));
        $response->assertSessionHas('account_credentials', function (array $credentials): bool {
            $password = Crypt::decryptString($credentials['encrypted_password']);

            return $credentials['username'] !== ''
                && Hash::check($password, DB::table('users')->where('email', $credentials['username'])->value('password'))
                && ! str_contains($credentials['encrypted_password'], $password);
        });
    }

    public function test_school_admin_cannot_create_an_invoice_for_another_schools_student(): void
    {
        $data = $this->createTwoSchoolSetup();

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.fees.invoices.store'), [
                'student_id' => $data['student_b'],
                'academic_year_id' => $data['academic_year_a'],
                'billing_period' => 'October 2026',
                'issue_date' => '2026-10-09',
                'due_date' => '2026-10-30',
                'items' => [
                    ['description' => 'Tuition', 'amount' => 100],
                ],
            ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('fee_invoices', 0);
    }

    public function test_school_admin_cannot_view_another_schools_student_by_changing_the_id(): void
    {
        $data = $this->createTwoSchoolSetup();

        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.students.show', $data['student_b']))
            ->assertNotFound();
    }

    public function test_student_directory_matches_any_populated_filter_without_leaking_other_schools(): void
    {
        $data = $this->createTwoSchoolSetup();

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.students.index', [
                'search' => 'A-001',
                'status' => 'withdrawn',
                'class_id' => $data['class_b'],
                'date_from' => '2027-01-01',
            ]));

        $response->assertOk()->assertSee('A-001')->assertDontSee('B-001');
    }

    public function test_attendance_filter_explains_when_selected_year_has_no_enrollments(): void
    {
        $data = $this->createTwoSchoolSetup();
        $nextYear = $this->createAcademicYear($data['school_a'], '2027 A');

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.attendance.index', [
                'academic_year_id' => $nextYear,
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'date' => '2027-02-01',
            ]));

        $response->assertOk()
            ->assertSee('No active students are enrolled in the selected class, section, and academic year.')
            ->assertDontSee('Select academic year, class, and section above to load enrolled students.');
    }

    public function test_student_subscription_limit_is_enforced_before_creating_a_record(): void
    {
        $data = $this->createTwoSchoolSetup();
        DB::table('plans')->where('name', 'Test Plan')->update(['student_limit' => 1]);

        $response = $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->post(route('school-admin.students.store'), [
                'admission_number' => 'A-LIMIT',
                'first_name' => 'Limit Test',
                'gender' => 'other',
                'admission_date' => '2026-10-09',
                'academic_year_id' => $data['academic_year_a'],
                'class_id' => $data['class_a'],
                'section_id' => $data['section_a'],
                'guardian_name' => 'Guardian',
                'guardian_relation' => 'Parent',
            ]);

        $response->assertSessionHasErrors('student_limit');
        $this->assertDatabaseMissing('students', ['admission_number' => 'A-LIMIT']);
    }

    public function test_expired_subscription_keeps_school_records_read_only(): void
    {
        $data = $this->createTwoSchoolSetup();
        DB::table('subscriptions')
            ->where('school_id', $data['school_a'])
            ->update([
                'status' => 'expired',
                'ends_at' => now()->subDay(),
            ]);

        $this->actingAs($data['user'])
            ->withSession(['current_school_id' => $data['school_a']])
            ->get(route('school-admin.students.index'))
            ->assertOk();

        $response = $this->post(route('school-admin.students.store'), [
            'admission_number' => 'A-EXPIRED',
            'first_name' => 'Expired Test',
            'gender' => 'other',
            'admission_date' => '2026-10-09',
            'academic_year_id' => $data['academic_year_a'],
            'class_id' => $data['class_a'],
            'section_id' => $data['section_a'],
            'guardian_name' => 'Guardian',
            'guardian_relation' => 'Parent',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('students', ['admission_number' => 'A-EXPIRED']);
    }

    /**
     * @return array{user: User, school_a: int, academic_year_a: int, academic_year_b: int, class_a: int, class_b: int, section_a: int, section_b: int, student_a: int, student_b: int}
     */
    private function createTwoSchoolSetup(): array
    {
        $now = now();
        $user = User::create([
            'name' => 'School A Admin',
            'email' => 'admin-a@example.test',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $schoolA = $this->createSchool('School A');
        $schoolB = $this->createSchool('School B');
        $planId = DB::table('plans')->insertGetId([
            'name' => 'Test Plan',
            'price' => 0,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'student_limit' => 50,
            'staff_limit' => 50,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([$schoolA, $schoolB] as $schoolId) {
            DB::table('subscriptions')->insert([
                'school_id' => $schoolId,
                'plan_id' => $planId,
                'status' => 'active',
                'starts_at' => $now,
                'ends_at' => $now->copy()->addYear(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('school_user')->insert([
            'school_id' => $schoolA,
            'user_id' => $user->id,
            'role' => 'school_admin',
            'status' => 'active',
            'joined_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $yearA = $this->createAcademicYear($schoolA, '2026 A');
        $yearB = $this->createAcademicYear($schoolB, '2026 B');
        $classA = $this->createClass($schoolA, 'Class A');
        $classB = $this->createClass($schoolB, 'Class B');
        $sectionA = $this->createSection($schoolA, $classA, 'Section A');
        $sectionB = $this->createSection($schoolB, $classB, 'Section B');
        $studentA = $this->createStudent($schoolA, 'A-001');
        $studentB = $this->createStudent($schoolB, 'B-001');

        $this->createEnrollment($schoolA, $studentA, $yearA, $classA, $sectionA);
        $this->createEnrollment($schoolB, $studentB, $yearB, $classB, $sectionB);

        return [
            'user' => $user,
            'school_a' => $schoolA,
            'academic_year_a' => $yearA,
            'academic_year_b' => $yearB,
            'class_a' => $classA,
            'class_b' => $classB,
            'section_a' => $sectionA,
            'section_b' => $sectionB,
            'student_a' => $studentA,
            'student_b' => $studentB,
        ];
    }

    private function createSchool(string $name): int
    {
        return DB::table('schools')->insertGetId([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'status' => 'active',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSchoolMember(int $schoolId, string $email, string $role): User
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

    private function createAcademicYear(int $schoolId, string $name): int
    {
        return DB::table('academic_years')->insertGetId([
            'school_id' => $schoolId,
            'name' => $name,
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
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

    private function createStudent(int $schoolId, string $admissionNumber): int
    {
        return DB::table('students')->insertGetId([
            'school_id' => $schoolId,
            'admission_number' => $admissionNumber,
            'first_name' => 'Student',
            'gender' => 'other',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createEnrollment(int $schoolId, int $studentId, int $yearId, int $classId, int $sectionId): void
    {
        DB::table('student_enrollments')->insert([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'academic_year_id' => $yearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
