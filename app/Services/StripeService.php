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

            // Determine the new type and the logic for `ends_at`
            $type = match ($subscriptionObject->status) {
                'active' => $isCancelAtPeriodEnd ? 'canceled' : 'default',
                'canceled' => $subscription->ends_at && $subscription->ends_at->isPast() ? 'expired' : 'canceled',
                default => $subscription->type,
            };

            // Handle immediate cancellation logic
            if ($subscriptionObject->status === 'canceled' && !$isCancelAtPeriodEnd) {
                // Immediate cancellation: Set `ends_at` to now, since the user loses access immediately
                $subscription->update([
                    'stripe_status' => $subscriptionObject->status,
                    'ends_at' => now(), // Ends now since it is immediate cancellation
                    'type' => 'canceled',
                    'updated_at' => now(),
                ]);
                \Log::info("Subscription for user {$user->id} has been immediately canceled.");
            } else {
                // Otherwise, update `ends_at` based on the current period end and handle other cases
                $subscription->update([
                    'stripe_status' => $subscriptionObject->status,
                    'ends_at' => $currentPeriodEnd, // This handles standard cancellation at period end or resumption scenarios
                    'type' => $type,
                    'updated_at' => now(),
                ]);
            }

            \Log::info("Updated subscription for user {$user->id} with status {$subscriptionObject->status}, type {$type}, and ends_at {$currentPeriodEnd}.");
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

    // Cancel subscription but do not archive user in Stripe
    public function cancelSubscription(User $user, $subscriptionObject)
    {
        \Log::info("Attempting to cancel subscription for user: {$user->id}");

        if ($user->stripe_id) {
            try {
                // Find the subscription in your local database
                $subscription = $user->subscriptions()->where('stripe_id', $subscriptionObject->id)->first();

                if ($subscription && !$subscription->ended()) {
                    // Update the subscription to mark it as canceled
                    $subscription->update([
                        'stripe_status' => 'canceled',
                        'ends_at' => \Carbon\Carbon::createFromTimestamp($subscriptionObject->current_period_end),
                        'updated_at' => now(),
                    ]);

                    \Log::info("Canceled subscription for user {$user->id} with Stripe subscription ID: {$subscriptionObject->id}");
                } else {
                    \Log::info("No active subscription found for user {$user->id}");
                }
            } catch (\Exception $e) {
                \Log::error("Error canceling subscription for user {$user->id}: " . $e->getMessage());
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
                    'type' => 'default',  // Change the type back to 'default'
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
