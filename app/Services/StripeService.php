<?php

namespace App\Services;

use Stripe\StripeClient;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Services\PlanService;


class StripeService
{
    protected $stripe;
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    // Archive the Stripe customer (or delete based on your preference)
    public function archiveUser(User $user)
    {
        if ($user->stripe_id) {
            try {
                $this->stripe->customers->update($user->stripe_id, [
                    'metadata' => ['status' => 'deleted'],
                ]);

                \Log::info("Archived Stripe customer for user {$user->id}");
            } catch (\Exception $e) {
                \Log::error("Error archiving Stripe customer for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    // Permanently delete a Stripe customer
    public function deleteUser(User $user)
    {
        if ($user->stripe_id) {
            try {
                $this->stripe->customers->delete($user->stripe_id);
                \Log::info("Deleted Stripe customer for user {$user->id}");
            } catch (\Exception $e) {
                \Log::error("Error deleting Stripe customer for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    // Handle subscription updates from Stripe webhooks
    public function updateSubscription(User $user, $subscription)
    {
        if ($user) {
            $userSubscription = $user->subscription('default');
            if ($userSubscription) {
                $userSubscription->update([
                    'stripe_status' => $subscription->status,
                    'stripe_price' => $subscription->items->data[0]->price->id, // Update the Stripe price
                ]);

                \Log::info("Updated subscription for user {$user->id} with price ID {$subscription->items->data[0]->price->id}");
            }
        }
    }

    // Downgrade the user to the Free plan
    public function downgradeToFree(User $user)
    {
        if ($user->subscription('default')) {
            try {
                $user->subscription('default')->cancel();
                \Log::info("Downgraded subscription for user {$user->id} to free plan.");
            } catch (\Exception $e) {
                \Log::error("Error downgrading subscription for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    // Cancel subscription and archive user in Stripe
    public function cancelAndArchiveUser(User $user)
{
    \Log::info("Attempting to cancel and archive user in Stripe: {$user->id}");

    if ($user->stripe_id) {
        try {
            // Cancel any active subscriptions
            $subscription = $user->subscription('default');
            if ($subscription) {
                $subscription->cancel();
                \Log::info("Canceled subscription for user {$user->id}");
            } else {
                \Log::info("No active subscription found for user {$user->id}");
            }

            // Archive the customer in Stripe by adding metadata
            $this->stripe->customers->update($user->stripe_id, [
                'metadata' => ['status' => 'deleted'],
            ]);

            \Log::info("Archived customer in Stripe for user {$user->id}");
        } catch (\Exception $e) {
            \Log::error("Error canceling and archiving customer for user {$user->id}: " . $e->getMessage());
        }
    } else {
        \Log::info("No Stripe customer ID found for user {$user->id}");
    }
}


    // Resume a canceled subscription
    public function resumeSubscription(User $user)
    {
        if ($user->subscription('default')->cancelled() && is_null($user->subscription('default')->ends_at)) {
            try {
                $user->subscription('default')->resume();
                \Log::info("Resumed subscription for user {$user->id}");
                return true;
            } catch (\Exception $e) {
                \Log::error("Error resuming subscription for user {$user->id}: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}
