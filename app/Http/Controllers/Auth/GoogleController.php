<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if the user already exists
            $user = User::where('email', $googleUser->getEmail())->orWhere('google_id', $googleUser->getId())->first();

            if ($user) {
                // Update Google token if necessary
                $user->update([
                    'google_token' => $googleUser->token,
                ]);
            } else {
                // Register the new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'password' => Hash::make(str_random(24)), // Generate a random password
                ]);
            }

            // Log the user in
            Auth::login($user);

            return redirect()->intended('/home');
        } catch (\Exception $e) {
            // Handle error, maybe redirect to login page with a message
            return redirect()->route('login')->withErrors(['error' => 'Google login failed, please try again.']);
        }
    }

    public function disconnect()
    {
        $user = auth()->user();
        $user->google_id = null;
        $user->google_token = null;
        $user->save();

        return back()->with('status', 'Google account disconnected successfully.');
    }
}
