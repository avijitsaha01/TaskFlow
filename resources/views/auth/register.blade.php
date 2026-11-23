@extends('layouts.auth')

@section('title', 'Create account')
@section('heading', 'Create your space')
@section('subheading', 'Your tasks stay private to your account.')

@section('content')
    <form class="form" action="{{ route('register') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="username">Username</label>
            <input class="input" id="username" name="username" value="{{ old('username') }}" required minlength="3" maxlength="50" autocomplete="username">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input class="input" id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+15551234567" required autocomplete="tel">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input class="input" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <button class="btn" type="submit">Create account</button>
    </form>

    <p class="auth-foot">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
@endsection
