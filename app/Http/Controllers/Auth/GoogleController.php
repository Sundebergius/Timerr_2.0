<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Google_Client;
use Google_Service_Calendar;

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

        $googleUser = null; // Initialize variable

        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();

            if (!$email) {
                Log::warning('Google user data missing email.', ['google_id' => $googleUser->getId() ?? 'N/A']);
                return redirect()->route('login')->withErrors(['error' => 'Google login failed due to missing email. Please try again or contact support.']);
            }

            Log::info('Received Google user data.', ['google_email' => $googleUser->getEmail()]);

            // Check if a user already exists with this Google ID
            $googleAccountUser = User::where('google_id', $googleUser->getId())->first();

            if ($googleAccountUser) {
                // Check and set email_verified_at if it's not set
                if (!$googleAccountUser->email_verified_at) {
                    $googleAccountUser->email_verified_at = now();
                    $googleAccountUser->save();
                    Log::info('Email verification date set for Google user.', ['user_id' => $googleAccountUser->id]);
                }

                Log::info('Google account linked to user, logging in.', ['google_id' => $googleUser->getId(), 'user_id' => $googleAccountUser->id]);
                Auth::login($googleAccountUser);
                return redirect()->intended('/dashboard');
            }

            // Check if a user with the same email exists but without the Google ID linked
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                if (!$existingUser->google_id) {
                    $existingUser->update([
                        'google_id' => $googleUser->getId(),
                        'google_token' => encrypt($googleUser->token),
                        'email_verified_at' => $existingUser->email_verified_at ?? now(), // Set verification date if not set
                    ]);
                    Log::info('Google account linked to existing user by email.', ['user_id' => $existingUser->id]);
                    Auth::login($existingUser);
                    return redirect()->intended('/dashboard');
                } else {
                    Log::warning('Google account already linked to another user.', ['google_id' => $googleUser->getId(), 'user_id' => $existingUser->id]);
                    return redirect()->route('login')->withErrors(['error' => 'This Google account is already linked to another Timerr account.']);
                }
            }

            // If no user is found by Google ID or email, create a new user
            $newUser = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'google_token' => encrypt($googleUser->token),
                'email_verified_at' => now(), // Mark as verified for social logins
                'password' => null,
            ]);
            Log::info('New user registered via Google.', ['user_id' => $newUser->id]);

            // Create personal team for the new user
            $team = Team::create([
                'user_id' => $newUser->id,
                'name' => $newUser->name . "'s Personal Workspace",
                'personal_team' => true,
            ]);
            $newUser->ownedTeams()->save($team);
            $newUser->current_team_id = $team->id;
            $newUser->teams()->attach($team, ['role' => 'owner']);
            $newUser->save();
            Log::info('Personal team created for new user.', ['team_id' => $team->id]);

            // Create Stripe customer without subscription
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $stripeCustomer = \Stripe\Customer::create([
                    'email' => $newUser->email,
                ]);
                $newUser->stripe_id = $stripeCustomer->id;
                $newUser->save();
                Log::info('Stripe customer created for new user.', ['stripe_id' => $newUser->stripe_id]);
            } catch (\Exception $stripeException) {
                Log::error('Failed to create Stripe customer.', ['error' => $stripeException->getMessage(), 'user_id' => $newUser->id]);
            }

            // Log the new user in
            Auth::login($newUser);
            Log::info('User logged in via Google.', ['user_id' => $newUser->id]);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            Log::critical('Error during Google login.', [
                'message' => $e->getMessage(),
                'google_email' => isset($googleUser) ? $googleUser->getEmail() : 'N/A',
            ]);

            return redirect()->route('login')->withErrors(['error' => 'Google login failed. Please try again or contact support.']);
        }
    }

    public function linkGoogle(Request $request)
    {
        $user = Auth::user();

        // Link Google account to the current authenticated user
        $user->update([
            'google_id' => $request->google_id,
            'google_token' => $request->google_token,
        ]);

        Log::info('User linked Google account.', ['user_id' => $user->id]);

        return redirect()->route('profile.show')->with('status', 'Your Google account has been successfully linked.');
    }

    /**
     * Fetch the list of Google Calendars for the authenticated user.
     */
    public function listGoogleCalendars()
    {
        $user = auth()->user();
        $googleToken = decrypt($user->google_token);

        $client = new Google_Client();
        $client->setAccessToken($googleToken);

        $service = new Google_Service_Calendar($client);

        // Fetch the list of available calendars
        $calendarList = $service->calendarList->listCalendarList();

        $calendars = [];
        foreach ($calendarList->getItems() as $calendar) {
            $calendars[] = [
                'id' => $calendar->getId(),
                'summary' => $calendar->getSummary(),
            ];
        }

        // Send the calendar list to the view so the user can select one
        return view('google.select-calendar', ['calendars' => $calendars]);
    }

    /**
     * Save the selected Google Calendar for the user.
     */
    public function saveSelectedCalendar(Request $request)
    {
        $user = auth()->user();
        $user->google_calendar_id = $request->input('google_calendar_id');
        $user->save();

        return redirect()->route('dashboard')->with('status', 'Google Calendar selected successfully!');
    }

    /**
     * Disconnect the Google account from the user's profile.
     */
    public function disconnect()
    {
        $user = auth()->user();
        $user->google_id = null;
        $user->google_token = null;
        $user->google_refresh_token = null;
        $user->google_calendar_id = null;
        $user->save();

        Log::info('Google account disconnected for user.', ['user_id' => $user->id]);

        return back()->with('status', 'Google account disconnected successfully.');
    }
}