<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Queries\DashboardTaskQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with stats, the task list and the month calendar.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = now()->startOfDay();

        // Dashboard statistics (scoped to the signed-in user)
        $totalCount = $user->tasks()->count();
        $activeCount = $user->tasks()->active()->count();
        $completedCount = $user->tasks()->completed()->count();
        $todayCount = $user->tasks()->today()->active()->count();
        $overdueCount = $user->tasks()->active()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today->toDateString())
            ->count();
        $completedThisWeek = $user->tasks()->completed()
            ->where('updated_at', '>=', now()->startOfWeek())
            ->count();
        $progressPercentage = $totalCount > 0
            ? round(($completedCount / $totalCount) * 100)
            : 0;

        // Alias variables for flexibility in views
        $totalTasks = $totalCount;
        $activeTasks = $activeCount;
        $completedTasks = $completedCount;
        $todayTasks = $todayCount;

        $dashboardParameters = array_filter($request->query(), 'is_string');
        $taskQuery = new DashboardTaskQuery($user, $today, $dashboardParameters);
        $searchQuery = $taskQuery->search;
        $currentPriority = $taskQuery->priority;
        $currentFilter = $filter = $taskQuery->status;
        $currentDue = $taskQuery->deadline;
        $currentSort = $sort = $taskQuery->sort;
        $filteredTasks = $taskQuery->get();

        // Single-day filter (set by clicking a calendar day)
        $selectedDate = null;
        $rawDate = $dashboardParameters['date'] ?? '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate) && Carbon::canBeCreatedFromFormat($rawDate, 'Y-m-d')) {
            $selectedDate = Carbon::createFromFormat('Y-m-d', $rawDate)->startOfDay();
        }

        $tasks = $selectedDate
            ? $filteredTasks
                ->filter(fn (Task $task) => $task->due_date && $task->due_date->isSameDay($selectedDate))
                ->values()
            : $filteredTasks;

        // Calendar month (YYYY-MM, defaults to the current month)
        $rawMonth = $dashboardParameters['month'] ?? '';
        if (preg_match('/^\d{4}-\d{2}$/', $rawMonth) && Carbon::canBeCreatedFromFormat($rawMonth.'-01', 'Y-m-d')) {
            $calendarMonth = Carbon::createFromFormat('Y-m-d', $rawMonth.'-01')->startOfMonth();
        } else {
            $calendarMonth = now()->startOfMonth();
        }

        // Always render a stable six-week grid that starts on Sunday
        $calendarStart = $calendarMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $calendarStart->copy()->addDays(41);
        $startKey = $calendarStart->toDateString();
        $endKey = $calendarEnd->toDateString();

        $calendarTasks = $filteredTasks
            ->filter(function (Task $task) use ($startKey, $endKey) {
                if (! $task->due_date) {
                    return false;
                }

                $key = $task->due_date->toDateString();

                return $key >= $startKey && $key <= $endKey;
            })
            ->groupBy(fn (Task $task) => $task->due_date->toDateString());

        $calendarDays = [];
        $cursor = $calendarStart->copy();

        for ($i = 0; $i < 42; $i++) {
            $key = $cursor->toDateString();

            $calendarDays[] = [
                'date' => $cursor->copy(),
                'key' => $key,
                'in_month' => $cursor->month === $calendarMonth->month && $cursor->year === $calendarMonth->year,
                'is_today' => $cursor->isToday(),
                'is_selected' => $selectedDate && $cursor->isSameDay($selectedDate),
                'tasks' => $calendarTasks->get($key, collect()),
            ];

            $cursor->addDay();
        }

        $calendarTaskCount = $calendarTasks->sum(fn ($items) => $items->count());

        // Upcoming deadlines for the side panel (kept in sync with the active
        // filters so a filtered-out task never reappears in the sidebar)
        $upcomingTasks = $filteredTasks
            ->filter(fn (Task $task) => ! $task->is_completed
                && $task->due_date
                && $task->due_date->toDateString() >= $today->toDateString())
            ->sortBy(fn (Task $task) => $task->due_date->getTimestamp())
            ->take(5)
            ->values();

        return view('dashboard', compact(
            'tasks',
            'totalCount',
            'activeCount',
            'completedCount',
            'todayCount',
            'overdueCount',
            'completedThisWeek',
            'totalTasks',
            'activeTasks',
            'completedTasks',
            'todayTasks',
            'progressPercentage',
            'currentFilter',
            'filter',
            'currentSort',
            'sort',
            'currentPriority',
            'currentDue',
            'searchQuery',
            'dashboardParameters',
            'selectedDate',
            'calendarDays',
            'calendarMonth',
            'calendarTaskCount',
            'upcomingTasks',
        ));
    }
}
