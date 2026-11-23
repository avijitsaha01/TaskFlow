<div class="modal-back {{ $errors->any() ? 'show' : '' }}" id="modalBack" data-reopen="{{ $errors->any() ? '1' : '0' }}">
    <div class="modal">
        <div class="modal-head">
            <h2 id="modalTitle">{{ old('_method') === 'PUT' ? 'Edit Task' : 'Add Task' }}</h2>
            <button type="button" class="close" id="closeModal">&times;</button>
        </div>

        <form class="form" id="taskForm" action="{{ old('_method') === 'PUT' && old('task_id') ? route('tasks.update', old('task_id')) : route('tasks.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="{{ old('_method', 'POST') }}">
            <input type="hidden" name="task_id" id="taskId" value="{{ old('task_id', '') }}">

            {{-- Title --}}
            <div class="form-group">
                <label for="taskTitle">Task title</label>
                <input class="input @error('title') is-invalid @enderror" 
                       id="taskTitle" 
                       name="title" 
                       value="{{ old('title') }}" 
                       required 
                       maxlength="100" 
                       placeholder="e.g. Finish project documentation">
                @error('title')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label for="taskDesc">Description</label>
                <textarea class="input @error('description') is-invalid @enderror" 
                          id="taskDesc" 
                          name="description" 
                          rows="3" 
                          placeholder="Add a short description...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            {{-- Priority & Due Date Row --}}
            <div class="row">
                <div class="form-group">
                    <label for="taskPriority">Priority</label>
                    <select class="input @error('priority') is-invalid @enderror" id="taskPriority" name="priority">
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
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
                           value="{{ old('due_date') }}">
                    @error('due_date')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="modal-actions">
                <button type="button" class="btn secondary" id="cancelBtn">Cancel</button>
                <button class="btn" type="submit">Save Task</button>
            </div>
        </form>
    </div>
</div>
