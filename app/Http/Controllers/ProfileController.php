<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use App\Services\PlanService;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService, PlanService $planService)
    {
        $this->stripeService = $stripeService;
        $this->planService = $planService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $hasPassword = !is_null($user->password); // Check if the user has a password set

        // Get the subscription
        $subscription = $user->subscription('default');

        // Translate the subscription details
        $subscriptionDetails = $this->translateSubscription($subscription ? $subscription->stripe_price : null);

        // Return the view with the necessary data
        return view('profile.edit', [
            'user' => $user,
            'hasPassword' => $hasPassword,
            'subscriptionDetails' => $subscriptionDetails,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $user->fill($validated);

        // Handle email change
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Save the profile changes locally
        $user->save();

        // Check if plan changes are needed (free to freelancer or freelancer to free)
        if (isset($validated['plan'])) {
            $this->handlePlanChange($user, $validated['plan']);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Handle plan change (e.g., free to freelancer or freelancer to free).
     */
    protected function handlePlanChange($user, $newPlan)
    {
        // Free Plan
        if ($newPlan === 'free') {
            $this->stripeService->downgradeToFree($user);
        }

        // Freelancer Plan
        if ($newPlan === 'freelancer') {
            $priceId = 'price_1Q2RSpEEh64CES4EjOr0VQvr'; // Freelancer plan price ID
            $this->stripeService->updateSubscription($user, $priceId);
        }

        // Update the plan in the local database
        $user->update(['plan' => $newPlan]);
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

        \DB::beginTransaction(); // Start transaction

        try {
            \Log::info("Starting user deletion for user ID: {$user->id}");

            // Archive the user in Stripe before deleting locally
            $this->stripeService->cancelAndArchiveUser($user);
            \Log::info("Stripe cancellation and archiving called for user ID: {$user->id}");

            // Delete all user-related projects
            \DB::table('projects')->where('user_id', $user->id)->delete();
            \Log::info("Projects deleted for user ID: {$user->id}");

            // Delete all user-related tasks
            \DB::table('tasks')->where('user_id', $user->id)->delete();
            \Log::info("Tasks deleted for user ID: {$user->id}");

            // Delete all user-related clients
            \DB::table('clients')->where('user_id', $user->id)->delete();
            \Log::info("Clients deleted for user ID: {$user->id}");

            // Delete the user's personal team (if exists)
            \DB::table('teams')->where('user_id', $user->id)->delete();
            \Log::info("Personal team deleted for user ID: {$user->id}");

            // Delete any other user-specific data here...

            /**
             * Future implementation for handling team-related deletions
             * --------------------------------------------------------
             * 1. If the user owns any teams:
             *    - Transfer ownership of the team to another team member.
             *    - If no other team members exist, archive the team.
             *    Example code for transferring ownership:
             *    foreach ($user->teams as $team) {
             *        if ($user->ownsTeam($team)) {
             *            $newOwner = $team->members()->first();
             *            if ($newOwner) {
             *                $team->owner_id = $newOwner->id;
             *                $team->save();
             *            } else {
             *                // Archive the team if no other members exist
             *            }
             *        }
             *    }
             * 
             * 2. Delete user-specific projects or tasks in their personal space:
             *    - Only delete projects/tasks that are not part of a team.
             *    - Ensure that team-related data (e.g., projects) is not deleted.
             *    Example:
             *    \DB::table('projects')->where('user_id', $user->id)->whereNull('team_id')->delete();
             */

            Auth::logout();

            // Delete the user from the local database
            $user->delete();
            \Log::info("User deleted from users table: {$user->id}");

            // Commit transaction once everything is successful
            \DB::commit();

            // Invalidate the session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            \Log::info("User deletion process completed for user ID: {$user->id}");
            return Redirect::to('/');
        } catch (\Exception $e) {
            \DB::rollBack(); // Rollback transaction if anything fails

            \Log::error("Error deleting user {$user->id}: " . $e->getMessage());

            return Redirect::route('profile.edit')->withErrors(['error' => 'An error occurred while trying to delete your account. Please try again later.']);
        }
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

    protected function getUserSubscriptionPlan($user): ?string
    {
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->active()) {
            return 'free'; // Assume 'free' if no active subscription
        }

        // Get the Stripe price ID
        $stripePrice = $subscription->stripe_price;

        // Use PlanService to translate the price ID to a plan name
        return $this->planService->getPlanName($stripePrice);
    }

    /**
     * Redirect the user to the Stripe Billing Portal.
     */
    public function redirectToBillingPortal(Request $request)
    {
        // Initialize Stripe with your secret key
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

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
