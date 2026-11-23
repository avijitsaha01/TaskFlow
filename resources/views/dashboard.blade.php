@extends('layouts.app')

@section('content')
    @php
        $calendarBase = Arr::except($dashboardParameters, ['month', 'date', 'page']);
        $prevMonth = $calendarMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $calendarMonth->copy()->addMonth()->format('Y-m');
        $monthName = $calendarMonth->format('F Y');
    @endphp

    <header class="dash-head">
        <div>
            <div class="eyebrow">{{ now()->format('l, F j, Y') }}</div>
            <h1>Hi, {{ auth()->user()->username }}</h1>
        </div>

        <div class="dash-head-actions">
            <button class="btn btn-add" id="addBtn" type="button">
                <span class="btn-plus" aria-hidden="true">＋</span> Add task
            </button>
        </div>
    </header>

    @if($overdueCount > 0)
        <a class="dash-alert"
           href="{{ route('dashboard', array_merge($calendarBase, ['filter' => 'active', 'due' => 'overdue', 'sort' => 'due'])) }}">
            <strong>{{ $overdueCount }} overdue {{ Str::plural('task', $overdueCount) }}</strong>
            <span class="alert-cta">Review overdue &rarr;</span>
        </a>
    @endif

    {{-- Key numbers --}}
    <section class="kpis" aria-label="Task overview">
        <article class="kpi">
            <p>Total tasks</p>
            <strong class="kpi-n">{{ $totalCount }}</strong>
        </article>

        <article class="kpi">
            <p>Active</p>
            <strong class="kpi-n">{{ $activeCount }}</strong>
            <span class="kpi-foot">{{ $todayCount }} due today</span>
        </article>

        <article class="kpi">
            <p>Completed</p>
            <strong class="kpi-n">{{ $completedCount }}</strong>
            <span class="kpi-foot">{{ $completedThisWeek }} this week</span>
        </article>

        <article class="kpi kpi-progress">
            <p>Progress</p>
            <strong class="kpi-n">{{ $progressPercentage }}%</strong>
            <progress class="completion-meter" max="100" value="{{ $progressPercentage }}" aria-label="Task completion">{{ $progressPercentage }}%</progress>
        </article>
    </section>

    <div class="dash-grid">
        {{-- Task list --}}
        <section class="panel tasks-panel">
            <div class="panel-head">
                <div>
                    <h2>My Tasks</h2>
                    <p>{{ $tasks->count() }} {{ Str::plural('task', $tasks->count()) }}{{ $currentFilter === 'all' ? '' : ' · '.ucfirst($currentFilter) }}</p>
                </div>

            </div>

            <form id="filterForm" action="{{ route('dashboard') }}" method="GET">
                @if(isset($dashboardParameters['filter']))
                    <input type="hidden" name="filter" value="{{ $currentFilter }}">
                @endif
                @if($currentDue)
                    <input type="hidden" name="due" value="{{ $currentDue }}">
                @endif
                @if(isset($dashboardParameters['month']))
                    <input type="hidden" name="month" value="{{ $calendarMonth->format('Y-m') }}">
                @endif
                @if($selectedDate)
                    <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                @endif

                <div class="toolbar">
                    <div class="search">
                        <span class="search-ico" aria-hidden="true">⌕</span>
                        <input class="input"
                               id="search"
                               name="search"
                               value="{{ $searchQuery }}"
                               placeholder="Search tasks..."
                               aria-label="Search tasks">
                    </div>

                    <select class="input select-compact" id="priorityFilter" name="priority" aria-label="Filter by priority">
                        <option value="all" {{ $currentPriority === 'all' ? 'selected' : '' }}>All priorities</option>
                        <option value="high" {{ $currentPriority === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ $currentPriority === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ $currentPriority === 'low' ? 'selected' : '' }}>Low</option>
                    </select>

                    <select class="input select-compact" id="sort" name="sort" aria-label="Sort tasks">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="oldest" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="priority" {{ $currentSort === 'priority' ? 'selected' : '' }}>Priority</option>
                        <option value="due" {{ $currentSort === 'due' ? 'selected' : '' }}>Due date</option>
                    </select>
                </div>
            </form>

            @if($selectedDate || $currentDue || $searchQuery !== '' || $currentPriority !== 'all')
                <div class="active-filters">
                    @if($selectedDate)
                        <span class="filter-pill">Due {{ $selectedDate->format('M j, Y') }}</span>
                    @endif
                    @if($currentDue === 'overdue')
                        <span class="filter-pill danger">Overdue</span>
                    @elseif($currentDue === 'today')
                        <span class="filter-pill">Due today</span>
                    @elseif($currentDue === 'week')
                        <span class="filter-pill">Next 7 days</span>
                    @endif
                    @if($searchQuery !== '')
                        <span class="filter-pill">“{{ $searchQuery }}”</span>
                    @endif
                    @if($currentPriority !== 'all')
                        <span class="filter-pill">{{ ucfirst($currentPriority) }} priority</span>
                    @endif

                    <a class="clear-filters" href="{{ route('dashboard', Arr::except($dashboardParameters, ['date', 'due', 'search', 'priority', 'page'])) }}">Clear filters</a>
                </div>
            @endif

            <div class="tasks" id="taskList">
                @forelse($tasks as $task)
                    @include('components.task-card', ['task' => $task])
                @empty
                    <div class="empty">
                        <b>No tasks found</b>
                        <span>{{ $totalCount > 0 ? 'Try a different search or filter.' : 'Add your first task to get started.' }}</span>
                        <button class="btn secondary" type="button" onclick="openModal()">＋ Add a task</button>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Calendar + upcoming --}}
        <aside class="dash-side">
            <section class="panel calendar-panel" id="calendar" aria-label="Task calendar">
                <div class="calendar-head">
                    <div>
                        <h2>{{ $monthName }}</h2>
                        <p>{{ $calendarTaskCount }} {{ Str::plural('task', $calendarTaskCount) }} scheduled</p>
                    </div>

                    <div class="calendar-nav">
                        <a href="{{ route('dashboard', array_merge($calendarBase, ['month' => $prevMonth])) }}"
                           class="cal-nav-btn" aria-label="Previous month" title="Previous month">&lsaquo;</a>
                        <a href="{{ route('dashboard', array_merge($calendarBase, ['month' => now()->format('Y-m')])) }}"
                           class="cal-today-btn">Today</a>
                        <a href="{{ route('dashboard', array_merge($calendarBase, ['month' => $nextMonth])) }}"
                           class="cal-nav-btn" aria-label="Next month" title="Next month">&rsaquo;</a>
                    </div>
                </div>

                <div class="calendar-weekdays" aria-hidden="true">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                        <span>{{ $weekday }}</span>
                    @endforeach
                </div>

                <div class="calendar-grid">
                    @foreach($calendarDays as $day)
                        <div class="cal-day {{ $day['in_month'] ? '' : 'outside' }} {{ $day['is_today'] ? 'today' : '' }} {{ $day['is_selected'] ? 'selected' : '' }} {{ $day['tasks']->isNotEmpty() ? 'has-tasks' : '' }}">
                            <a class="cal-date"
                               href="{{ route('dashboard', array_merge($calendarBase, ['date' => $day['key'], 'month' => $day['date']->format('Y-m')])) }}"
                               title="Show tasks due {{ $day['date']->format('M j, Y') }}">{{ $day['date']->day }}</a>

                            <div class="cal-tasks">
                                @foreach($day['tasks']->take(2) as $task)
                                    <a class="cal-chip {{ $task->priority }} {{ $task->is_completed ? 'done' : '' }}"
                                       href="{{ route('tasks.show', $task) }}"
                                       title="{{ $task->title }} — {{ ucfirst($task->priority) }} priority">{{ $task->title }}</a>
                                @endforeach

                                @if($day['tasks']->count() > 2)
                                    <a class="cal-more"
                                       href="{{ route('dashboard', array_merge($calendarBase, ['date' => $day['key'], 'month' => $day['date']->format('Y-m')])) }}"
                                       title="Show all {{ $day['tasks']->count() }} tasks due this day">+{{ $day['tasks']->count() - 2 }} more</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="calendar-legend">
                    <span><i class="legend-dot high"></i>High</span>
                    <span><i class="legend-dot medium"></i>Medium</span>
                    <span><i class="legend-dot low"></i>Low</span>
                    <span><i class="legend-dot done"></i>Done</span>
                </div>
            </section>

            <section class="panel upcoming-panel">
                <div class="panel-head compact">
                    <div>
                        <h2>Upcoming deadlines</h2>
                    </div>
                    <a class="muted-link" href="{{ route('dashboard', ['filter' => 'active', 'sort' => 'due']) }}">View all</a>
                </div>

                @forelse($upcomingTasks as $task)
                    <a class="upcoming-item" href="{{ route('tasks.show', $task) }}">
                        <span class="up-bar {{ $task->priority }}" aria-hidden="true"></span>
                        <span class="up-body">
                            <strong>{{ $task->title }}</strong>
                            <span>
                                @if($task->due_date->isToday())
                                    Today
                                @elseif($task->due_date->isTomorrow())
                                    Tomorrow
                                @else
                                    {{ $task->due_date->format('D, M j') }}
                                @endif
                            </span>
                        </span>
                        <span class="up-pri {{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                    </a>
                @empty
                    <div class="up-empty">
                        No upcoming deadlines.
                    </div>
                @endforelse
            </section>
        </aside>
    </div>
@endsection
