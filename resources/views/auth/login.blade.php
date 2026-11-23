@extends('layouts.auth')

@section('title', 'Sign in')
@section('heading', 'Welcome back')
@section('subheading', 'Sign in with your email, username or phone.')

@section('content')
    <form class="form" action="{{ route('login') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="login">Email, username or phone</label>
            <input class="input" id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input class="input" id="password" name="password" type="password" required autocomplete="current-password">
        </div>

        <label class="check-line">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            Remember me
        </label>

        <button class="btn" type="submit">Sign in</button>
    </form>

    <p class="auth-foot">
        @if(\App\Http\Middleware\EnsureDemoPasswordResetIsEnabled::enabled())
            <a href="{{ route('password.request') }}">Forgot password?</a><br>
        @endif
        No account? <a href="{{ route('register') }}">Create one</a>
    </p>
@endsection
