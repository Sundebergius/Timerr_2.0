<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use App\Services\PlanService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use \Stripe\Webhook;
use Stripe\StripeClient;

class StripeController extends Controller
{
    protected $stripeService;
    protected $planService;

    public function __construct(StripeService $stripeService, PlanService $planService)
    {
        $this->stripeService = $stripeService;
        $this->planService = $planService;
    }

    public function showPaymentPage()
    {
        $user = Auth::user();

        // Set the Stripe secret key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Use PlanService to get the Freelancer plan price ID
        $freelancerPriceId = $this->planService->getPriceId('freelancer');

        // Create a Checkout Session for the Freelancer plan
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $freelancerPriceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription', // Use 'payment' for one-time payments
            'subscription_data' => [ // Add this key to hold subscription-specific data
                'trial_period_days' => 30, // Optional: Set a trial period for the subscription
            ],
            'success_url' => route('dashboard') . '?success=true',
            'cancel_url' => route('stripe.payment') . '?canceled=true',
            'metadata' => [
                'user_id' => $user->id,
                'plan' => 'freelancer',
            ],
        ]);

        return redirect($session->url);
    }

    public function processPayment(Request $request)
    {
        $user = Auth::user();
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntentId = $request->input('payment_intent');
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status == 'succeeded') {
                // Fetch the plan name from the request or use a default (e.g., 'freelancer')
                $planName = $request->input('plan', 'freelancer');

                // Use PlanService to get the price ID for the selected plan
                $priceId = $this->planService->getPriceId($planName);

                if (!$priceId) {
                    return redirect()->back()->withErrors(['error' => 'Invalid subscription plan.']);
                }

                // Check if the user has already used the trial
                if (!$user->trial_used) {
                    // Apply 30-day trial only if the user hasn't used it
                    $user->newSubscription('default', $priceId)
                        ->trialDays(30) // Apply 30-day trial period
                        ->create($paymentIntent->payment_method);

                    // Mark that the user has used their trial and save the trial end date
                    $user->update([
                        'trial_used' => true,
                        'trial_ends_at' => now()->addDays(30), // Store the trial end date locally
                    ]);
                } else {
                    // No trial for users who have already used it
                    $user->newSubscription('default', $priceId)
                        ->create($paymentIntent->payment_method);
                }

                // Update the user's plan in the database
                $user->update(['plan' => $planName]);

                return redirect()->route('dashboard')->with('success', 'You have successfully upgraded to the ' . ucfirst($planName) . ' plan!');
            }

            return redirect()->back()->withErrors(['error' => 'Payment could not be completed.']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function handleWebhook(Request $request)
    {
        \Log::info('Webhook hit, starting process.');

        $endpoint_secret = config('services.stripe.webhook_secret');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            \Log::info('Webhook successfully verified. Event Type: ' . $event->type);
        } catch (\UnexpectedValueException $e) {
            \Log::error('Invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            \Log::error('Invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Process the event
        try {
            $this->processEvent($event);
        } catch (\Exception $e) {
            \Log::error('Error processing event: ' . $e->getMessage());
            return response()->json(['error' => 'Error processing event'], 500);
        }

        return response('Webhook handled', 200);
    }

    private function processEvent($event)
    {
        \Log::info("Stripe Webhook Event Received: " . $event->type);

        // Identify the type of object based on the event type
        $object = $event->data->object;

        // Fetch the customer associated with the event
        $customer_id = isset($object->customer) ? $object->customer : null;
        if (!$customer_id) {
            \Log::error("No customer ID found in event: " . $event->id);
            return;
        }

        $user = User::where('stripe_id', $customer_id)->first();
        if (!$user) {
            \Log::error("No user found with Stripe customer ID: " . $customer_id);
            return;
        }

        // Wrap event processing in a transaction
        DB::beginTransaction();
        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    \Log::info("Processing checkout.session.completed for user: " . $user->id);
                    $this->createSubscriptionForUser($user, $object);  // Use session object
                    break;

                case 'customer.subscription.created':
                    \Log::info("Processing customer.subscription.created for user: " . $user->id);
                    $this->createSubscriptionForUser($user, $object);  // Use subscription object
                    break;

                case 'customer.subscription.updated':
                    $subscription = $user->subscriptions()->where('stripe_id', $object->id)->first();
                    if (!$subscription) {
                        \Log::error("Subscription not found for user {$user->id} during update. Delaying handling.");
                        // You can decide to retry this webhook later, or log the issue and investigate.
                        return;
                    }
                    $this->stripeService->updateSubscription($user, $object);
                    break;

                case 'customer.subscription.deleted':
                    \Log::info("Processing customer.subscription.deleted for user: " . $user->id);
                    $this->stripeService->cancelSubscription($user, $object);  // Use subscription object, but don't archive the user
                    break;

                case 'invoice.payment_succeeded':
                    \Log::info("Processing invoice.payment_succeeded for user: " . $user->id);
                    \Log::info("Invoice status: " . $object->status);  // Log the payment status
                    break;

                case 'invoice.payment_failed':
                    \Log::error("Processing invoice.payment_failed for user: " . $user->id);
                    \Log::error("Failure reason: " . $object->last_payment_error->message);  // Log why the payment failed
                    break;

                default:
                    \Log::warning("Unhandled event type: " . $event->type);
                    break;
            }
            DB::commit();
        } catch (\Exception $e) {
            \Log::error('Error processing event: ' . $e->getMessage());
            DB::rollBack();
        }
    }

