<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Guardian;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── 1. Super Admin ────────────────────────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@schoolsaas.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'is_super_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // ── 2. Subscription Plans ─────────────────────────────────────────────
        $plan = Plan::firstOrCreate(
            ['name' => 'Starter'],
            [
                'price' => 99.00,
                'currency' => 'USD',
                'billing_interval' => 'monthly',
                'student_limit' => 500,
                'staff_limit' => 50,
                'is_active' => true,
            ]
        );

        Plan::firstOrCreate(
            ['name' => 'Professional'],
            [
                'price' => 199.00,
                'currency' => 'USD',
                'billing_interval' => 'monthly',
                'student_limit' => 2000,
                'staff_limit' => 200,
                'is_active' => true,
            ]
        );

        // ── 3. School ─────────────────────────────────────────────────────────
        $school = School::firstOrCreate(
            ['slug' => 'beaconhouse'],
            [
                'name' => 'Beaconhouse Model School',
                'address' => '123 Education Lane, Lahore, Punjab',
                'phone' => '+92-42-3571-0000',
                'email' => 'info@beaconhouse.edu',
                'currency' => 'PKR',
                'timezone' => 'Asia/Karachi',
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ]
        );

        // ── 4. Subscription ───────────────────────────────────────────────────
        Subscription::firstOrCreate(
            ['school_id' => $school->id, 'plan_id' => $plan->id],
            [
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
            ]
        );

        // ── 5. School Admin ───────────────────────────────────────────────────
        $schoolAdmin = User::firstOrCreate(
            ['email' => 'admin@beaconhouse.edu'],
            [
                'name' => 'Dr. Sarah Khan',
                'password' => Hash::make('password123'),
                'is_super_admin' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $school->users()->syncWithoutDetaching([
            $schoolAdmin->id => [
                'role' => 'school_admin',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        // ── 6. Teacher ────────────────────────────────────────────────────────
        $teacher = User::firstOrCreate(
            ['email' => 'ahmed.teacher@beaconhouse.edu'],
            [
                'name' => 'Ahmed Raza',
                'password' => Hash::make('password123'),
                'is_super_admin' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $school->users()->syncWithoutDetaching([
            $teacher->id => [
                'role' => 'teacher',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        Employee::firstOrCreate(
            ['school_id' => $school->id, 'email' => 'ahmed.teacher@beaconhouse.edu'],
            [
                'user_id' => $teacher->id,
                'employee_number' => 'EMP-2025-001',
                'name' => 'Ahmed Raza',
                'phone' => '+92-300-5555001',
                'job_title' => 'Mathematics Teacher',
                'role' => 'teacher',
                'salary' => 45000,
                'joining_date' => '2024-04-01',
                'status' => 'active',
            ]
        );

        // ── 7. Accountant ─────────────────────────────────────────────────────
        $accountant = User::firstOrCreate(
            ['email' => 'accounts@beaconhouse.edu'],
            [
                'name' => 'Ayesha Malik',
                'password' => Hash::make('password123'),
                'is_super_admin' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $school->users()->syncWithoutDetaching([
            $accountant->id => ['role' => 'accountant', 'status' => 'active', 'joined_at' => now()],
        ]);

        Employee::firstOrCreate(
            ['school_id' => $school->id, 'email' => 'accounts@beaconhouse.edu'],
            [
                'user_id' => $accountant->id,
                'employee_number' => 'EMP-2025-002',
                'name' => 'Ayesha Malik',
                'phone' => '+92-300-5555002',
                'job_title' => 'Accounts Officer',
                'role' => 'accountant',
                'salary' => 40000,
                'joining_date' => '2024-04-01',
                'status' => 'active',
            ]
        );

        // ── 8. Parent ─────────────────────────────────────────────────────────
        $parent = User::firstOrCreate(
            ['email' => 'parent.hassan@email.com'],
            [
                'name' => 'Hassan Ali',
                'password' => Hash::make('password123'),
                'is_super_admin' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $school->users()->syncWithoutDetaching([
            $parent->id => [
                'role' => 'parent',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        // ── 9. Student Portal User ────────────────────────────────────────────
        $studentUser = User::firstOrCreate(
            ['email' => 'student@beaconhouse.edu'],
            [
                'name' => 'Ali Hassan',
                'password' => Hash::make('password123'),
                'is_super_admin' => false,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $school->users()->syncWithoutDetaching([
            $studentUser->id => [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        // ── 10. Academic Year ──────────────────────────────────────────────────
        $academicYear = AcademicYear::firstOrCreate(
            ['school_id' => $school->id, 'name' => '2025-2026'],
            [
                'starts_on' => '2025-04-01',
                'ends_on' => '2026-03-31',
                'is_current' => true,
                'status' => 'active',
            ]
        );

        // ── 11. Classes & Sections ────────────────────────────────────────────
        $classData = [
            ['name' => 'Grade 1', 'sort_order' => 1],
            ['name' => 'Grade 2', 'sort_order' => 2],
            ['name' => 'Grade 5', 'sort_order' => 5],
            ['name' => 'Grade 9', 'sort_order' => 9],
            ['name' => 'Grade 10', 'sort_order' => 10],
        ];

        $createdClasses = [];

        foreach ($classData as $cls) {
            $schoolClass = SchoolClass::firstOrCreate(
                ['school_id' => $school->id, 'name' => $cls['name']],
                ['sort_order' => $cls['sort_order'], 'status' => 'active']
            );

            $createdClasses[] = $schoolClass;

            foreach (['A', 'B'] as $sectionName) {
                Section::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'class_id' => $schoolClass->id,
                        'name' => $sectionName,
                    ],
                    ['capacity' => 40, 'status' => 'active']
                );
            }
        }

        // ── 12. Subjects ──────────────────────────────────────────────────────
        $subjectNames = ['Mathematics', 'English', 'Science', 'Urdu', 'Islamiat', 'Computer'];

        foreach ($subjectNames as $subjectName) {
            Subject::firstOrCreate(
                ['school_id' => $school->id, 'name' => $subjectName],
                [
                    'code' => strtoupper(substr($subjectName, 0, 4)),
                    'status' => 'active',
                ]
            );
        }

        // ── 13. Guardian & Student ────────────────────────────────────────────
        $guardian = Guardian::firstOrCreate(
            ['school_id' => $school->id, 'email' => 'parent.hassan@email.com'],
            [
                'name' => 'Hassan Ali',
                'relationship' => 'father',
                'phone' => '+92-300-1234567',
                'address' => '456 Gulberg, Lahore',
                'user_id' => $parent->id,
            ]
        );

        $grade1Class = $createdClasses[0];
        $grade1SectionA = Section::where('school_id', $school->id)
            ->where('class_id', $grade1Class->id)
            ->where('name', 'A')
            ->first();

        $student = Student::firstOrCreate(
            ['school_id' => $school->id, 'admission_number' => 'BHS-2025-001'],
            [
                'user_id' => $studentUser->id,
                'first_name' => 'Ali',
                'last_name' => 'Hassan',
                'date_of_birth' => '2018-05-15',
                'gender' => 'male',
                'address' => '456 Gulberg, Lahore',
                'emergency_phone' => '+92-300-1234567',
                'admission_date' => now(),
                'status' => 'active',
            ]
        );

        // Link guardian to student
        DB::table('student_guardian')->insertOrIgnore([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Enroll student
        StudentEnrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'school_id' => $school->id,
                'class_id' => $grade1Class->id,
                'section_id' => $grade1SectionA?->id,
                'roll_number' => '001',
                'enrolled_at' => now(),
                'status' => 'enrolled',
            ]
        );

        $this->command->info('');
        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  Login Credentials  (Password: password123)');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  Super Admin:   superadmin@schoolsaas.com');
        $this->command->info('  School Admin:  admin@beaconhouse.edu');
        $this->command->info('  Teacher:       ahmed.teacher@beaconhouse.edu');
        $this->command->info('  Accountant:    accounts@beaconhouse.edu');
        $this->command->info('  Parent:        parent.hassan@email.com');
        $this->command->info('  Student:       student@beaconhouse.edu');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  URL: http://localhost:8000/login');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
