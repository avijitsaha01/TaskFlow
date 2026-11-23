@extends('layouts.app')

@section('content')
    <a href="{{ route('dashboard') }}" class="back-link">&larr; Back to Dashboard</a>

    <div class="edit-card">
        <h2>Edit Task</h2>

        <form class="form" action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="taskTitle">Task title</label>
                <input class="input @error('title') is-invalid @enderror" 
                       id="taskTitle" 
                       name="title" 
                       value="{{ old('title', $task->title) }}" 
                       required 
                       maxlength="100">
                @error('title')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="taskDesc">Description</label>
                <textarea class="input @error('description') is-invalid @enderror" 
                          id="taskDesc" 
                          name="description" 
                          rows="4">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="taskPriority">Priority</label>
                    <select class="input @error('priority') is-invalid @enderror" id="taskPriority" name="priority">
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                    @error('priority')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="taskDue">Due date</label>
                    <input class="input @error('due_date') is-invalid @enderror" 
                           type="date" 
                           id="taskDue" 
                           name="due_date" 
                           value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
                    @error('due_date')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="modal-actions" style="margin-top:14px">
                <a href="{{ route('dashboard') }}" class="btn secondary">Cancel</a>
                <button class="btn" type="submit">Update Task</button>
            </div>
        </form>
    </div>
@endsection
