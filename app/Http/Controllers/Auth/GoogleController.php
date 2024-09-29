<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
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

        // Try to find the user by Google ID first
        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // If no user found by Google ID, check by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If a user exists by email but not Google ID, update their Google ID and token
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                ]);
                Log::info('Existing user updated with Google ID:', ['user_id' => $user->id]);
            } else {
                // If no user found by email, create a new user without a password
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'password' => null, // No password for Google login
                ]);
                Log::info('New user registered:', ['user_id' => $user->id]);

                // **Create a personal team for the user (just like normal registration flow)**
                $team = Team::create([
                    'user_id' => $user->id,
                    'name' => $user->name . "'s Team",
                    'personal_team' => true,
                ]);

                // Assign the team to the user
                $user->ownedTeams()->save($team);
                $user->current_team_id = $team->id;
                $user->teams()->attach($team, ['role' => 'owner']);
                $user->save();

                Log::info('Personal team created for new user', ['team_id' => $team->id]);

                // **Create a Stripe customer without subscription**
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $stripeCustomer = \Stripe\Customer::create([
                    'email' => $user->email,
                    // Optionally add metadata or other fields
                ]);
                $user->stripe_id = $stripeCustomer->id;
                $user->save();

                Log::info('Stripe customer created for new user', ['stripe_id' => $user->stripe_id]);
            }
        } else {
            // Update Google token if necessary for existing user
            $user->update(['google_token' => $googleUser->token]);
            Log::info('Existing user updated with Google token:', ['user_id' => $user->id]);
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
