@extends('layouts.app')

@section('content')
    <a href="{{ route('dashboard') }}" class="back-link">&larr; Back to Dashboard</a>

    <div class="detail-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <h2>{{ $task->title }}</h2>
                <div class="detail-meta">
                    @include('components.priority-badge', ['priority' => $task->priority])
                    <span>Status: <strong>{{ ucfirst($task->status) }}</strong></span>
                    @if($task->due_date)
                        <span>Due: 📅 {{ $task->due_date->format('F d, Y') }}</span>
                    @endif
                    <span>Created: {{ $task->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $task->is_completed ? 'secondary' : '' }}">
                    {{ $task->is_completed ? 'Reopen Task' : 'Mark Completed' }}
                </button>
            </form>
        </div>

        @if($task->description)
            <div class="detail-body">
                <strong>Description:</strong>
                <p style="margin-top:6px;white-space:pre-wrap">{{ $task->description }}</p>
            </div>
        @else
            <div class="detail-body" style="color:var(--muted)">
                <em>No description provided.</em>
            </div>
        @endif

        <div class="detail-actions">
            <button type="button" class="btn secondary" onclick="openModal({{ $task->id }})" data-task-id="{{ $task->id }}"
                data-title="{{ $task->title }}"
                data-description="{{ $task->description }}"
                data-priority="{{ $task->priority }}"
                data-due="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}">✎ Edit Task</button>
            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?')" style="margin:0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn danger">⌫ Delete Task</button>
            </form>
        </div>
    </div>
@endsection
