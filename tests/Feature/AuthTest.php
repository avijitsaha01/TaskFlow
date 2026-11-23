<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_user_can_register_and_gets_own_space(): void
    {
        $response = $this->post(route('register'), [
            'username' => 'alice',
            'email' => 'alice@example.com',
            'phone' => '+15551230001',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'username' => 'alice',
            'email' => 'alice@example.com',
            'phone' => '+15551230001',
        ]);

        $this->get('/')->assertOk()->assertSeeText('alice');
    }

    public function test_registration_validates_unique_fields_and_password_confirmation(): void
    {
        User::factory()->create([
            'username' => 'taken',
            'email' => 'taken@example.com',
            'phone' => '+15559990000',
        ]);

        $response = $this->post(route('register'), [
            'username' => 'taken',
            'email' => 'taken@example.com',
            'phone' => '+15559990000',
            'password' => 'secret-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['username', 'email', 'phone', 'password']);
        $this->assertGuest();
    }

    public function test_user_can_login_with_email_username_or_phone(): void
    {
        $user = User::factory()->create([
            'username' => 'bob',
            'email' => 'bob@example.com',
            'phone' => '+15551230002',
            'password' => 'secret-password',
        ]);

        foreach (['bob@example.com', 'bob', '+15551230002'] as $identifier) {
            $this->post(route('logout'));

            $this->post(route('login'), [
                'login' => $identifier,
                'password' => 'secret-password',
            ])->assertRedirect(route('dashboard'));

            $this->assertAuthenticatedAs($user);
        }
    }

    public function test_login_rejects_bad_credentials(): void
    {
        User::factory()->create([
            'email' => 'carol@example.com',
            'password' => 'secret-password',
        ]);

        $this->post(route('login'), [
            'login' => 'carol@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_password_is_hashed_on_registration(): void
    {
        $this->post(route('register'), [
            'username' => 'hashed',
            'email' => 'hashed@example.com',
            'phone' => '+15551230003',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $user = User::where('username', 'hashed')->firstOrFail();

        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }
}
