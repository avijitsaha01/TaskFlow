@extends('layouts.auth')

@section('title', 'Set new password')
@section('heading', 'Choose a new password')
@section('subheading', 'Use at least 8 characters.')

@section('content')
    <form class="form" action="{{ route('password.reset') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="password">New password</label>
            <input class="input" id="password" name="password" type="password" required minlength="8" autofocus autocomplete="new-password">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm new password</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <button class="btn" type="submit">Update password</button>
    </form>
@endsection
