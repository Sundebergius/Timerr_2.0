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

        // Use the config helper to retrieve the Stripe secret from the services configuration
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createSubscriptionForUser(User $user, $subscriptionId)
    {
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeSubscription = \Stripe\Subscription::retrieve($subscriptionId);

            // Log subscription details
            Log::info("Stripe subscription retrieved: " . json_encode($stripeSubscription));

            // Check for trial period and payment method
            $trialEnd = $stripeSubscription->trial_end
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_end)
                : null;

            if ($stripeSubscription->default_payment_method) {
                $paymentMethod = \Stripe\PaymentMethod::retrieve($stripeSubscription->default_payment_method);
                if ($paymentMethod && $paymentMethod->card) {
                    $user->update([
                        'pm_type' => $paymentMethod->card->brand,
                        'pm_last_four' => $paymentMethod->card->last4,
                    ]);
                    Log::info("Updated payment method for user: " . $user->id);
                }
            }

            // Update the local subscription data
            $subscription = $user->subscriptions()->updateOrCreate(
                ['stripe_id' => $stripeSubscription->id],
                [
                    'type' => 'default',
                    'stripe_status' => $stripeSubscription->status,
                    'stripe_price' => $stripeSubscription->items->data[0]->price->id,
                    'quantity' => $stripeSubscription->items->data[0]->quantity,
                    'trial_ends_at' => $trialEnd,
                    'ends_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                ]
            );

            // Update subscription items
            foreach ($stripeSubscription->items->data as $item) {
                \DB::table('subscription_items')->updateOrInsert(
                    ['subscription_id' => $subscription->id, 'stripe_id' => $item->id],
                    [
                        'stripe_product' => $item->price->product,
                        'stripe_price' => $item->price->id,
                        'quantity' => $item->quantity,
                    ]
                );
            }

            Log::info("Successfully updated subscription items for user: " . $user->id);
            
            // Flash success message for both banner and action-message
            session()->flash('flash.banner', 'Subscription successfully created!');
            session()->flash('flash.bannerStyle', 'success');
            session()->flash('message', 'Subscription successfully created!');

        } catch (\Exception $e) {
            Log::error("Error creating subscription for user {$user->id}: " . $e->getMessage());

            // Flash error message for both banner and action-message
            session()->flash('flash.banner', 'There was an issue creating your subscription. Please try again.');
            session()->flash('flash.bannerStyle', 'danger');
            session()->flash('error', 'There was an issue creating your subscription. Please try again.');
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

            $type = match ($subscriptionObject->status) {
                'active' => $isCancelAtPeriodEnd ? 'canceled' : 'default',
                'canceled' => $subscription->ends_at && $subscription->ends_at->isPast() ? 'expired' : 'canceled',
                default => $subscription->type,
            };

            if ($subscriptionObject->status === 'canceled' && !$isCancelAtPeriodEnd) {
                $subscription->update([
                    'stripe_status' => $subscriptionObject->status,
                    'ends_at' => now(),
                    'type' => 'canceled',
                    'updated_at' => now(),
                ]);

                Log::info("Subscription for user {$user->id} has been immediately canceled.");

                // Flash success message
                session()->flash('message', 'Your subscription has been canceled.');
            } else {
                $subscription->update([
                    'stripe_status' => $subscriptionObject->status,
                    'ends_at' => $currentPeriodEnd,
                    'type' => $type,
                    'updated_at' => now(),
                ]);

                Log::info("Updated subscription for user {$user->id} with status {$subscriptionObject->status}.");

                // Flash success message
                session()->flash('message', 'Your subscription has been updated.');
            }
        } else {
            Log::error("No subscription found for user {$user->id} with Stripe subscription ID: {$subscriptionObject->id}");

            // Flash error message
            session()->flash('error', 'There was an issue updating your subscription. Please try again.');
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

                    Log::info("Canceled subscription for user {$user->id} with Stripe subscription ID: {$subscriptionObject->id}");

                    // Flash success message
                    session()->flash('message', 'Your subscription has been successfully canceled.');
    
                } else {
                    Log::info("No active subscription found for user {$user->id}");
                    session()->flash('error', 'No active subscription found to cancel.');
                }
            } catch (\Exception $e) {
                Log::error("Error canceling subscription for user {$user->id}: " . $e->getMessage());
    
                // Flash error message
                session()->flash('error', 'There was an issue canceling your subscription. Please try again.');
            }
        } else {
            Log::info("No Stripe customer ID found for user {$user->id}");
            session()->flash('error', 'No Stripe customer ID found.');
        }
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

    // Handle trial logic and error handling when creating a subscription
    public function handleSubscription(User $user, $planName, $trialDays = null)
    {
        try {
            // Use PlanService to get the price ID for the selected plan
            $priceId = $this->planService->getPriceId($planName);

            if (!$priceId) {
                Log::error("Invalid plan selected: {$planName}");
                return false;
            }

            // Check if the user is eligible for a trial (not already used)
            if (!$user->subscriptions()->exists() && !$user->subscriptions->first()->trial_used) {
                $trialDays = $trialDays ?? 30; // Apply trial days if eligible
            } else {
                $trialDays = 0; // No trial for existing subscribers
            }

            // Create the subscription
            $subscription = $user->newSubscription('default', $priceId)
                ->trialDays($trialDays)
                ->create();

            // Mark the trial as used in the local database
            $subscription->update(['trial_used' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error creating subscription for user {$user->id}: " . $e->getMessage());
            return false;
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
