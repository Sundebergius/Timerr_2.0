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

            // Check if a user already exists with this Google ID
            $googleAccountUser = User::where('google_id', $googleUser->getId())->first();

            if ($googleAccountUser) {
                // If the user exists with this Google ID, log them in
                Log::info('Google account linked to user, logging in.', ['google_id' => $googleUser->getId(), 'user_id' => $googleAccountUser->id]);
                Auth::login($googleAccountUser);
                return redirect()->intended('/dashboard');
            }

            // Check if a user with the same email exists but without the Google ID linked
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                if (!$existingUser->google_id) {
                    // Link Google account to the existing user
                    $existingUser->update([
                        'google_id' => $googleUser->getId(),
                        'google_token' => $googleUser->token,
                    ]);
                    Log::info('Google account linked to existing user by email.', ['user_id' => $existingUser->id]);

                    Auth::login($existingUser);
                    return redirect()->intended('/dashboard');
                } else {
                    // If the email matches but the Google ID is already linked, handle gracefully
                    Log::warning('Google account already linked to another user.', ['google_id' => $googleUser->getId(), 'user_id' => $existingUser->id]);
                    return redirect()->route('login')->withErrors(['error' => 'This Google account is already linked to another Timerr account.']);
                }
            }

            // If no user is found by Google ID or email, create a new user
            $newUser = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'google_token' => $googleUser->token,
                'password' => null, // No password for Google login
            ]);
            Log::info('New user registered via Google.', ['user_id' => $newUser->id]);

            // Create personal team for the new user
            $team = Team::create([
                'user_id' => $newUser->id,
                'name' => $newUser->name . "'s Team",
                'personal_team' => true,
            ]);

            // Assign the team to the user
            $newUser->ownedTeams()->save($team);
            $newUser->current_team_id = $team->id;
            $newUser->teams()->attach($team, ['role' => 'owner']);
            $newUser->save();

            Log::info('Personal team created for new user.', ['team_id' => $team->id]);

            // Create Stripe customer without subscription
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeCustomer = \Stripe\Customer::create([
                'email' => $newUser->email,
            ]);
            $newUser->stripe_id = $stripeCustomer->id;
            $newUser->save();

            Log::info('Stripe customer created for new user.', ['stripe_id' => $newUser->stripe_id]);

            // Log the new user in
            Auth::login($newUser);
            Log::info('User logged in via Google.', ['user_id' => $newUser->id]);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            Log::critical('Error during Google login.', [
                'message' => $e->getMessage(),
                'google_email' => $googleUser->getEmail() ?? 'N/A',
            ]);

            return redirect()->route('login')->withErrors(['error' => 'Google login failed. Please try again or contact support.']);
        }
    }

    public function linkGoogle(Request $request)
    {
        $user = Auth::user();

        // Link Google account to the current authenticated user
        $user->update([
            'google_id' => $request->google_user_id,
            'google_token' => $request->google_user_token,
        ]);

        Log::info('User linked Google account.', ['user_id' => $user->id]);

        return redirect()->route('profile.show')->with('status', 'Your Google account has been successfully linked.');
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