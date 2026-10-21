<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchoolControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_school_issues_a_password_setting_invitation(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $plan = Plan::create(['name' => 'Basic']);

        $response = $this->actingAs($superAdmin)->post(route('super-admin.schools.store'), [
            'name' => 'Example Academy',
            'admin_email' => 'admin@example.test',
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'plan_id' => $plan->id,
            'is_trial' => '1',
            'status' => 'active',
            'enabled_portals' => ['school_admin', 'teacher'],
        ]);
        $response->assertSessionHasNoErrors();

        $school = School::query()->where('email', 'admin@example.test')->firstOrFail();
        $invitation = SchoolInvitation::query()->where('school_id', $school->id)->firstOrFail();
        $invitationUrl = session('invitation_url');
        $token = basename((string) parse_url($invitationUrl, PHP_URL_PATH));

        $response
            ->assertRedirect(route('super-admin.schools.show', $school))
            ->assertSessionHas('initial_admin_email', 'admin@example.test')
            ->assertSessionHas('invitation_url')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.test']);
        $this->assertSame(hash('sha256', $token), $invitation->token_hash);
        $this->assertSame(['school_admin', 'teacher'], $school->enabled_portals);
        $this->assertDatabaseHas('subscriptions', [
            'school_id' => $school->id,
            'status' => 'trial',
        ]);

        $this->get(route('super-admin.schools.show', $school))
            ->assertOk()
            ->assertSee('School Admin Invitation')
            ->assertSee($invitationUrl)
            ->assertDontSee('Default Password');

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('invitations.accept.post', ['token' => $token]), [
            'name' => 'School Administrator',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('school-admin.dashboard'));

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('secure-password', $admin->password));
        $this->assertDatabaseHas('school_user', [
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'role' => 'school_admin',
            'status' => 'active',
        ]);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.post'), [
            'email' => 'admin@example.test',
            'password' => 'secure-password',
        ])->assertRedirect(route('school-admin.dashboard'));
    }

    public function test_school_creation_rejects_an_email_that_already_has_an_account(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $existingUser = User::factory()->create(['email' => 'admin@example.test']);
        $plan = Plan::create(['name' => 'Basic']);

        $response = $this->actingAs($superAdmin)->post(route('super-admin.schools.store'), [
            'name' => 'Example Academy',
            'admin_email' => $existingUser->email,
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'plan_id' => $plan->id,
            'status' => 'pending',
            'enabled_portals' => ['school_admin'],
        ]);

        $response->assertSessionHasErrors('admin_email');
        $this->assertDatabaseMissing('schools', ['name' => 'Example Academy']);
    }

    public function test_super_admin_can_update_school_status_and_portals(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $school = School::create([
            'name' => 'Example Academy',
            'slug' => 'example-academy',
            'status' => 'active',
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'enabled_portals' => ['school_admin', 'student'],
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('super-admin.schools.update', $school), [
                'name' => $school->name,
                'email' => 'contact@example.test',
                'phone' => '123456',
                'address' => 'Main Road',
                'currency' => 'PKR',
                'timezone' => 'Asia/Karachi',
                'status' => 'suspended',
                'enabled_portals' => ['school_admin', 'teacher'],
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $school->refresh();
        $this->assertSame('suspended', $school->status);
        $this->assertSame(['school_admin', 'teacher'], $school->enabled_portals);
        $this->assertSame('contact@example.test', $school->email);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'school_status_changed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'school_portals_changed',
        ]);
    }

    public function test_school_admin_cannot_change_school_status_or_portals(): void
    {
        $schoolAdmin = User::factory()->create();
        $school = School::create([
            'name' => 'Example Academy',
            'slug' => 'example-academy',
            'status' => 'active',
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'enabled_portals' => ['school_admin'],
        ]);

        $this->actingAs($schoolAdmin)
            ->post(route('super-admin.schools.status', $school), ['status' => 'suspended'])
            ->assertForbidden();

        $this->assertSame('active', $school->fresh()->status);
    }
}
