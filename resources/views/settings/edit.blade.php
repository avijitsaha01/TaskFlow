@extends('layouts.app')

@section('content')
    <div class="top">
        <div class="title">
            <h1>Settings</h1>
            <p>Manage your profile, password and dashboard defaults.</p>
        </div>
        <a class="btn secondary" href="{{ route('dashboard') }}">&larr; Back to tasks</a>
    </div>

    @if($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <div class="profile-summary">
        <div class="avatar">{{ strtoupper(substr($user->username, 0, 1)) }}</div>
        <div>
            <strong>{{ $user->username }}</strong>
            <div class="profile-meta">
                {{ $user->email }} &middot; {{ $user->phone }} &middot;
                {{ $taskCount }} {{ Str::plural('task', $taskCount) }} &middot;
                Joined {{ $user->created_at->format('M Y') }}
            </div>
        </div>
    </div>

    <div class="settings-grid">
        {{-- Profile --}}
        <section class="panel settings-card">
            <div class="card-head">
                <h2>Profile</h2>
                <p>Your username is how you sign in and how you appear in the app.</p>
            </div>

            <form class="form" action="{{ route('settings.profile') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="username">Username</label>
                    <input class="input" id="username" name="username" value="{{ old('username', $user->username) }}" required minlength="3" maxlength="50" autocomplete="username">
                    @error('username')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                    @error('email')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input class="input" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required autocomplete="tel">
                    @error('phone')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit">Save profile</button>
                </div>
            </form>
        </section>

        {{-- Password --}}
        <section class="panel settings-card">
            <div class="card-head">
                <h2>Password</h2>
                <p>Use at least 8 characters. You will stay signed in.</p>
            </div>

            <form class="form" action="{{ route('settings.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">Current password</label>
                    <input class="input" id="current_password" name="current_password" type="password" required autocomplete="current-password">
                    @error('current_password')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="new_password">New password</label>
                    <input class="input" id="new_password" name="password" type="password" required minlength="8" autocomplete="new-password">
                    @error('password')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Confirm new password</label>
                    <input class="input" id="new_password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit">Update password</button>
                </div>
            </form>
        </section>

        {{-- Preferences --}}
        <section class="panel settings-card">
            <div class="card-head">
                <h2>Dashboard defaults</h2>
                <p>Choose what the task list shows when you open it.</p>
            </div>

            <form class="form" action="{{ route('settings.preferences') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="default_filter">Default view</label>
                    <select class="input" id="default_filter" name="default_filter">
                        @foreach(['all' => 'All tasks', 'today' => 'Due today', 'active' => 'Active', 'completed' => 'Completed'] as $value => $label)
                            <option value="{{ $value }}" {{ old('default_filter', $user->default_filter ?: 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_filter')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="default_sort">Default sort</label>
                    <select class="input" id="default_sort" name="default_sort">
                        @foreach(['newest' => 'Newest first', 'oldest' => 'Oldest first', 'priority' => 'Priority', 'due' => 'Due date'] as $value => $label)
                            <option value="{{ $value }}" {{ old('default_sort', $user->default_sort ?: 'newest') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_sort')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit">Save preferences</button>
                </div>
            </form>
        </section>

        {{-- Danger zone --}}
        <section class="panel settings-card danger-card">
            <div class="card-head">
                <h2>Delete account</h2>
                <p>This permanently removes your account and all {{ $taskCount }} {{ Str::plural('task', $taskCount) }}. This cannot be undone.</p>
            </div>

            <form class="form" action="{{ route('settings.destroy') }}" method="POST" onsubmit="return confirm('Delete your account and all tasks? This cannot be undone.')">
                @csrf
                @method('DELETE')

                <div class="form-group">
                    <label for="delete_password">Confirm with your password</label>
                    <input class="input" id="delete_password" name="confirm_password" type="password" required autocomplete="current-password">
                    @error('confirm_password')<span class="error-text">{{ $message }}</span>@enderror
                </div>

                <div class="form-actions">
                    <button class="btn danger" type="submit">Delete my account</button>
                </div>
            </form>
        </section>
    </div>
@endsection
