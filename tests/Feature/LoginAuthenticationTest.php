<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_only_common_email_and_password_authentication(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('type="email"', false)
            ->assertSee('type="password"', false)
            ->assertSee('Log in')
            ->assertDontSee('Super Admin')
            ->assertDontSee('School Admin')
            ->assertDontSee('Teacher Portal')
            ->assertDontSee('OTP')
            ->assertDontSee('Forgot Password?');

        $this->get('/login/otp')->assertNotFound();
    }

    public function test_school_admin_logs_in_to_their_school_dashboard(): void
    {
        [$user, $schoolId] = $this->createSchoolUser('admin@example.test', 'school_admin');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])
            ->assertRedirect(route('school-admin.dashboard'))
            ->assertSessionHas('current_school_id', $schoolId);

        $this->assertAuthenticatedAs($user);
        $this->get(route('school-admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_super_admin_logs_in_to_the_platform_dashboard(): void
    {
        $user = User::create([
            'name' => 'Platform Admin',
            'email' => 'platform@example.test',
            'password' => Hash::make('correct-password'),
            'status' => 'active',
        ]);
        $user->forceFill(['is_super_admin' => true])->save();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('super-admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Platform Control Hub');
    }

    public function test_teacher_logs_in_to_the_teacher_dashboard(): void
    {
        [$user] = $this->createSchoolUser('teacher@example.test', 'teacher');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('teacher.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Teacher Dashboard');
    }

    public function test_student_logs_in_to_the_student_dashboard(): void
    {
        [$user] = $this->createSchoolUser('student@example.test', 'student');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Student Dashboard');
    }

    public function test_parent_logs_in_to_the_parent_dashboard(): void
    {
        [$user] = $this->createSchoolUser('parent@example.test', 'parent');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('parent.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('Parent Dashboard');
    }

    public function test_invalid_credentials_are_rejected_without_authenticating(): void
    {
        $this->post(route('login.post'), [
            'email' => 'missing@example.test',
            'password' => 'incorrect-password',
        ])
            ->assertSessionHasErrors('email')
            ->assertSessionHasErrors(['email' => 'Invalid email or password.']);

        $this->assertGuest();
    }

    public function test_inactive_school_members_are_rejected_at_login(): void
    {
        [$user, $schoolId] = $this->createSchoolUser('inactive@example.test', 'school_admin');
        School::query()->findOrFail($schoolId)->update(['status' => 'suspended']);

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Your school account is currently inactive. Please contact your school administrator.',
            ]);

        $this->assertGuest();
    }

    public function test_a_logged_in_member_is_logged_out_after_school_deactivation(): void
    {
        [$user, $schoolId] = $this->createSchoolUser('existing-session@example.test', 'school_admin');

        School::query()->findOrFail($schoolId)->update(['status' => 'suspended']);

        $this->actingAs($user)
            ->withSession(['current_school_id' => $schoolId])
            ->get(route('school-admin.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'school' => 'Your school account is currently inactive. Please contact your school administrator.',
            ]);

        $this->assertGuest();
    }

    public function test_disabled_student_portal_does_not_block_school_administration(): void
    {
        [$student, $schoolId] = $this->createSchoolUser('portal-student@example.test', 'student');
        School::query()->findOrFail($schoolId)->update(['enabled_portals' => ['school_admin']]);

        $this->post(route('login.post'), [
            'email' => $student->email,
            'password' => 'correct-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'The portal for your role is currently disabled for this school.',
            ]);

        $this->assertGuest();

        $schoolAdmin = User::factory()->create();
        $schoolAdmin->schools()->attach($schoolId, [
            'role' => 'school_admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($schoolAdmin)
            ->withSession(['current_school_id' => $schoolId])
            ->get(route('school-admin.students.index'))
            ->assertOk();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::create([
            'name' => 'Signed In User',
            'email' => 'signed-in@example.test',
            'password' => Hash::make('correct-password'),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * @return array{0: User, 1: int}
     */
    private function createSchoolUser(string $email, string $role): array
    {
        $now = now();
        $user = User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => Hash::make('correct-password'),
            'status' => 'active',
        ]);

        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Test School',
            'slug' => 'test-school',
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

        return [$user, $schoolId];
    }
}
