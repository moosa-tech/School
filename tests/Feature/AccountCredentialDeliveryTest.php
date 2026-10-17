<?php

namespace Tests\Feature;

use App\Exceptions\WhatsAppRateLimitException;
use App\Jobs\SendWhatsAppCredential;
use App\Models\User;
use App\Models\WhatsAppCredentialDelivery;
use App\Services\WhatsAppCredentialDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountCredentialDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_staff_account_gets_a_random_hashed_password_and_one_time_reveal(): void
    {
        Queue::fake([SendWhatsAppCredential::class]);
        $this->configureWhatsApp();
        [$administrator, $schoolId] = $this->createSchoolUser('school_admin');

        $response = $this->actingAs($administrator)
            ->withSession(['current_school_id' => $schoolId])
            ->post(route('school-admin.employees.store'), [
                'employee_number' => 'EMP-TEST-001',
                'name' => 'Staff One',
                'email' => 'staff-one@example.test',
                'phone' => '+15551234567',
                'job_title' => 'Academic Coordinator',
                'role' => 'staff',
                'salary' => 45000,
                'joining_date' => now()->toDateString(),
                'give_system_access' => '1',
                'whatsapp_opt_in' => 'yes',
            ]);

        $response->assertRedirect(route('school-admin.employees.index'));
        $account = User::where('email', 'staff-one@example.test')->firstOrFail();
        $this->assertTrue($account->must_change_password);
        $this->assertNotSame('password123', $account->password);

        $confirmation = $this->get(route('school-admin.employees.index'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('One-time staff login credentials')
            ->assertSee('WhatsApp credential message queued; delivery is not yet confirmed.');
        $this->assertSame(1, preg_match('/Temporary password: <code[^>]*>([^<]+)<\/code>/', $confirmation->getContent(), $matches));
        $temporaryPassword = $matches[1];
        $this->assertTrue(Hash::check($temporaryPassword, $account->fresh()->password));

        Queue::assertPushed(SendWhatsAppCredential::class, function (SendWhatsAppCredential $job) use ($temporaryPassword): bool {
            return $job->deliveryId > 0 && ! str_contains(serialize($job), $temporaryPassword);
        });

        $ciphertext = DB::table('whatsapp_credential_deliveries')->value('temporary_password');
        $this->assertIsString($ciphertext);
        $this->assertNotSame($temporaryPassword, $ciphertext);
        $this->assertStringNotContainsString($temporaryPassword, $ciphertext);

        $this->get(route('school-admin.employees.index'))
            ->assertOk()
            ->assertDontSee('One-time staff login credentials');
    }

    public function test_principal_cannot_create_another_principal_account(): void
    {
        [$principal, $schoolId] = $this->createSchoolUser('principal');

        $this->actingAs($principal)
            ->withSession(['current_school_id' => $schoolId])
            ->post(route('school-admin.employees.store'), [
                'employee_number' => 'EMP-PRINCIPAL-2',
                'name' => 'Second Principal',
                'email' => 'second-principal@example.test',
                'job_title' => 'Principal',
                'role' => 'principal',
                'salary' => 45000,
                'joining_date' => now()->toDateString(),
                'give_system_access' => '1',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('employees', ['email' => 'second-principal@example.test']);
    }

    public function test_missing_whatsapp_setup_is_reported_without_queueing_a_message(): void
    {
        Queue::fake([SendWhatsAppCredential::class]);
        Config::set('services.whatsapp_cloud.access_token', null);
        Config::set('services.whatsapp_cloud.phone_number_id', null);
        Config::set('services.whatsapp_cloud.api_version', null);
        Config::set('services.whatsapp_cloud.template_name', null);
        [$administrator, $schoolId] = $this->createSchoolUser('school_admin');

        $this->actingAs($administrator)
            ->withSession(['current_school_id' => $schoolId])
            ->post(route('school-admin.employees.store'), [
                'employee_number' => 'EMP-MISSING-WA',
                'name' => 'No WhatsApp Setup',
                'email' => 'no-whatsapp@example.test',
                'phone' => '+15551234567',
                'job_title' => 'Teacher',
                'role' => 'teacher',
                'salary' => 45000,
                'joining_date' => now()->toDateString(),
                'give_system_access' => '1',
                'whatsapp_opt_in' => 'yes',
            ])
            ->assertRedirect(route('school-admin.employees.index'));

        $this->get(route('school-admin.employees.index'))
            ->assertOk()
            ->assertSee('WHATSAPP_CLOUD_API_TOKEN')
            ->assertSee('WHATSAPP_PHONE_NUMBER_ID')
            ->assertSee('WHATSAPP_GRAPH_API_VERSION')
            ->assertSee('WHATSAPP_CREDENTIAL_TEMPLATE');

        $this->assertDatabaseCount('whatsapp_credential_deliveries', 0);
        Queue::assertNotPushed(SendWhatsAppCredential::class);
    }

    public function test_temporary_password_must_be_changed_before_portal_access(): void
    {
        [$user, $schoolId] = $this->createSchoolUser('student', 'first-login@example.test');
        $user->forceFill([
            'password' => 'generated-temporary-password',
            'must_change_password' => true,
        ])->save();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'generated-temporary-password',
        ])->assertRedirect(route('profile.password'));

        $this->get(route('student.dashboard'))->assertRedirect(route('profile.password'));
        $this->get(route('profile.password'))
            ->assertOk()
            ->assertSee('Choose a new password to continue.');

        $this->post(route('profile.password.update'), [
            'current_password' => 'generated-temporary-password',
            'password' => 'permanent-user-password',
            'password_confirmation' => 'permanent-user-password',
        ])->assertRedirect(route('profile.password'));

        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('permanent-user-password', $user->fresh()->password));
        $this->get(route('student.dashboard'))->assertOk();

        $this->assertDatabaseHas('school_user', [
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'role' => 'student',
        ]);
    }

    public function test_cloud_api_acceptance_is_recorded_as_sent_then_webhook_can_confirm_delivery(): void
    {
        $this->configureWhatsApp();
        Http::preventStrayRequests();
        Http::fake([
            'https://graph.facebook.com/v42.0/123456789/messages' => Http::response([
                'messages' => [['id' => 'wamid.test.accepted']],
            ]),
        ]);
        $delivery = $this->createDelivery();
        $ciphertext = DB::table('whatsapp_credential_deliveries')
            ->where('id', $delivery->id)
            ->value('temporary_password');

        app(WhatsAppCredentialDeliveryService::class)->send($delivery->id);

        $delivery->refresh();
        $this->assertSame('sent', $delivery->status);
        $this->assertSame('wamid.test.accepted', $delivery->provider_message_id);
        $this->assertNull($delivery->temporary_password);
        $this->assertNotSame('Temporary-Pass-Secret', $ciphertext);

        Http::assertSent(function (ClientRequest $request): bool {
            return $request->url() === 'https://graph.facebook.com/v42.0/123456789/messages'
                && $request['messaging_product'] === 'whatsapp'
                && $request['to'] === '15551234567'
                && $request['template']['name'] === 'account_credentials'
                && $request['template']['components'][0]['parameters'][2]['text'] === 'Temporary-Pass-Secret';
        });

        $this->setWebhookConfiguration();
        $body = json_encode([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'statuses' => [[
                            'id' => 'wamid.test.accepted',
                            'status' => 'delivered',
                        ]],
                    ],
                ]],
            ]],
        ], JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $body, 'test-webhook-app-secret');

        $this->call(
            'POST',
            route('webhooks.whatsapp.receive'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => $signature],
            $body
        )->assertNoContent();

        $this->assertSame('delivered', $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->delivered_at);
    }

    public function test_429_provider_response_retries_only_the_explicitly_rejected_attempts(): void
    {
        $this->configureWhatsApp();
        Http::preventStrayRequests();
        Http::fake([
            'https://graph.facebook.com/v42.0/123456789/messages' => Http::sequence()
                ->push(['error' => ['message' => 'rate limited']], 429)
                ->push(['error' => ['message' => 'rate limited']], 429)
                ->push(['error' => ['message' => 'rate limited']], 429),
        ]);
        $delivery = $this->createDelivery();
        $service = app(WhatsAppCredentialDeliveryService::class);

        try {
            $service->send($delivery->id);
            $this->fail('A rate-limited request must be released for a safe retry.');
        } catch (WhatsAppRateLimitException) {
            $this->assertSame('pending', $delivery->fresh()->status);
            $this->assertSame(1, $delivery->fresh()->attempts);
        }

        try {
            $service->send($delivery->id);
            $this->fail('The second rate-limited request must be released for a safe retry.');
        } catch (WhatsAppRateLimitException) {
            $this->assertSame('pending', $delivery->fresh()->status);
            $this->assertSame(2, $delivery->fresh()->attempts);
        }

        $service->send($delivery->id);

        $this->assertSame('failed', $delivery->fresh()->status);
        $this->assertSame(3, $delivery->fresh()->attempts);
        $this->assertNull($delivery->fresh()->temporary_password);
    }

    public function test_provider_rejection_marks_delivery_failed_and_clears_the_credential(): void
    {
        $this->configureWhatsApp();
        Http::preventStrayRequests();
        Http::fake([
            'https://graph.facebook.com/v42.0/123456789/messages' => Http::response([
                'error' => ['message' => 'template rejected'],
            ], 400),
        ]);
        $delivery = $this->createDelivery();

        app(WhatsAppCredentialDeliveryService::class)->send($delivery->id);

        $this->assertSame('failed', $delivery->fresh()->status);
        $this->assertNull($delivery->fresh()->temporary_password);
        $this->assertSame(1, $delivery->fresh()->attempts);
    }

    public function test_webhook_requires_a_valid_provider_signature(): void
    {
        $this->setWebhookConfiguration();

        $this->call(
            'POST',
            route('webhooks.whatsapp.receive'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => 'sha256=invalid'],
            '{"entry":[]}'
        )->assertForbidden();
    }

    /**
     * @return array{0: User, 1: int}
     */
    private function createSchoolUser(string $role, string $email = 'administrator@example.test'): array
    {
        $user = User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => 'initial-password',
            'status' => 'active',
        ]);
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Credential Test School',
            'slug' => 'credential-test-school-'.$role,
            'status' => 'active',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'created_at' => now(),
            'updated_at' => now(),
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
        $planId = DB::table('plans')->insertGetId([
            'name' => 'Credential Test Plan',
            'price' => 0,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'student_limit' => 500,
            'staff_limit' => 50,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('subscriptions')->insert([
            'school_id' => $schoolId,
            'plan_id' => $planId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $schoolId];
    }

    private function createDelivery(): WhatsAppCredentialDelivery
    {
        [$user, $schoolId] = $this->createSchoolUser('student', 'delivery-user@example.test');

        return WhatsAppCredentialDelivery::create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'recipient_number' => '+15551234567',
            'username' => $user->email,
            'temporary_password' => 'Temporary-Pass-Secret',
            'status' => 'pending',
            'expires_at' => now()->addHour(),
        ]);
    }

    private function configureWhatsApp(): void
    {
        Config::set('services.whatsapp_cloud.access_token', 'test-cloud-token');
        Config::set('services.whatsapp_cloud.phone_number_id', '123456789');
        Config::set('services.whatsapp_cloud.api_version', 'v42.0');
        Config::set('services.whatsapp_cloud.template_name', 'account_credentials');
        Config::set('services.whatsapp_cloud.template_language', 'en');
        Config::set('app.url', 'https://school.example.test');
    }

    private function setWebhookConfiguration(): void
    {
        Config::set('services.whatsapp_cloud.webhook_verify_token', 'test-webhook-verify-token');
        Config::set('services.whatsapp_cloud.app_secret', 'test-webhook-app-secret');
    }
}
