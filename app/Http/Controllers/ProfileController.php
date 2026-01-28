<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'dark_mode' => $request->user()->dark_mode ?? false,
        ]);
    }

    /**
     * Display the edit profile page.
     */
    public function editProfile(Request $request): Response
    {
        $user = $request->user();
        $fullname = '';
        if ($user) {
            $fullname = FullnameCapital($user->firstname ?? '', $user->lastname ?? '');
        }
        return Inertia::render('Profile/EditProfile', [
            'user' => $user,
            'fullname' => $fullname,
        ]);
    }

    /**
     * Display the change password page.
     */
    public function changePassword(Request $request): Response
    {
        $user = $request->user();
        $fullname = '';
        if ($user) {
            $fullname = FullnameCapital($user->firstname ?? '', $user->lastname ?? '');
        }
        return Inertia::render('Profile/ChangePassword', [
            'fullname' => $fullname,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('dashboard')->with('success', 'Your profile has been updated successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update only the user's theme preference (dark_mode).
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'dark_mode' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $user->dark_mode = $request->boolean('dark_mode');
        $user->save();

        return response()->json(['dark_mode' => $user->dark_mode]);
    }
}
