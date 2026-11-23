<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     * Redirects to the dashboard since all task listing happens there.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    /**
     * Show the form for creating a new task.
     * Since we use a modal, redirect to dashboard.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $request->user()->tasks()->create($request->validated());

        return redirect()->route('dashboard')->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified task.
     */
    public function show(Request $request, Task $task): View
    {
        $this->ensureOwnership($request, $task);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Request $request, Task $task): View
    {
        $this->ensureOwnership($request, $task);

        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()->route('dashboard')->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->ensureOwnership($request, $task);

        $task->delete();

        return redirect()->route('dashboard')->with('success', 'Task deleted successfully!');
    }

    /**
     * Toggle the task status between pending/in_progress and completed.
     */
    public function toggle(Request $request, Task $task): RedirectResponse
    {
        $this->ensureOwnership($request, $task);

        $task->status = $task->status === 'completed' ? 'pending' : 'completed';
        $task->save();

        $statusLabel = $task->status === 'completed' ? 'completed' : 'reopened';

        return redirect()->back()->with('success', "Task marked as {$statusLabel}!");
    }

    /**
     * Treat other users' tasks as not found.
     */
    private function ensureOwnership(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 404);
    }
}
