<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        Log::info('Redirecting user to Google authentication page.');
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        Log::info('Google callback initiated.');

        try {
            $googleUser = Socialite::driver('google')->user();
            Log::info('Received Google user data:', ['user' => $googleUser]);

            // Check if the user already exists
            $user = User::where('email', $googleUser->getEmail())->orWhere('google_id', $googleUser->getId())->first();
            Log::info('User lookup result:', ['user' => $user]);

            if ($user) {
                // Update Google token if necessary
                $user->update([
                    'google_token' => $googleUser->token,
                ]);
                Log::info('Existing user updated:', ['user_id' => $user->id]);
            } else {
                // Register the new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'password' => Hash::make(str_random(24)), // Generate a random password
                ]);
                Log::info('New user registered:', ['user_id' => $user->id]);
            }

            // Log the user in
            Auth::login($user);
            Log::info('User logged in:', ['user_id' => $user->id]);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error during Google login:', ['message' => $e->getMessage()]);

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
