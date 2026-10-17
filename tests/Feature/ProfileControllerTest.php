<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public static function schoolRoles(): array
    {
        return [
            'school admin' => ['school_admin'],
            'principal' => ['principal'],
            'academic coordinator' => ['academic_coordinator'],
            'accountant' => ['accountant'],
            'teacher' => ['teacher'],
            'student' => ['student'],
            'staff' => ['staff'],
            'parent' => ['parent'],
        ];
    }

    #[DataProvider('schoolRoles')]
    public function test_school_members_can_access_their_own_profile_for_each_role(string $role): void
    {
        $user = User::factory()->create();
        $this->addSchoolRole($user, $role);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertViewIs('profile.show')
            ->assertSee(\Illuminate\Support\Str::headline($role));
    }

    public function test_super_admin_can_access_their_own_profile(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_super_admin' => true])->save();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Super Admin');
    }

    public function test_unauthenticated_users_are_redirected_from_the_profile(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));

        $this->get(route('profile.photo'))
            ->assertRedirect(route('login'));
    }

    public function test_profile_form_targets_the_authenticated_update_route_with_put_and_csrf(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('action="'.route('profile.update').'"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_profile_updates_only_the_signed_in_users_permitted_fields(): void
    {
        $user = User::factory()->create([
            'password' => 'current-password',
            'email_verified_at' => now(),
        ]);
        $otherUser = User::factory()->create([
            'name' => 'Other User',
            'email' => 'other@example.test',
        ]);
        $userSchoolId = $this->addSchoolRole($user, 'teacher');
        $otherSchoolId = $this->addSchoolRole($otherUser, 'school_admin');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.test',
                'password' => 'attacker-password',
                'status' => 'suspended',
                'is_super_admin' => true,
                'role' => 'school_admin',
                'school_id' => $otherSchoolId,
                'user_id' => $otherUser->id,
                'avatar_path' => 'profile-photos/another-users-photo.png',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Profile updated successfully.');

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->isSuperAdmin());
        $this->assertSame('active', $user->status);
        $this->assertNull($user->avatar_path);
        $this->assertTrue(Hash::check('current-password', $user->password));
        $this->assertSame('Other User', $otherUser->fresh()->name);
        $this->assertSame('other@example.test', $otherUser->fresh()->email);
        $this->assertDatabaseHas('school_user', [
            'school_id' => $userSchoolId,
            'user_id' => $user->id,
            'role' => 'teacher',
        ]);
        $this->assertDatabaseHas('school_user', [
            'school_id' => $otherSchoolId,
            'user_id' => $otherUser->id,
            'role' => 'school_admin',
        ]);

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('value="Updated Name"', false)
            ->assertSee('value="updated@example.test"', false);
    }

    public function test_profile_rejects_an_email_address_used_by_another_account(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'already-used@example.test']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'already-used@example.test',
            ])
            ->assertSessionHasErrors([
                'email' => 'The email has already been taken.',
            ]);

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_profile_photo_is_stored_and_previous_photo_removed(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $previousPhoto = 'profile-photos/previous.jpg';
        Storage::disk('local')->put($previousPhoto, 'old image');
        $user->forceFill(['avatar_path' => $previousPhoto])->save();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'photo' => UploadedFile::fake()->createWithContent(
                    'avatar.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/X5sAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertNotSame($previousPhoto, $user->avatar_path);
        $this->assertTrue(Storage::disk('local')->exists($user->avatar_path));
        $this->assertFalse(Storage::disk('local')->exists($previousPhoto));
        $this->get(route('profile.photo'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_profile_rejects_non_image_uploads(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'photo' => UploadedFile::fake()->create('avatar.svg', 50, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->fresh()->avatar_path);
        $this->assertSame([], Storage::disk('local')->allFiles('profile-photos'));
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'correct-current-password']);

        $this->actingAs($user)
            ->from(route('profile.password'))
            ->post(route('profile.password.update'), [
                'current_password' => 'incorrect-current-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('correct-current-password', $user->fresh()->password));
    }

    public function test_password_change_rejects_a_mismatched_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'correct-current-password']);

        $this->actingAs($user)
            ->from(route('profile.password'))
            ->post(route('profile.password.update'), [
                'current_password' => 'correct-current-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'different-secure-password',
            ])
            ->assertSessionHasErrors([
                'password' => 'The password field confirmation does not match.',
            ]);

        $this->assertTrue(Hash::check('correct-current-password', $user->fresh()->password));
    }

    public function test_password_change_rejects_passwords_shorter_than_eight_characters(): void
    {
        $user = User::factory()->create(['password' => 'correct-current-password']);

        $this->actingAs($user)
            ->from(route('profile.password'))
            ->post(route('profile.password.update'), [
                'current_password' => 'correct-current-password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors([
                'password' => 'The password field must be at least 8 characters.',
            ]);

        $this->assertTrue(Hash::check('correct-current-password', $user->fresh()->password));
    }

    public function test_password_change_saves_the_new_password_hashed(): void
    {
        $user = User::factory()->create(['password' => 'correct-current-password']);

        $this->actingAs($user)
            ->from(route('profile.password'))
            ->post(route('profile.password.update'), [
                'current_password' => 'correct-current-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('profile.password'))
            ->assertSessionHas('status', 'Password updated successfully.');

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    private function addSchoolRole(User $user, string $role): int
    {
        $now = now();
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Member School',
            'slug' => 'member-school-'.$user->id,
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

        return $schoolId;
    }
}
