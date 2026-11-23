<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'username' => 'erin',
            'email' => 'erin@example.com',
            'phone' => '+15551230020',
            'password' => 'secret-password',
        ]);
        $this->actingAs($this->user);
    }

    public function test_guest_cannot_open_settings(): void
    {
        auth()->logout();

        $this->get(route('settings.edit'))->assertRedirect(route('login'));
    }

    public function test_settings_page_displays(): void
    {
        Task::factory()->for($this->user)->count(2)->create();

        $this->get(route('settings.edit'))
            ->assertOk()
            ->assertSeeText('Profile')
            ->assertSeeText('Password')
            ->assertSeeText('Dashboard defaults')
            ->assertSeeText('Delete account');
    }

    public function test_profile_can_be_updated(): void
    {
        $response = $this->from(route('settings.edit'))->put(route('settings.profile'), [
            'username' => 'erin_new',
            'email' => 'ERIN.NEW@Example.com',
            'phone' => '(555) 123-0099',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'username' => 'erin_new',
            'name' => 'erin_new',
            'email' => 'erin.new@example.com',
            'phone' => '5551230099',
        ]);
    }

    public function test_profile_rejects_other_users_identifiers(): void
    {
        User::factory()->create([
            'username' => 'taken',
            'email' => 'taken@example.com',
            'phone' => '+15559990000',
        ]);

        $response = $this->from(route('settings.edit'))->put(route('settings.profile'), [
            'username' => 'taken',
            'email' => 'taken@example.com',
            'phone' => '+15559990000',
        ]);

        $response->assertSessionHasErrors(['username', 'email', 'phone']);
        $this->assertSame('erin', $this->user->fresh()->username);
    }

    public function test_password_update_requires_correct_current_password(): void
    {
        $this->from(route('settings.edit'))->put(route('settings.password'), [
            'current_password' => 'wrong-password',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('secret-password', $this->user->fresh()->password));
    }

    public function test_password_can_be_updated(): void
    {
        $this->from(route('settings.edit'))->put(route('settings.password'), [
            'current_password' => 'secret-password',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertSessionHas('success');

        $this->assertTrue(Hash::check('brand-new-password', $this->user->fresh()->password));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_new_password_must_differ_from_current_one(): void
    {
        $this->from(route('settings.edit'))->put(route('settings.password'), [
            'current_password' => 'secret-password',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasErrors('password');
    }

    public function test_dashboard_defaults_are_applied_and_overridable(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'Active item', 'status' => 'pending']);
        Task::factory()->for($this->user)->create(['title' => 'Completed item', 'status' => 'completed']);

        $this->from(route('settings.edit'))->put(route('settings.preferences'), [
            'default_filter' => 'active',
            'default_sort' => 'priority',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'default_filter' => 'active',
            'default_sort' => 'priority',
        ]);

        $this->user->refresh();

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Active item')
            ->assertDontSeeText('Completed item')
            ->assertSee('value="priority" selected', false);

        $this->get('/?filter=all')
            ->assertOk()
            ->assertSeeText('Active item')
            ->assertSeeText('Completed item');
    }

    public function test_preferences_reject_invalid_values(): void
    {
        $this->from(route('settings.edit'))->put(route('settings.preferences'), [
            'default_filter' => 'archived',
            'default_sort' => 'random',
        ])->assertSessionHasErrors(['default_filter', 'default_sort']);
    }

    public function test_account_cannot_be_deleted_without_correct_password(): void
    {
        $this->from(route('settings.edit'))->delete(route('settings.destroy'), [
            'confirm_password' => 'wrong-password',
        ])->assertSessionHasErrors('confirm_password');

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_account_deletion_removes_user_and_tasks(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $this->delete(route('settings.destroy'), ['confirm_password' => 'secret-password'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}

