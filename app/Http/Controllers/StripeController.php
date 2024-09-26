<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Stripe\Webhook;
use Stripe\StripeClient;


class StripeController extends Controller
{
    public function showPaymentPage()
    {
        $user = Auth::user();

        // Set the Stripe secret key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create a Checkout Session for the Freelancer plan
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => 'price_1Q2RSpEEh64CES4EjOr0VQvr', // Replace with your actual Stripe price ID for the Freelancer plan
                'quantity' => 1,
            ]],
            'mode' => 'subscription', // Use 'payment' for one-time payments
            'success_url' => route('dashboard') . '?success=true', // Redirect URL after successful payment
            'cancel_url' => route('stripe.payment') . '?canceled=true', // Redirect URL for canceled payment
            'metadata' => [
                'user_id' => $user->id,
                'plan' => 'freelancer',
            ],
        ]);

        // Redirect the user to the Stripe Checkout page
        return redirect($session->url);
    }


    public function processPayment(Request $request)
    {
        $user = Auth::user();

        // Set the Stripe API key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Retrieve the PaymentIntent (this confirms the payment was processed)
            $paymentIntentId = $request->input('payment_intent'); // You need to pass this from the frontend if needed
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status == 'succeeded') {
                // Subscribe the user to the Freelancer plan using Stripe
                $priceId = 'price_1Q2RSpEEh64CES4EjOr0VQvr';  // Replace this with your actual Stripe price ID

                $subscription = $user->newSubscription('default', $priceId)->create($paymentIntent->payment_method);

                // Update the user’s plan
                $user->update([
                    'plan' => 'freelancer'
                ]);

                return redirect()->route('dashboard')->with('success', 'You have successfully upgraded to the Freelancer plan!');
            }

            return redirect()->back()->withErrors(['error' => 'Payment could not be completed.']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // public function upgrade(Request $request)
    // {
    //     $user = auth()->user();
    //     $user->subscription('default')->swap('freelancer_plan_id'); // Replace with your actual plan ID
    //     return redirect()->back()->with('status', 'Successfully upgraded your subscription!');
    // }

    // public function downgrade(Request $request)
    // {
    //     $user = auth()->user();
    //     $user->subscription('default')->swap('free_plan_id'); // Replace with your actual plan ID
    //     return redirect()->back()->with('status', 'Successfully downgraded your subscription!');
    // }

    // public function cancel(Request $request)
    // {
    //     $user = auth()->user();
    //     $user->subscription('default')->cancel();
    //     return redirect()->back()->with('status', 'Your subscription has been canceled.');
    // }

    // Webhook handler methods
    public function handleWebhook(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            // Verify the signature of the webhook
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // Handle the event based on its type
        $this->processEvent($event);

        // Return a 200 response to acknowledge receipt of the event
        return response('Webhook handled', 200);
    }

    // Process the event based on its type
    private function processEvent($event)
    {
        $subscription = $event->data->object;
        $user = User::where('stripe_id', $subscription->customer)->first();

        switch ($event->type) {
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdate($user, $subscription);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionCancellation($user, $subscription);
                break;

            case 'invoice.payment_succeeded':
                // Handle successful payment (optional)
                break;

            case 'invoice.payment_failed':
                // Handle payment failure (optional)
                break;

            case 'customer.subscription.trial_will_end':
                // Notify user about the trial ending (optional)
                break;

            // Add other cases as needed

            default:
                // Handle unexpected event types
                break;
        }
    }

    // Handle subscription updates
    private function handleSubscriptionUpdate($user, $subscription)
    {
        if ($user) {
            if ($subscription->status === 'canceled') {
                $this->downgradeUserToFreePlan($user);
            } else {
                // Handle other updates as necessary, like payment failures
            }
        }
    }

    // Handle subscription cancellation events
    private function handleSubscriptionCancellation($user, $subscription)
    {
        if ($user) {
            $this->downgradeUserToFreePlan($user);
        }
    }

    // Downgrade user to the Free plan
    private function downgradeUserToFreePlan($user)
    {
        // Cancel the current subscription (if not already done)
        $currentSubscription = $user->subscription('default');
        if ($currentSubscription) {
            $currentSubscription->cancel();
        }

        // Create a new subscription for the Free plan
        $newSubscription = \Stripe\Subscription::create([
            'customer' => $user->stripe_id, // Replace with your user’s Stripe ID
            'items' => [['price' => 'price_for_free_package']], // Replace with your Free package price ID
        ]);

        // Update the user in the database
        $user->update(['plan' => 'free']); // Update the user plan in your database
    }

    public function resumeSubscription(Request $request)
    {
        $user = $request->user();

        // Initialize Stripe client
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        // Fetch subscription from Stripe
        $subscription = $stripe->subscriptions->retrieve(
            $user->subscription('default')->stripe_id
        );

        // Check if the subscription is canceled but still active on Stripe
        if ($subscription->status === 'active' && $user->subscription('default')->cancelled() && is_null($user->subscription('default')->ends_at)) {
            try {
                // Resume the subscription
                $user->subscription('default')->resume();

                return redirect()->back()->with('success', 'Your subscription has been resumed.');
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['error' => 'Error resuming subscription: ' . $e->getMessage()]);
            }
        }

        return redirect()->back()->withErrors(['error' => 'Unable to resume your subscription.']);
    }
}
