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
            Log::info('Received Google user data.', ['google_email' => $googleUser->getEmail()]);

            // Check if the Google ID already exists in the system
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                // If no user is found by Google ID, check by email
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    // Check if the Google account is already linked to another user
                    if ($user->google_id) {
                        Log::critical('Google account already linked to another user.', [
                            'google_id' => $googleUser->getId(),
                            'current_user_id' => $user->id
                        ]);
                        return redirect()->route('login')->withErrors(['error' => 'This Google account is already linked to another user.']);
                    }

                    // Link Google account to the existing user
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'google_token' => $googleUser->token,
                    ]);
                    Log::info('Existing user updated with Google ID.', ['user_id' => $user->id]);
                } else {
                    // If no user is found by email, create a new user without a password
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'google_token' => $googleUser->token,
                        'password' => null, // No password for Google login
                    ]);
                    Log::info('New user registered via Google.', ['user_id' => $user->id]);

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

                    Log::info('Personal team created for new user.', ['team_id' => $team->id]);

                    // **Create a Stripe customer without subscription**
                    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                    $stripeCustomer = \Stripe\Customer::create([
                        'email' => $user->email,
                    ]);
                    $user->stripe_id = $stripeCustomer->id;
                    $user->save();

                    Log::info('Stripe customer created for new user.', ['stripe_id' => $user->stripe_id]);
                }
            } else {
                // Update Google token for the existing user
                $user->update(['google_token' => $googleUser->token]);
                Log::info('Existing user updated with Google token.', ['user_id' => $user->id]);
            }

            // Log the user in
            Auth::login($user);
            Log::info('User logged in via Google.', ['user_id' => $user->id]);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            // Log critical error and redirect
            Log::critical('Error during Google login.', [
                'message' => $e->getMessage(),
                'google_email' => $googleUser->getEmail() ?? 'N/A'
            ]);

            return redirect()->route('login')->withErrors(['error' => 'Google login failed. Please try again or contact support.']);
        }
    }

    /**
     * Disconnect the Google account from the user's profile.
     */
    public function disconnect()
    {
        $user = auth()->user();
        $user->google_id = null;
        $user->google_token = null;
        $user->save();

        Log::info('Google account disconnected for user.', ['user_id' => $user->id]);

        return back()->with('status', 'Google account disconnected successfully.');
    }
}