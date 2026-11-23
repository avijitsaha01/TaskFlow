<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Design landing page mockup',
            'Set up CI/CD pipeline',
            'Write unit tests for auth module',
            'Review pull request #42',
            'Update API documentation',
            'Fix navigation bug on mobile',
            'Implement user profile page',
            'Optimize database queries',
            'Create onboarding email sequence',
            'Refactor payment gateway integration',
            'Add dark mode support',
            'Migrate legacy data to new schema',
            'Set up error monitoring with Sentry',
            'Design system component library',
            'Write blog post about new features',
            'Configure staging environment',
            'Implement search functionality',
            'Create data export feature',
            'Update privacy policy page',
            'Performance audit for homepage',
            'Set up automated backups',
            'Build admin dashboard widgets',
            'Integrate third-party analytics',
            'Create user feedback survey',
            'Fix timezone handling issues',
            'Implement rate limiting on API',
            'Design notification system',
            'Write migration scripts',
            'Update dependencies to latest',
            'Create README for open source repo',
        ];

        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement($titles),
            'description' => fake()->optional(0.8)->paragraph(rand(1, 3)),
            'status' => fake()->randomElement(['pending', 'pending', 'in_progress', 'in_progress', 'completed']),
            'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high']),
            'due_date' => fake()->optional(0.7)->dateTimeBetween('-3 days', '+14 days'),
        ];
    }

    /**
     * Indicate the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate the task is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate the task is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }
}
