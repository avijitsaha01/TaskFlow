@extends('layouts.auth')

@section('title', 'Verify code')
@section('heading', 'Enter your code')
@section('subheading', 'Type the 6-digit code to continue.')

@section('content')
    @if(session('otp_code'))
        <div class="otp-popup" id="otpPopup">
            <div class="otp-popup-head">
                <span>Verification code</span>
                <button type="button" class="otp-popup-close" onclick="document.getElementById('otpPopup').remove()">&times;</button>
            </div>
            <div class="otp-code">{{ session('otp_code') }}</div>
            <p class="otp-note">
                This code is shown here for the local demo.
            </p>
        </div>
    @endif

    <form class="form" action="{{ route('password.verify') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="code">Verification code</label>
            <input class="input otp-input" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required autofocus autocomplete="one-time-code">
        </div>

        <button class="btn" type="submit">Verify code</button>
    </form>

    <form class="resend-form" action="{{ route('password.resend') }}" method="POST">
        @csrf
        <button type="submit" class="link-button">Resend code</button>
        <span class="auth-foot">or <a href="{{ route('password.request') }}">start over</a></span>
    </form>
@endsection
