<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use \Stripe\StripeClient;


class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $hasPassword = !is_null($user->password); // Check if user has a password set

        // Get the subscription
        $subscription = $user->subscription('default');

        // Translate the subscription details
        $subscriptionDetails = $this->translateSubscription($subscription ? $subscription->stripe_price : null);

        // Return the view with the necessary data
        return view('profile.edit', [
            'user' => $user,
            'hasPassword' => $hasPassword, // Pass the password existence check
            'subscriptionDetails' => $subscriptionDetails, // Pass subscription details to view
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

    /**
     * Translate the Stripe subscription price ID to a human-readable format.
     */
    public function translateSubscription($stripePriceId)
    {
        switch ($stripePriceId) {
            case 'price_xxx': // Replace with your actual free plan price ID
                return [
                    'name' => 'Free',
                    'price' => '0.00 DKK',
                ];
            case 'price_1Q2RSpEEh64CES4EjOr0VQvr': // Freelancer plan price ID
                return [
                    'name' => 'Freelancer',
                    'price' => '99 DKK',
                ];
            default:
                return [
                    'name' => 'Unknown Plan',
                    'price' => 'N/A',
                ];
        }
    }

    public function redirectToBillingPortal(Request $request)
    {
        // Initialize Stripe with your secret key
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        // Get the current authenticated user
        $user = $request->user();

        // Fetch the billing portal session URL from Stripe
        $session = $stripe->billingPortal->sessions->create([
            'customer' => $user->stripe_id, // Ensure user is subscribed and has a Stripe ID
            'return_url' => route('profile.show'), // Redirect user back to profile after managing subscription
        ]);

        // Redirect to the billing portal
        return redirect($session->url);
    }
}
