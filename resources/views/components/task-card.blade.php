@php
    $due = $task->due_date;
    $dueClass = '';
    $dueLabel = $due ? $due->format('M j') : '';

    if ($due && ! $task->is_completed) {
        if ($due->isToday()) {
            $dueClass = 'today';
            $dueLabel = 'Today';
        } elseif ($due->isTomorrow()) {
            $dueClass = 'soon';
            $dueLabel = 'Tomorrow';
        } elseif ($due->isPast()) {
            $dueClass = 'overdue';
        }
    }
@endphp

<article class="task {{ $task->is_completed ? 'done' : '' }}"
    data-task-id="{{ $task->id }}"
    data-title="{{ $task->title }}"
    data-description="{{ $task->description }}"
    data-priority="{{ $task->priority }}"
    data-due="{{ $due ? $due->format('Y-m-d') : '' }}">

    {{-- Toggle completion --}}
    <form action="{{ route('tasks.toggle', $task) }}" method="POST" style="margin:0;display:inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="check {{ $task->is_completed ? 'done' : '' }}"
                title="{{ $task->is_completed ? 'Reopen task' : 'Mark complete' }}"
                aria-label="{{ $task->is_completed ? 'Reopen task' : 'Mark complete' }}">
            {{ $task->is_completed ? '✓' : '' }}
        </button>
    </form>

    {{-- Task info --}}
    <div class="task-body">
        <a href="{{ route('tasks.show', $task) }}" class="task-link">
            <div class="task-title">{{ $task->title }}</div>
        </a>
        <div class="meta">
            @include('components.priority-badge', ['priority' => $task->priority])

            @if($task->status === 'in_progress')
                <span class="tag status-progress">In progress</span>
            @endif

            @if($due)
                <span class="due {{ $dueClass }}" title="Due {{ $due->format('F j, Y') }}">
                    {{ $dueClass === 'overdue' ? 'Overdue · ' : '' }}{{ $dueLabel }}
                </span>
            @endif

            @if($task->description)
                <span class="task-snippet">{{ Str::limit($task->description, 70) }}</span>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="actions">
        <button type="button" class="icon" onclick="openModal({{ $task->id }})" title="Edit task" aria-label="Edit task">Edit</button>
        <button type="button" class="icon task-delete" onclick="confirmDelete({{ $task->id }})" title="Delete task" aria-label="Delete task">Delete</button>
    </div>

    {{-- Hidden delete form --}}
    <form id="delete-form-{{ $task->id }}" action="{{ route('tasks.destroy', $task) }}" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>
</article>
