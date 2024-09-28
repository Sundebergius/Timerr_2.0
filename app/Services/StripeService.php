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
            // Get the current billing period end
            $currentPeriodEnd = \Carbon\Carbon::createFromTimestamp($subscriptionObject->current_period_end);
            $isCancelAtPeriodEnd = $subscriptionObject->cancel_at_period_end;
            
            // Always update the `ends_at` field with the current period end from Stripe
            $endsAt = $currentPeriodEnd;

            // Handle different statuses and adjust the type accordingly
            $type = match ($subscriptionObject->status) {
                'active' => $isCancelAtPeriodEnd ? 'canceled' : 'active',  // Mark as canceled if cancel_at_period_end is true
                'canceled' => $subscription->ends_at && $subscription->ends_at->isPast() ? 'expired' : 'canceled', // Mark as expired if ends_at has passed
                default => $subscription->type,  // Retain the type if status is unrecognized
            };

            // Update the local subscription with the new status, 'ends_at' date, and 'type'
            $subscription->update([
                'stripe_status' => $subscriptionObject->status,  // Update to reflect actual Stripe status
                'ends_at' => $endsAt,  // Always set ends_at to the current billing period end
                'type' => $type,  // Update the subscription type based on the status
                'updated_at' => now(),
            ]);

            \Log::info("Updated subscription for user {$user->id} with status {$subscriptionObject->status}, type {$type}, and ends_at {$endsAt}.");
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
            // Check if the subscription has been canceled but is within the grace period
            $subscription = $user->subscription('default');
            
            if ($subscription && $subscription->onGracePeriod()) {
                $subscription->resume();
                \Log::info("Resumed subscription for user {$user->id}");
                
                // Ensure `ends_at` remains unchanged, as it reflects the next billing cycle
                $currentPeriodEnd = \Carbon\Carbon::createFromTimestamp($subscription->asStripeSubscription()->current_period_end);

                // Update the subscription to ensure the status is correct, but keep `ends_at`
                $subscription->update([
                    'stripe_status' => 'active',  // Mark the subscription as active again
                    'type' => 'active',  // Change the type back to active
                    'updated_at' => now(),
                ]);

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
