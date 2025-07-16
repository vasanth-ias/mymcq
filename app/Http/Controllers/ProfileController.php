<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Fill validated name/email/etc.
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // --- Handle Profile Picture ---
        if ($request->hasFile('profile_picture')) {
            // Delete old picture
            if ($request->user()->profile_picture) {
                Storage::disk('public')->delete($request->user()->profile_picture);
            }
            // Upload new one
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $request->user()->profile_picture = $path;
        } elseif ($request->input('delete_profile_picture')) {
            // Delete current picture
            if ($request->user()->profile_picture) {
                Storage::disk('public')->delete($request->user()->profile_picture);
            }
            $request->user()->profile_picture = null;
        }

        // --- Optional: Handle Phone Number ---
        if ($request->has('phone_number')) {
            $request->user()->phone_number = $request->input('phone_number');
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
