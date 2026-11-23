@extends('layouts.auth')

@section('title', 'Forgot password')
@section('heading', 'Reset your password')
@section('subheading', 'We will send a 6-digit code to verify it is you.')

@section('content')
    <form class="form" action="{{ route('password.send') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="identifier">Email, username or phone</label>
            <input class="input" id="identifier" name="identifier" value="{{ old('identifier') }}" required autofocus>
        </div>

        <button class="btn" type="submit">Send verification code</button>
    </form>

    <p class="auth-foot">Remembered it? <a href="{{ route('login') }}">Back to sign in</a></p>
@endsection
