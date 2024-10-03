<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\StripeService;
use Illuminate\Support\Facades\Log;
// might not need this
use Stripe\Stripe;
use Stripe\Subscription;

class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event; // The Stripe event object

    protected $stripeService;

    /**
     * Create a new job instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(StripeService $stripeService): void
    {
        $this->stripeService = $stripeService;

        Log::info("Processing Stripe Webhook: " . $this->event->type);

        // Process the event based on the event type
        $object = $this->event->data->object;

        // Fetch the customer associated with the event
        $customer_id = $object->customer ?? null;

        if (!$customer_id) {
            Log::error("No customer ID found in event: " . $this->event->id);
            return;
        }

        $user = User::where('stripe_id', $customer_id)->first();
        if (!$user) {
            Log::error("No user found with Stripe customer ID: " . $customer_id);
            return;
        }

        // Wrap event processing in a transaction
        DB::beginTransaction();
        try {
            switch ($this->event->type) {
                case 'checkout.session.completed':
                    Log::info("Processing checkout.session.completed for user: " . $user->id);
                    if (isset($object->subscription)) {
                        $this->stripeService->createSubscriptionForUser($user, $object->subscription);
                    } else {
                        Log::error("No subscription ID found in checkout session for user: " . $user->id);
                    }
                    break;

                case 'customer.subscription.created':
                    Log::info("Processing customer.subscription.created for user: " . $user->id);
                    $this->stripeService->createSubscriptionForUser($user, $object->id);
                    break;

                case 'customer.subscription.updated':
                    $this->stripeService->updateSubscription($user, $object);
                    break;

                case 'customer.subscription.deleted':
                    $this->stripeService->cancelSubscription($user, $object);
                    break;

                case 'invoice.payment_succeeded':
                    Log::info("Processing invoice.payment_succeeded for user: " . $user->id);
                    break;

                case 'invoice.payment_failed':
                    Log::error("Processing invoice.payment_failed for user: " . $user->id);
                    break;

                default:
                    Log::warning("Unhandled event type: " . $this->event->type);
                    break;
            }
            DB::commit();
        } catch (\Exception $e) {
            Log::error('Error processing event: ' . $e->getMessage());
            DB::rollBack();
        }
    }
}
