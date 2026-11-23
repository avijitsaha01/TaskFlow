<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the account settings page.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $taskCount = $user->tasks()->count();

        return view('settings.edit', compact('user', 'taskCount'));
    }

    /**
     * Update username, email and phone.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => preg_replace('/[^0-9+]/', '', (string) $request->input('phone')),
        ]);

        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/', Rule::unique('users', 'phone')->ignore($user->id)],
        ], [
            'username.alpha_dash' => 'The username may only contain letters, numbers, dashes and underscores.',
            'phone.regex' => 'Enter a valid phone number, for example +15551234567.',
        ]);

        $user->update([
            'name' => $data['username'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        return back()->with('success', 'Profile updated.');
    }

    /**
     * Change the account password after confirming the current one.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'different:current_password', 'confirmed'],
        ], [
            'password.different' => 'The new password must be different from your current password.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $user->update(['password' => $data['password']]);

        $request->session()->regenerate();

        return back()->with('success', 'Password updated.');
    }

    /**
     * Save dashboard defaults.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_filter' => ['required', Rule::in(['all', 'today', 'active', 'completed'])],
            'default_sort' => ['required', Rule::in(['newest', 'oldest', 'priority', 'due'])],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Preferences saved.');
    }

    /**
     * Permanently delete the account and its tasks.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check((string) $request->input('confirm_password'), $user->password)) {
            throw ValidationException::withMessages([
                'confirm_password' => 'Enter your password to confirm account deletion.',
            ]);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account and all tasks were deleted.');
    }
}
