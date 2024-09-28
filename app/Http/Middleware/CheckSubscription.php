<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Get the user's translated subscription plan (e.g., 'free', 'freelancer')
        $subscriptionPlan = $this->getUserSubscriptionPlan($user);

        // Apply restrictions based on the subscription plan
        switch ($subscriptionPlan) {
            case 'free':
                if ($user->clients()->count() >= 5) {
                    return redirect()->back()->withErrors(['error' => 'You have reached the maximum number of clients for the Free plan.']);
                }
                if ($user->projects()->count() >= 3) {
                    return redirect()->back()->withErrors(['error' => 'You have reached the maximum number of projects for the Free plan.']);
                }
                if ($user->products()->count() >= 5) {
                    return redirect()->back()->withErrors(['error' => 'You have reached the maximum number of products/services for the Free plan.']);
                }
                break;

            case 'freelancer':
                if ($user->clients()->count() >= 15) {
                    return redirect()->back()->withErrors(['error' => 'You have reached the maximum number of clients for the Freelancer plan.']);
                }
                // Additional restrictions for 'freelancer' can go here.
                break;

            // Add future plans (e.g., 'enterprise') as needed
        }

        return $next($request);
    }

    /**
     * Determine the user's subscription plan based on Stripe product and price IDs.
     *
     * @param  \App\Models\User  $user
     * @return string|null
     */
    protected function getUserSubscriptionPlan($user): ?string
    {
        // Fetch the user's active subscription
        $subscription = $user->subscription('default'); // 'default' is the name of the default subscription plan in Cashier

        if (!$subscription || !$subscription->active()) {
            \Log::info("No active subscription found for user: {$user->id}");
            return 'free'; // Assume 'free' if no active subscription
        }

        // Get the Stripe price ID from the active subscription
        $stripePrice = $subscription->stripe_price; // Use $subscription->stripe_price to get the price ID

        // Use the PlanService to get the plan name from the price ID
        $plan = $this->planService->getPlanNameByPriceId($stripePrice);

        \Log::info("Subscription plan for user {$user->id}: " . $plan);

        return $plan;
    }

}
