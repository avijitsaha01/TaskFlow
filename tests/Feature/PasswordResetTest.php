<?php

namespace Tests\Feature;

use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.demo_password_reset' => true]);

        $this->user = User::factory()->create([
            'username' => 'dana',
            'email' => 'dana@example.com',
            'phone' => '+15551230009',
            'password' => 'old-password',
        ]);
    }

    public function test_password_reset_demo_requires_opt_in(): void
    {
        config(['auth.demo_password_reset' => false]);

        $this->get(route('login'))->assertDontSeeText('Forgot password?');
        $this->get(route('password.request'))->assertNotFound();
        $this->post(route('password.send'), ['identifier' => 'dana@example.com'])
            ->assertNotFound();
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    public function test_password_reset_demo_is_blocked_in_production(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->app->instance('env', 'production');

        $this->get(route('login'))->assertDontSeeText('Forgot password?');
        $this->post(route('password.send'), ['identifier' => 'dana@example.com'])
            ->assertNotFound();
    }

    public function test_otp_can_be_requested_by_email_username_or_phone(): void
    {
        foreach (['dana@example.com', 'dana', '+15551230009'] as $identifier) {
            $response = $this->post(route('password.send'), ['identifier' => $identifier]);

            $response->assertRedirect(route('password.verify.form'));
            $response->assertSessionHas('otp_code');

            $code = $response->getSession()->get('otp_code');
            $this->assertMatchesRegularExpression('/^[0-9]{6}$/', (string) $code);

            $this->assertDatabaseHas('password_reset_otps', ['user_id' => $this->user->id]);
        }
    }

    public function test_unknown_account_cannot_request_otp(): void
    {
        $this->post(route('password.send'), ['identifier' => 'nobody@example.com'])
            ->assertSessionHasErrors('identifier');

        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    public function test_otp_is_shown_on_the_verification_screen(): void
    {
        $response = $this->post(route('password.send'), ['identifier' => 'dana@example.com']);
        $code = $response->getSession()->get('otp_code');

        $this->get(route('password.verify.form'))
            ->assertOk()
            ->assertSeeText('Verification code')
            ->assertSee($code);
    }

    public function test_wrong_otp_is_rejected(): void
    {
        $this->post(route('password.send'), ['identifier' => 'dana@example.com']);

        $this->post(route('password.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertNull(session('password_reset_verified_user_id'));
    }

    public function test_correct_otp_allows_password_reset(): void
    {
        $response = $this->post(route('password.send'), ['identifier' => 'dana@example.com']);
        $code = $response->getSession()->get('otp_code');

        $this->post(route('password.verify'), ['code' => $code])
            ->assertRedirect(route('password.reset.form'));

        $this->get(route('password.reset.form'))->assertOk();

        $this->post(route('password.reset'), [
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('brand-new-password', $this->user->fresh()->password));
        $this->assertDatabaseCount('password_reset_otps', 0);

        $this->post(route('login'), [
            'login' => 'dana@example.com',
            'password' => 'brand-new-password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_otp_cannot_be_reused(): void
    {
        $response = $this->post(route('password.send'), ['identifier' => 'dana@example.com']);
        $code = $response->getSession()->get('otp_code');

        $this->post(route('password.verify'), ['code' => $code]);

        $this->withSession(['password_reset_user_id' => $this->user->id])
            ->post(route('password.verify'), ['code' => $code])
            ->assertSessionHasErrors('code');
    }

    public function test_reset_requires_verified_otp(): void
    {
        $this->post(route('password.reset'), [
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertRedirect(route('password.request'));
    }

    public function test_expired_otp_is_rejected(): void
    {
        PasswordResetOtp::create([
            'user_id' => $this->user->id,
            'code' => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
        ]);

        $this->withSession(['password_reset_user_id' => $this->user->id])
            ->post(route('password.verify'), ['code' => '123456'])
            ->assertSessionHasErrors('code');
    }
}
