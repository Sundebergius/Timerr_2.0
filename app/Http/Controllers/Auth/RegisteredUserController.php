<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Convert email to lowercase before validation
        $request->merge([
            'email' => strtolower($request->email),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create personal team for the user
        $team = Team::create([
            'user_id' => $user->id,
            'name' => $user->name . "'s Team",
            'personal_team' => true,  // Set the team as personal
        ]);

        // Assign the team to the user
        $user->ownedTeams()->save($team);

        // Set the current team ID for the user
        $user->current_team_id = $team->id; // Set the current team ID
        $user->save(); // Don't forget to save the user model!

        // Optionally, make the user a member of their own team
        $user->teams()->attach($team, ['role' => 'owner']);

        // Create a customer in Stripe
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $stripeCustomer = \Stripe\Customer::create([
            'email' => $user->email,
            // Add more fields if necessary
        ]);

        // Save the Stripe customer ID to the user model
        $user->stripe_id = $stripeCustomer->id;
        $user->save();

        // Create a subscription for the Free plan
        $user->newSubscription('default', 'price_1Q2SR1EEh64CES4EGHXu1oMy')->create(); // Use the appropriate price ID for your Free plan

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}