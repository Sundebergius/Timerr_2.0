<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        // Pass the selected plan to the registration view, if any
        $plan = $request->query('plan', 'free'); // Default to 'free' if no plan is selected
        return view('auth.register', ['plan' => $plan]);    
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction(); // Start a database transaction

        try {
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
                'personal_team' => true,
            ]);

            // Assign the team to the user
            $user->ownedTeams()->save($team);

            // Set the current team ID for the user
            $user->current_team_id = $team->id;
            $user->save();

            // Optionally, make the user a member of their own team
            $user->teams()->attach($team, ['role' => 'owner']);

            // Wrap Stripe-related code in a separate try-catch block
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));  // Use config instead of env
                $stripeCustomer = \Stripe\Customer::create([
                    'email' => $user->email,
                ]);
                $user->stripe_id = $stripeCustomer->id;
                $user->save();
            } catch (\Stripe\Exception\ApiErrorException $e) {
                \Log::error('Stripe error for user ' . $user->email . ': ' . $e->getMessage());
                throw new \Exception('There was an issue setting up your payment details.');
            }

            DB::commit();

            // Fire registered event and log the user in
            event(new Registered($user));
            Auth::login($user);

            // After successful registration, handle the redirection based on the plan
            return $this->redirectToDashboard($request, $user);

        } catch (Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Log the error message for debugging
            \Log::error('Registration failed: ' . $e->getMessage());

            return redirect()->back()->withErrors('Registration failed. Please try again.');
        }
    }

    /**
     * Redirect the user after registration based on the selected plan.
     */
    public function redirectToDashboard(Request $request, User $user): RedirectResponse
    {
        // Get the selected plan from the query parameters (passed during registration)
        $plan = $request->query('plan', 'free'); // Default to 'free' if no plan is provided

        if ($plan === 'freelancer') {
            // Redirect to the Stripe checkout page for the Freelancer plan
            return redirect()->route('stripe.checkout', ['plan' => 'freelancer']);
        } elseif ($plan === 'pro') {
            // Redirect to the Stripe checkout page for the Pro plan
            return redirect()->route('stripe.checkout', ['plan' => 'freelancer_pro']);
        }

        // For the free plan, redirect the user to the dashboard
        return redirect()->route('dashboard');
    }
}