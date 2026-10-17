<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchoolControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_school_sets_the_admin_password_to_the_generated_slug_without_an_invitation(): void
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
        ]);
        $response->assertSessionHasNoErrors();

        $school = School::query()->where('email', 'admin@example.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $response
            ->assertRedirect(route('super-admin.schools.show', $school))
            ->assertSessionHas('initial_admin_email', 'admin@example.test')
            ->assertSessionHas('initial_admin_password', $school->slug);

        $this->assertNotSame($school->slug, $admin->password);
        $this->assertTrue(Hash::check($school->slug, $admin->password));
        $this->assertDatabaseHas('school_user', [
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'role' => 'school_admin',
            'status' => 'active',
        ]);
        $this->assertDatabaseMissing('school_invitations', [
            'school_id' => $school->id,
        ]);

        $this->get(route('super-admin.schools.show', $school))
            ->assertOk()
            ->assertSee('Initial School Admin Login Credentials')
            ->assertSee($school->slug)
            ->assertDontSee('Invitation Link');

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.post'), [
            'email' => 'admin@example.test',
            'password' => $school->slug,
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
        ]);

        $response->assertSessionHasErrors('admin_email');
        $this->assertDatabaseMissing('schools', ['name' => 'Example Academy']);
    }
}
