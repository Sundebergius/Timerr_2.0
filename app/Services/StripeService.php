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
    public function updateSubscription($user, $subscriptionObject)
    {
        // Find the subscription in your local database
        $subscription = $user->subscriptions()->where('stripe_id', $subscriptionObject->id)->first();

        if ($subscription) {
            $currentPeriodEnd = \Carbon\Carbon::createFromTimestamp($subscriptionObject->current_period_end);
            $isCancelAtPeriodEnd = $subscriptionObject->cancel_at_period_end;
            
            // Determine the correct `ends_at` value
            $endsAt = $isCancelAtPeriodEnd ? $currentPeriodEnd : null;

            // Update the local subscription with the new status and `ends_at` date
            $subscription->update([
                'stripe_status' => $subscriptionObject->status,  // Update to reflect the actual subscription status from Stripe
                'ends_at' => $endsAt,  // Update ends_at if cancel_at_period_end is true
                'updated_at' => now(),
            ]);

            // Log subscription status update
            \Log::info("Updated subscription for user {$user->id} with price ID {$subscriptionObject->items->data[0]->price->id} and status {$subscriptionObject->status}.");
        } else {
            \Log::error("No subscription found for user {$user->id} with Stripe subscription ID: {$subscriptionObject->id}");
        }
    }

    // Downgrade the user to the Free plan
    public function downgradeToFree(User $user)
    {
        if ($user->subscription('default')) {
            try {
                $user->subscription('default')->cancel();
                \Log::info("Downgraded subscription for user {$user->id} to the free plan.");
            } catch (\Exception $e) {
                \Log::error("Error downgrading subscription for user {$user->id}: " . $e->getMessage());
            }
        } else {
            \Log::info("No active subscription found for user {$user->id} to downgrade.");
        }
    }

    // Cancel subscription and archive user in Stripe
    public function cancelAndArchiveUser(User $user)
    {
        \Log::info("Attempting to cancel and archive user in Stripe: {$user->id}");

        if ($user->stripe_id) {
            try {
                $subscription = $user->subscription('default');
                if ($subscription && !$subscription->ended()) {
                    $subscription->cancel();
                    \Log::info("Canceled subscription for user {$user->id}");

                    // Update the local database to mark the subscription as canceled
                    $subscription->update([
                        'stripe_status' => 'canceled',
                        'ends_at' => $subscription->cancel_at_period_end ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_end) : now(),  // Mark when it ends
                        'updated_at' => now(),
                    ]);
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
        try {
            // Check if the subscription has been canceled but is within the grace period (meaning it's not fully ended)
            $subscription = $user->subscription('default');
            
            if ($subscription && $subscription->onGracePeriod()) {
                $subscription->resume();
                \Log::info("Resumed subscription for user {$user->id}");
                return true;
            } else {
                \Log::warning("Cannot resume subscription for user {$user->id}. Subscription is either not canceled or grace period has ended.");
            }
        } catch (\Exception $e) {
            \Log::error("Error resuming subscription for user {$user->id}: " . $e->getMessage());
        }

        return false;
    }

}
