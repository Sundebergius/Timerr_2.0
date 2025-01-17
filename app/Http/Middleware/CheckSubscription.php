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
        $subscriptionPlan = $this->getUserSubscriptionPlan($user);
        $limits = $this->planService->getPlanLimits($subscriptionPlan);

        // Determine which type of resource is being created based on the route
        $isCreatingClient = $request->routeIs('clients.store');
        $isCreatingProject = $request->routeIs('projects.store');
        $isCreatingProduct = $request->routeIs('products.store');

        // Apply the respective limits based on the resource type
        if ($isCreatingClient && isset($limits['clients']) && $user->clients()->count() >= $limits['clients']) {
            return redirect()->back()->withErrors(['error' => "You have reached the maximum number of clients for the $subscriptionPlan plan."]);
        }

        if ($isCreatingProject && isset($limits['projects']) && $user->projects()->count() >= $limits['projects']) {
            return redirect()->back()->withErrors(['error' => "You have reached the maximum number of projects for the $subscriptionPlan plan."]);
        }

        if ($isCreatingProduct && isset($limits['products']) && $user->products()->count() >= $limits['products']) {
            return redirect()->back()->withErrors(['error' => "You have reached the maximum number of products for the $subscriptionPlan plan."]);
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
        // Fetch the user's subscription based on your local 'type' field
        $subscription = $user->subscriptions()->whereIn('type', ['default', 'canceled'])->first();

        // If no subscription exists, assume the user is on the 'free' plan
        if (!$subscription) {
            return 'free';
        }

        // Check if the subscription has ended (i.e., it's expired)
        if ($subscription->ends_at && $subscription->ends_at->isPast() && $subscription->type === 'canceled') {
            return 'free'; // Expired subscriptions revert to 'free'
        }

        // If the subscription is canceled but still within the grace period
        if ($subscription->type === 'canceled' && !$subscription->ends_at->isPast()) {
            \Log::info("User {$user->id} is on a grace period after cancellation.");
            return $this->planService->getPlanNameByPriceId($subscription->stripe_price);
        }

        // If the subscription is active (i.e., 'default')
        if ($subscription->type === 'default') {
            return $this->planService->getPlanNameByPriceId($subscription->stripe_price);
        }

        return 'free'; // Default to 'free' if no other conditions match
    }
}
