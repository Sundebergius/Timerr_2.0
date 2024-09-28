<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use App\Services\PlanService;
use Illuminate\Http\Request;
use App\Models\User;
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
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

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
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntentId = $request->input('payment_intent');
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status == 'succeeded') {
                $priceId = 'price_1Q2RSpEEh64CES4EjOr0VQvr';
                $user->newSubscription('default', $priceId)->create($paymentIntent->payment_method);
                $user->update(['plan' => 'freelancer']);

                return redirect()->route('dashboard')->with('success', 'You have successfully upgraded to the Freelancer plan!');
            }

            return redirect()->back()->withErrors(['error' => 'Payment could not be completed.']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function handleWebhook(Request $request)
    {
        \Log::info('Webhook hit, starting process.');

        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            \Log::info('Webhook successfully verified.');
        } catch (\UnexpectedValueException $e) {
            \Log::error('Invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            \Log::error('Invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        \Log::info('Event Type: ' . $event->type);

        // Handle the event
        $this->processEvent($event);

        return response('Webhook handled', 200);
    }

    private function processEvent($event)
    {
        \Log::info("Stripe Webhook Event Received: " . $event->type);

        $session = $event->data->object;
        $user = User::where('stripe_id', $session->customer)->first();

        if (!$user) {
            \Log::error("No user found with Stripe customer ID: " . $session->customer);
            return;
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                \Log::info("Processing checkout.session.completed for user: " . $user->id);
                // Create subscription locally after successful payment
                $this->createSubscriptionForUser($user, $session);
                break;

            case 'customer.subscription.updated':
                \Log::info("Processing customer.subscription.updated for user: " . $user->id);
                $this->stripeService->updateSubscription($user, $session);
                break;

            case 'customer.subscription.deleted':
                \Log::info("Processing customer.subscription.deleted for user: " . $user->id);
                $this->stripeService->cancelAndArchiveUser($user);
                break;

            case 'invoice.payment_succeeded':
                \Log::info("Processing invoice.payment_succeeded for user: " . $user->id);
                // Handle successful payment if needed
                break;

            case 'invoice.payment_failed':
                \Log::error("Processing invoice.payment_failed for user: " . $user->id);
                // Handle payment failure
                break;

            default:
                \Log::warning("Unhandled event type: " . $event->type);
                // Handle other event types
                break;
        }
    }

    // Method to handle creating a subscription in your local database after successful payment
    private function createSubscriptionForUser($user, $session)
    {
        if ($user && isset($session->subscription)) {
            // Retrieve the Stripe subscription object
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripeSubscription = \Stripe\Subscription::retrieve($session->subscription);

            // Use Laravel Cashier's newSubscription method to create the subscription
            try {
                $user->newSubscription('default', $stripeSubscription->items->data[0]->price->id)
                    ->create(); // This handles creating the subscription in the subscriptions and subscription_items tables

                \Log::info("Successfully created subscription for user: {$user->id}");

            } catch (\Exception $e) {
                \Log::error("Error creating subscription for user {$user->id}: " . $e->getMessage());
            }

        } else {
            \Log::error("Failed to create subscription: user or session subscription missing.");
        }
    }

    public function subscribe(Request $request)
    {
        $user = $request->user();

        // Create a Stripe Checkout Session for the Freelancer plan
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'customer' => $user->stripe_id, // Pass the existing Stripe customer ID
            'line_items' => [[
                'price' => 'price_1Q2RSpEEh64CES4EjOr0VQvr', // Freelancer plan price ID
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('dashboard') . '?session_id={CHECKOUT_SESSION_ID}', // Redirect URL after successful payment
            'cancel_url' => route('profile.edit'), // Redirect URL for canceled payment
        ]);

        // Redirect the user to the Stripe Checkout page
        return redirect($session->url);
    }

    public function resumeSubscription(Request $request)
    {
        $user = $request->user();
        $result = $this->stripeService->resumeSubscription($user);

        if ($result) {
            return redirect()->back()->with('success', 'Your subscription has been resumed.');
        }

        return redirect()->back()->withErrors(['error' => 'Unable to resume your subscription.']);
    }
}