// Method to handle creating or updating a subscription in your local database after successful payment
private function createSubscriptionForUser($user, $session)
{
    try {
        if ($user && isset($session->subscription)) {
            // Set up Stripe client
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeSubscription = \Stripe\Subscription::retrieve($session->subscription);

            // Log detailed info about the subscription object
            \Log::info("Stripe subscription retrieved: " . json_encode($stripeSubscription));

            // Log trial period if applicable
            $trialEnd = null;
            if ($stripeSubscription->trial_end) {
                $trialEnd = \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_end);
                \Log::info("User {$user->id} has a trial until {$trialEnd->toDateTimeString()}");
            }

            // Always ensure 'type' is set to 'default'
            $type = 'default';  // Assuming 'default' is the intended value for active subscriptions

            // Update or create the subscription locally with 'type' field
            $localSubscription = $user->subscriptions()->updateOrCreate(
                ['stripe_id' => $stripeSubscription->id],
                [
                    'type' => $type,  // Set type field
                    'stripe_status' => $stripeSubscription->status,
                    'stripe_price' => $stripeSubscription->items->data[0]->price->id,
                    'quantity' => $stripeSubscription->items->data[0]->quantity,
                    'trial_ends_at' => $trialEnd, // Set trial end date
                    'ends_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                    'updated_at' => now(),
                ]
            );

            \Log::info("Subscription for user {$user->id} created/updated in the database.");

            // Ensure the subscription exists before updating subscription items
            if ($localSubscription) {
                // Manually update or insert subscription items
                foreach ($stripeSubscription->items->data as $item) {
                    \DB::table('subscription_items')->updateOrInsert(
                        ['subscription_id' => $localSubscription->id, 'stripe_id' => $item->id],
                        [
                            'stripe_product' => $item->price->product,
                            'stripe_price' => $item->price->id,
                            'quantity' => $item->quantity,
                            'created_at' => $localSubscription->created_at ?? now(),  // Set created_at if new
                            'updated_at' => now(),
                        ]
                    );
                }

                \Log::info("Successfully updated subscription items for user: {$user->id}");
            }
        } else {
            \Log::error("Failed to create subscription: user or session subscription missing.");
        }
    } catch (\Exception $e) {
        \Log::error("Error in createSubscriptionForUser for user {$user->id}: " . $e->getMessage());
    }
}

    
    public function subscribe(Request $request)
    {
        $user = $request->user();

        // Set the plan name, e.g., 'freelancer' (you can pass this dynamically in the future)
        $planName = $request->input('plan', 'freelancer'); // Default to 'freelancer'

        // Use the PlanService to get the price ID
        $priceId = $this->planService->getPriceId($planName);

        if (!$priceId) {
            return redirect()->back()->withErrors(['error' => 'Invalid subscription plan.']);
        }

        // Create a Stripe Checkout Session for the selected plan
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        
        $subscriptionData = [];

        // Check if the user has already used the trial
        if (!$user->trial_used) {
            $subscriptionData['trial_period_days'] = 30; // Apply 30-day free trial
        }

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'customer' => $user->stripe_id, // Pass the existing Stripe customer ID
            'line_items' => [[
                'price' => $priceId, // Use the price ID from PlanService
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'subscription_data' => $subscriptionData, // Add the trial if applicable
            'success_url' => route('dashboard') . '?session_id={CHECKOUT_SESSION_ID}', // Redirect URL after successful payment
            'cancel_url' => route('dashboard'), // Redirect URL for canceled payment
        ]);

        // Redirect the user to the Stripe Checkout page
        return redirect($session->url);
    }

    public function resumeSubscription(Request $request)
    {
        \Log::info("Attempting to resume subscription for user: " . $request->user()->id);

        $user = $request->user();
        $result = $this->stripeService->resumeSubscription($user);

        if ($result) {
            \Log::info("Subscription resumed successfully for user: " . $user->id);
            return redirect()->back()->with('success', 'Your subscription has been resumed.');
        }

        \Log::error("Failed to resume subscription for user: " . $user->id);
        return redirect()->back()->withErrors(['error' => 'Unable to resume your subscription.']);
    }

}
