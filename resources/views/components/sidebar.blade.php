<aside class="sidebar">
    @php
        $viewParameters = array_filter(Arr::only(request()->query(), ['priority', 'sort', 'search']), 'is_string');
        $taskViews = [
            'all' => ['All tasks', '≡', $totalCount ?? null],
            'today' => ['Today', '◷', $todayCount ?? null],
            'active' => ['Active', '○', $activeCount ?? null],
            'completed' => ['Completed', '✓', $completedCount ?? null],
        ];
    @endphp

    <a class="logo" href="{{ route('dashboard', ['filter' => 'all']) }}">
        <span class="brand-mark" aria-hidden="true">✓</span> TaskFlow
    </a>

    <nav class="nav" aria-label="Task views">
        @foreach($taskViews as $view => [$label, $icon, $count])
            <a href="{{ route('dashboard', ['filter' => $view] + $viewParameters) }}"
                @if(($currentFilter ?? null) === $view) class="active" aria-current="page" @endif>
                <span class="nav-ico" aria-hidden="true">{{ $icon }}</span>
                <span>{{ $label }}</span>
                @if($count !== null)
                    <small>{{ $count }}</small>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="side-bottom">
        <div class="side-account">
            <span class="avatar-sm" aria-hidden="true">{{ strtoupper(substr(auth()->user()->username, 0, 1)) }}</span>

            <a class="side-user" href="{{ route('settings.edit') }}" title="Open account settings">
                <strong>{{ auth()->user()->username }}</strong>
            </a>

            <a class="gear-btn {{ request()->routeIs('settings.*') ? 'active' : '' }}"
               href="{{ route('settings.edit') }}"
               title="Settings"
               aria-label="Settings">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c0 .66.39 1.26 1 1.51H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </a>
        </div>

        <form action="{{ route('logout') }}" method="POST" style="margin:0">
            @csrf
            <button type="submit" class="logout-btn">Sign out</button>
        </form>
    </div>
</aside>
