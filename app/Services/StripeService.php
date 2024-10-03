<?php

namespace App\Services;

use Stripe\StripeClient;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Services\PlanService;
use App\Notifications\SubscriptionUpdated;

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

            // Use Cashier's subscription method to update local subscription
            $subscription = $user->subscriptions()->updateOrCreate(
                ['stripe_id' => $stripeSubscription->id],
                [
                    'type' => 'default',
                    'stripe_status' => $stripeSubscription->status,
                    'stripe_price' => $stripeSubscription->items->data[0]->price->id,
                    'quantity' => $stripeSubscription->items->data[0]->quantity,
                    'trial_ends_at' => $trialEnd,
                    'ends_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // No need to manually save, updateOrCreate does this

            // Update subscription items
            if (!empty($stripeSubscription->items->data)) {
                foreach ($stripeSubscription->items->data as $item) {
                    \DB::table('subscription_items')->updateOrInsert(
                        ['subscription_id' => $subscription->id, 'stripe_id' => $item->id],
                        [
                            'stripe_product' => $item->price->product,
                            'stripe_price' => $item->price->id,
                            'quantity' => $item->quantity,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    Log::info("Updated subscription item for user: " . $user->id);
                }
            } else {
                Log::warning("No subscription items found for user {$user->id}");
            }

            Log::info("Successfully updated subscription items for user: " . $user->id);

            // Send notification after successful subscription creation
            $user->notify(new SubscriptionUpdated('Subscription successfully created!'));

        } catch (\Exception $e) {
            Log::error("Error creating subscription for user {$user->id}: " . $e->getMessage());

            // Notify the user of failure
            $user->notify(new SubscriptionUpdated('There was an issue creating your subscription. Please try again.'));
        }
    }

    // Handle subscription updates from Stripe webhooks
    public function updateSubscription($user, $subscriptionObject)
    {
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Find the subscription in your local database
        $subscription = $user->subscriptions()->where('stripe_id', $subscriptionObject->id)->first();

        if ($subscription) {
            $currentPeriodEnd = \Carbon\Carbon::createFromTimestamp($subscriptionObject->current_period_end);
            $isCancelAtPeriodEnd = $subscriptionObject->cancel_at_period_end;

            // Determine the type based on Stripe status
            $type = match ($subscriptionObject->status) {
                'active' => $isCancelAtPeriodEnd ? 'canceled' : 'default', // 'default' while active even with cancel_at_period_end
                'canceled' => $subscription->ends_at && $subscription->ends_at->isPast() ? 'expired' : 'canceled',
                default => $subscription->type,
            };

            // Check if the user has updated their payment method
            if ($subscriptionObject->default_payment_method) {
                $paymentMethod = \Stripe\PaymentMethod::retrieve($subscriptionObject->default_payment_method);
                if ($paymentMethod && $paymentMethod->card) {
                    // Update user's payment method in the database
                    $user->update([
                        'pm_type' => $paymentMethod->card->brand,
                        'pm_last_four' => $paymentMethod->card->last4,
                    ]);
                    Log::info("Updated payment method for user: {$user->id}");
                }
            }

            // Update the subscription based on whether it's being canceled at the period's end
            if ($isCancelAtPeriodEnd) {
                $subscription->update([
                    'stripe_status' => 'canceled',
                    'ends_at' => $currentPeriodEnd, // End at the current period end
                    'type' => 'canceled', // Mark as 'canceled' to indicate it's in the process
                    'updated_at' => now(),
                ]);

                Log::info("Subscription for user {$user->id} will cancel at the end of the billing period.");
                $user->notify(new SubscriptionUpdated('Your subscription has been set to cancel at the end of the period.'));
            } else {
                $subscription->update([
                    'stripe_status' => $subscriptionObject->status,
                    'ends_at' => $currentPeriodEnd,
                    'type' => $type,
                    'updated_at' => now(),
                ]);

                Log::info("Updated subscription for user {$user->id} with status {$subscriptionObject->status}.");
                $user->notify(new SubscriptionUpdated('Your subscription has been updated.'));
            }

            // Now update or insert subscription items
            foreach ($subscriptionObject->items->data as $item) {
                \DB::table('subscription_items')->updateOrInsert(
                    ['subscription_id' => $subscription->id, 'stripe_id' => $item->id],
                    [
                        'stripe_product' => $item->price->product,
                        'stripe_price' => $item->price->id,
                        'quantity' => $item->quantity,
                        'updated_at' => now(), // Always update 'updated_at'
                        'created_at' => \DB::raw('IFNULL(created_at, NOW())'), // Preserve 'created_at' for existing rows
                    ]
                );
            }
        } else {
            Log::error("No subscription found for user {$user->id} with Stripe subscription ID: {$subscriptionObject->id}");

            // Flash error message
            $user->notify(new SubscriptionUpdated('There was an issue updating your subscription. Please try again.'));
        }
    }

    // Handle full subscription cancelation (end of grace period or immediate cancel)
    public function cancelSubscription(User $user, $subscriptionObject)
    {
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        \Log::info("Attempting to cancel subscription for user: {$user->id}");

        if ($user->stripe_id) {
            try {
                // Find the subscription in your local database
                $subscription = $user->subscriptions()->where('stripe_id', $subscriptionObject->id)->first();

                if ($subscription && !$subscription->ended()) {
                    // Immediate or final cancellation (grace period ended)
                    $subscription->update([
                        'stripe_status' => 'canceled',
                        'ends_at' => now(), // Final cancellation, end now
                        'type' => 'expired', // Mark as 'expired'
                        'updated_at' => now(),
                    ]);

                    Log::info("Subscription for user {$user->id} has been fully canceled.");
                    $user->notify(new SubscriptionUpdated('Your subscription has been fully canceled and ended.'));
                } else {
                    Log::info("No active subscription found for user {$user->id}");
                    $user->notify(new SubscriptionUpdated('No active subscription found to cancel.'));
                }
            } catch (\Exception $e) {
                Log::error("Error canceling subscription for user {$user->id}: " . $e->getMessage());
                $user->notify(new SubscriptionUpdated('There was an issue canceling your subscription. Please try again.'));
            }
        } else {
            Log::info("No Stripe customer ID found for user {$user->id}");
            $user->notify(new SubscriptionUpdated('No Stripe customer ID found.'));
        }
    }

    public function handleTrialEndingSoon(User $user)
    {
        // Notify the user about their trial ending
        $user->notify(new SubscriptionUpdated('Your trial will end soon.'));
        
        // You can log or do additional actions here if needed
        Log::info("Notified user {$user->id} that their trial is ending soon.");
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
