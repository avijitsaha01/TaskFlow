<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private const OTP_TTL_MINUTES = 10;

    private const SESSION_USER = 'password_reset_user_id';

    private const SESSION_VERIFIED = 'password_reset_verified_user_id';

    /**
     * Show the "find your account" form.
     */
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Find the account and issue a one-time code.
     */
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ], [
            'identifier.required' => 'Enter your email, username or phone number.',
        ]);

        $user = User::byIdentifier($data['identifier'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => 'No account found with that email, username or phone.',
            ]);
        }

        return $this->issueCode($request, $user);
    }

    /**
     * Re-issue a code for the account already in progress.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = User::find($request->session()->get(self::SESSION_USER));

        if (! $user) {
            return redirect()->route('password.request')
                ->with('error', 'Your reset session expired. Please start again.');
        }

        return $this->issueCode($request, $user);
    }

    /**
     * Show the OTP entry form.
     */
    public function verifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_USER)) {
            return redirect()->route('password.request')
                ->with('error', 'Please start the password reset again.');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify the submitted OTP and allow setting a new password.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.digits' => 'The verification code must be 6 digits.',
        ]);

        $userId = $request->session()->get(self::SESSION_USER);

        if (! $userId) {
            return redirect()->route('password.request')
                ->with('error', 'Please start the password reset again.');
        }

        $otp = PasswordResetOtp::where('user_id', $userId)->usable()->latest()->first();

        if (! $otp || ! Hash::check((string) $request->input('code'), $otp->code)) {
            throw ValidationException::withMessages([
                'code' => 'That code is invalid or has expired. Request a new one.',
            ]);
        }

        $otp->update(['used_at' => now()]);

        $request->session()->forget([self::SESSION_USER, 'otp_code']);
        $request->session()->put(self::SESSION_VERIFIED, $userId);

        return redirect()->route('password.reset.form')
            ->with('success', 'Code verified. Choose a new password.');
    }

    /**
     * Show the new password form.
     */
    public function resetForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_VERIFIED)) {
            return redirect()->route('password.request')
                ->with('error', 'Verify a code before setting a new password.');
        }

        return view('auth.reset-password');
    }

    /**
     * Store the new password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $userId = $request->session()->get(self::SESSION_VERIFIED);
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('password.request')
                ->with('error', 'Your reset session expired. Please start again.');
        }

        $user->update(['password' => $request->input('password')]);

        PasswordResetOtp::where('user_id', $user->id)->delete();

        $request->session()->forget([self::SESSION_VERIFIED, 'otp_code']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Password updated. You can sign in now.');
    }

    /**
     * Generate, store and flash a fresh OTP for the user.
     */
    private function issueCode(Request $request, User $user): RedirectResponse
    {
        $code = (string) random_int(100000, 999999);

        PasswordResetOtp::where('user_id', $user->id)->whereNull('used_at')->delete();

        PasswordResetOtp::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        $request->session()->put(self::SESSION_USER, $user->id);
        $request->session()->put('otp_code', $code);
        $request->session()->forget(self::SESSION_VERIFIED);

        // Demo delivery: the code is shown on the verification screen.
        // Replace this with an SMS/WhatsApp job once a gateway is wired up.
        return redirect()->route('password.verify.form')
            ->with('success', 'Demo verification code generated.');
    }
}
