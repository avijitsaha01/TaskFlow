<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('email', 'demo@taskflow.test')->firstOrFail();

        // Create specific realistic tasks for a good demo experience
        $tasks = [
            [
                'title' => 'Design landing page mockup',
                'description' => 'Create a high-fidelity mockup for the new landing page using Figma. Include hero section, features grid, testimonials, and CTA.',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Set up CI/CD pipeline',
                'description' => 'Configure GitHub Actions for automated testing and deployment to staging and production environments.',
                'status' => 'completed',
                'priority' => 'high',
                'due_date' => now()->subDays(1)->toDateString(),
            ],
            [
                'title' => 'Write unit tests for auth module',
                'description' => 'Cover login, registration, password reset, and email verification flows with comprehensive unit tests.',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'title' => 'Review pull request #42',
                'description' => 'Review the refactoring PR for the notification service. Check for breaking changes and test coverage.',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => now()->toDateString(),
            ],
            [
                'title' => 'Update API documentation',
                'description' => 'Document the new v2 endpoints including request/response schemas and authentication requirements.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(5)->toDateString(),
            ],
            [
                'title' => 'Fix navigation bug on mobile',
                'description' => 'The hamburger menu doesn\'t close after selecting a menu item on iOS Safari. Investigate and fix.',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->addDay()->toDateString(),
            ],
            [
                'title' => 'Implement user profile page',
                'description' => 'Build the user profile page with avatar upload, bio editing, and social links management.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'Optimize database queries',
                'description' => 'Identify and optimize N+1 queries on the dashboard and reports pages. Add eager loading where needed.',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => now()->addDays(4)->toDateString(),
            ],
            [
                'title' => 'Create onboarding email sequence',
                'description' => 'Design and implement a 5-email welcome sequence for new users covering key features and tips.',
                'status' => 'completed',
                'priority' => 'low',
                'due_date' => now()->subDays(3)->toDateString(),
            ],
            [
                'title' => 'Refactor payment gateway integration',
                'description' => 'Extract payment logic into a dedicated service class and add support for multiple payment providers.',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addDays(6)->toDateString(),
            ],
            [
                'title' => 'Add dark mode support',
                'description' => 'Implement system-preference-aware dark mode with manual toggle. Update all component styles.',
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => now()->addDays(10)->toDateString(),
            ],
            [
                'title' => 'Migrate legacy data to new schema',
                'description' => 'Write migration scripts to transform and move data from the old MySQL database to the new PostgreSQL schema.',
                'status' => 'completed',
                'priority' => 'high',
                'due_date' => now()->subDays(2)->toDateString(),
            ],
            [
                'title' => 'Set up error monitoring with Sentry',
                'description' => 'Integrate Sentry for real-time error tracking and configure alert rules for critical errors.',
                'status' => 'completed',
                'priority' => 'medium',
                'due_date' => now()->subDays(4)->toDateString(),
            ],
            [
                'title' => 'Design system component library',
                'description' => 'Create reusable UI components: buttons, inputs, cards, modals, and dropdowns with consistent styling.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(8)->toDateString(),
            ],
            [
                'title' => 'Write blog post about new features',
                'description' => 'Draft a blog post announcing the Q4 feature releases including screenshots and usage examples.',
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => now()->addDays(12)->toDateString(),
            ],
            [
                'title' => 'Configure staging environment',
                'description' => 'Set up a staging server that mirrors production. Configure environment variables and SSL certificates.',
                'status' => 'completed',
                'priority' => 'medium',
                'due_date' => now()->subDays(5)->toDateString(),
            ],
            [
                'title' => 'Implement search functionality',
                'description' => 'Add full-text search across tasks with filtering by status, priority, and date range.',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => now()->addDays(5)->toDateString(),
            ],
            [
                'title' => 'Create data export feature',
                'description' => 'Allow users to export their tasks as CSV or PDF with customizable date ranges and filters.',
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => now()->addDays(14)->toDateString(),
            ],
            [
                'title' => 'Update privacy policy page',
                'description' => 'Review and update the privacy policy to comply with GDPR and CCPA requirements.',
                'status' => 'completed',
                'priority' => 'medium',
                'due_date' => now()->subDays(6)->toDateString(),
            ],
            [
                'title' => 'Performance audit for homepage',
                'description' => 'Run Lighthouse audit and optimize Core Web Vitals. Target LCP under 2.5s and CLS under 0.1.',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'title' => 'Set up automated backups',
                'description' => 'Configure daily automated database backups to S3 with 30-day retention policy.',
                'status' => 'completed',
                'priority' => 'high',
                'due_date' => now()->subDays(7)->toDateString(),
            ],
            [
                'title' => 'Build admin dashboard widgets',
                'description' => 'Create dashboard widgets showing user growth, revenue metrics, and system health indicators.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(9)->toDateString(),
            ],
            [
                'title' => 'Integrate third-party analytics',
                'description' => 'Set up Mixpanel for product analytics and configure key event tracking throughout the app.',
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => now()->addDays(11)->toDateString(),
            ],
            [
                'title' => 'Create user feedback survey',
                'description' => 'Design and implement an in-app NPS survey that triggers after the user completes their 10th task.',
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => null,
            ],
            [
                'title' => 'Fix timezone handling issues',
                'description' => 'Ensure all dates and times are stored in UTC and correctly converted to the user\'s local timezone for display.',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => now()->toDateString(),
            ],
        ];

        foreach ($tasks as $task) {
            $owner->tasks()->create($task);
        }
    }
}
