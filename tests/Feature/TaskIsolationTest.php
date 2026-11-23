<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_only_see_their_own_tasks(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        Task::factory()->for($alice)->create(['title' => 'Alice private task']);
        Task::factory()->for($bob)->create(['title' => 'Bob private task']);

        $this->actingAs($alice)->get('/')
            ->assertOk()
            ->assertSeeText('Alice private task')
            ->assertDontSeeText('Bob private task');

        $this->actingAs($alice)->get('/?search=Bob')
            ->assertOk()
            ->assertDontSeeText('Bob private task');
    }

    public function test_users_cannot_view_or_change_another_users_task(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $bobTask = Task::factory()->for($bob)->create(['title' => 'Bob private task']);

        $this->actingAs($alice);

        $this->get(route('tasks.show', $bobTask))->assertNotFound();
        $this->get(route('tasks.edit', $bobTask))->assertNotFound();

        $this->put(route('tasks.update', $bobTask), [
            'title' => 'Hijacked',
            'priority' => 'low',
        ])->assertNotFound();
        $this->put(route('tasks.update', $bobTask), [])->assertNotFound();

        $this->delete(route('tasks.destroy', $bobTask))->assertNotFound();
        $this->patch(route('tasks.toggle', $bobTask))->assertNotFound();

        $this->assertDatabaseHas('tasks', [
            'id' => $bobTask->id,
            'title' => 'Bob private task',
            'status' => $bobTask->status,
        ]);
    }
}
