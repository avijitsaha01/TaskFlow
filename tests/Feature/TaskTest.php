<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_dashboard_displays(): void
    {
        Task::factory()->count(3)->for($this->user)->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('My Tasks');
        $response->assertSeeText('Total tasks');
        $response->assertSeeText('Active');
        $response->assertSeeText('Completed');
        $response->assertSeeText('Progress');
    }

    public function test_dashboard_handles_a_user_without_tasks(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeText('0 tasks scheduled')
            ->assertSeeText('No tasks found')
            ->assertSeeText('No upcoming deadlines');
    }

    public function test_task_can_be_created(): void
    {
        $response = $this->post(route('tasks.store'), [
            'title' => 'Write the project report',
            'description' => 'Draft the final report.',
            'priority' => 'high',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'title' => 'Write the project report',
            'priority' => 'high',
            'status' => 'pending',
        ]);
    }

    public function test_task_creation_validates(): void
    {
        $response = $this->post(route('tasks.store'), [
            'title' => '',
            'priority' => 'urgent',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['title', 'priority', 'due_date']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_task_can_be_updated(): void
    {
        $task = Task::factory()->for($this->user)->create([
            'title' => 'Old title',
            'priority' => 'low',
        ]);

        $response = $this->put(route('tasks.update', $task), [
            'title' => 'New title',
            'description' => 'Updated description.',
            'priority' => 'high',
            'due_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New title',
            'priority' => 'high',
        ]);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_can_be_toggled(): void
    {
        $task = Task::factory()->for($this->user)->pending()->create();

        $this->patch(route('tasks.toggle', $task))->assertRedirect();
        $this->assertSame('completed', $task->fresh()->status);

        $this->patch(route('tasks.toggle', $task))->assertRedirect();
        $this->assertSame('pending', $task->fresh()->status);
    }

    public function test_search_filters_tasks(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'Buy groceries']);
        Task::factory()->for($this->user)->create(['title' => 'Fix the login bug']);

        $response = $this->get('/?search=groceries');

        $response->assertOk();
        $response->assertSeeText('Buy groceries');
        $response->assertDontSeeText('Fix the login bug');
    }

    public function test_priority_filter_works(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'High priority item', 'priority' => 'high']);
        Task::factory()->for($this->user)->create(['title' => 'Low priority item', 'priority' => 'low']);

        $response = $this->get('/?priority=high');

        $response->assertOk();
        $response->assertSeeText('High priority item');
        $response->assertDontSeeText('Low priority item');
    }

    public function test_task_show_and_edit_pages_display(): void
    {
        $task = Task::factory()->for($this->user)->create(['title' => 'Visible task']);

        $this->get(route('tasks.show', $task))->assertOk()->assertSeeText('Visible task');
        $this->get(route('tasks.edit', $task))->assertOk()->assertSee('Visible task');
    }

    public function test_calendar_renders_the_current_month_and_scheduled_tasks(): void
    {
        Task::factory()->for($this->user)->create([
            'title' => 'Calendar planning',
            'status' => 'pending',
            'due_date' => now()->toDateString(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeText(now()->format('F Y'))
            ->assertSeeText('Calendar planning')
            ->assertSeeText('scheduled');
    }

    public function test_dashboard_views_share_the_same_filtered_tasks(): void
    {
        $date = now()->addDay();
        $matching = Task::factory()->for($this->user)->create([
            'title' => 'Planning report',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => $date,
        ]);
        Task::factory()->for($this->user)->create([
            'title' => 'Planning archive',
            'priority' => 'low',
            'status' => 'pending',
            'due_date' => $date,
        ]);
        Task::factory()->for(User::factory())->create([
            'title' => 'Planning for another account',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => $date,
        ]);

        $response = $this->get('/?search=Planning&priority=high&filter=active&due=week&sort=due&month='.$date->format('Y-m'));

        $response->assertOk();
        $this->assertSame([$matching->id], $response->viewData('tasks')->modelKeys());
        $this->assertSame([$matching->id], $response->viewData('upcomingTasks')->modelKeys());
        $this->assertSame(1, $response->viewData('calendarTaskCount'));
        $this->assertSame(2, $response->viewData('totalCount'));
    }

    public function test_malformed_dashboard_query_values_use_defaults(): void
    {
        $this->get('/?search[]=word&priority[]=high&filter[]=active&due[]=week&sort[]=due&date[]=bad&month[]=bad')
            ->assertOk()
            ->assertViewHas('searchQuery', '')
            ->assertViewHas('currentPriority', 'all')
            ->assertViewHas('currentFilter', 'all')
            ->assertViewHas('currentSort', 'newest');
    }

    public function test_calendar_month_navigation_renders_the_requested_month(): void
    {
        $nextMonth = now()->copy()->addMonth();

        $this->get('/?month='.$nextMonth->format('Y-m'))
            ->assertOk()
            ->assertSeeText($nextMonth->format('F Y'));
    }

    public function test_overdue_filter_excludes_tasks_that_are_not_overdue(): void
    {
        Task::factory()->for($this->user)->create([
            'title' => 'Overdue report',
            'status' => 'pending',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        Task::factory()->for($this->user)->create([
            'title' => 'Future report',
            'status' => 'pending',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->get('/?due=overdue&filter=active')
            ->assertOk()
            ->assertSeeText('Overdue report')
            ->assertDontSeeText('Future report');
    }

    public function test_selecting_a_calendar_day_shows_that_days_tasks(): void
    {
        $day = now()->addDays(2)->startOfDay();

        Task::factory()->for($this->user)->create([
            'title' => 'Day-specific task',
            'due_date' => $day->toDateString(),
        ]);

        $this->get('/?date='.$day->format('Y-m-d'))
            ->assertOk()
            ->assertSeeText('Due '.$day->format('M j, Y'))
            ->assertSee('name="date" value="'.$day->toDateString().'"', false)
            ->assertSeeText('Day-specific task');
    }

    public function test_search_accepts_zero_and_orders_tasks_with_equal_timestamps(): void
    {
        $createdAt = now()->subMinute();
        $first = Task::factory()->for($this->user)->create(['title' => 'Chapter 0 draft', 'created_at' => $createdAt]);
        $second = Task::factory()->for($this->user)->create(['title' => 'Chapter 0 notes', 'created_at' => $createdAt]);
        Task::factory()->for($this->user)->create(['title' => 'Skip this chapter', 'description' => null]);

        $response = $this->get('/?search=0&sort=newest')->assertOk();

        $this->assertSame([$second->id, $first->id], $response->viewData('tasks')->modelKeys());
    }
}
