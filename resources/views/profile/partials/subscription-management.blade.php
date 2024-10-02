<section class="md:grid md:grid-cols-3 md:gap-6">
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Manage Your Subscription') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('View and manage your subscription plan and add-ons in the billing portal.') }}
            </p>
        </div>
    </div>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
            @if(session('success'))
                <div class="alert bg-green-500 text-white px-4 py-3 rounded relative" role="alert" id="alert-success">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-white" role="button" id="close-success-alert" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M14.348 5.652a.999.999 0 10-1.414-1.414L10 7.172 7.066 4.238a.999.999 0 10-1.414 1.414l2.934 2.934-2.934 2.934a.999.999 0 101.414 1.414L11.414 10l2.934-2.934z"/></svg>
                    </span>
                </div>
            @endif
    
            @if($errors->any())
                <div class="alert bg-red-500 text-white px-4 py-3 rounded relative" role="alert" id="alert-danger">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ implode(', ', $errors->all()) }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-white" role="button" id="close-danger-alert" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M14.348 5.652a.999.999 0 10-1.414-1.414L10 7.172 7.066 4.238a.999.999 0 10-1.414 1.414l2.934 2.934-2.934 2.934a.999.999 0 101.414 1.414L11.414 10l2.934-2.934z"/></svg>
                    </span>
                </div>
            @endif

            <p class="mt-4 text-sm text-gray-600">
                {{ __('Manage your subscription and billing details in the secure Stripe portal.') }}
            </p>

            <div class="space-y-4 mt-4">
                @inject('planService', 'App\Services\PlanService') <!-- Inject the PlanService -->
                
                <!-- Custom check for subscription status -->
                @php
                    // Check if the user has a subscription
                    $subscription = $user->subscriptions()->whereIn('type', ['default', 'canceled'])->first();
                    
                    if (!$subscription) {
                        // Treat user as "free" if no subscription exists
                        $subscription = (object) [
                            'type' => 'free',
                            'limits' => $this->planService->getPlanLimits('free'),  // Define the free plan limits in PlanService
                        ];
                        \Log::info('User is on the free plan.');
                    } else {
                        \Log::info('User subscription:', [$subscription]);
                    }

                    // Check if the subscription has ended based on the current date and the ends_at field
                    $subscriptionExpired = $subscription && $subscription->ends_at && $subscription->ends_at->isPast();
                @endphp

                @if(!$subscription || $subscriptionExpired)
                    <!-- Show subscribe button if no active subscription or subscription has ended -->
                    <form method="POST" action="{{ route('stripe.subscribe') }}">
                        @csrf
                        <input type="hidden" name="plan" value="freelancer">
                        <x-primary-button>
                            {{ __('Subscribe to Freelancer Plan') }}
                        </x-primary-button>
                    </form>                    
                @else
                    <!-- If user has an active or canceled subscription within grace period, direct to Stripe's billing portal -->
                    <form method="GET" action="{{ route('billing.portal') }}">
                        <x-primary-button>
                            {{ __('Manage Subscription') }}
                        </x-primary-button>
                    </form>
                @endif
            
                <!-- Display subscription details if the user has a subscription -->
                @if($subscription)
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h3 class="font-bold text-lg">{{ __('Subscription Details') }}</h3>
                        <p>{{ __('Plan:') }} {{ ucfirst($planService->getPlanNameByPriceId($subscription->stripe_price)) }}</p>
            
                        <!-- Display the appropriate subscription status -->
                        @if($subscription->type === 'canceled' && !$subscriptionExpired)
                            <p>{{ __('Status:') }} {{ __('Canceled (Active until ') }} {{ $subscription->ends_at->format('F j, Y') }}{{ __(')') }}</p>
                        @elseif($subscription->type === 'default' && $subscription->active())
                            <p>{{ __('Status:') }} {{ ucfirst($subscription->stripe_status) }}</p>
                        @elseif($subscriptionExpired)
                            <p>{{ __('Status: Expired') }}</p>
                        @endif
            
                        <!-- Display period end dates -->
                        @if($subscription->active() && $subscription->ends_at)
                            <p>{{ __('Current Billing Period Ends:') }} {{ $subscription->ends_at->format('F j, Y') }}</p>
                        @elseif($subscription->canceled() && $subscription->ends_at)
                            <p>{{ __('Ends At:') }} {{ $subscription->ends_at->format('F j, Y') }}</p>
                        @endif
            
                        <!-- Display trial period if applicable -->
                        @if($subscription->onTrial())
                            <p>{{ __('Trial Ends At:') }} {{ $subscription->trial_ends_at->format('F j, Y') }}</p>
                        @endif
            
                        <!-- Show add-ons if they exist -->
                        {{-- @if(!empty($subscription->items))
                        <h4 class="mt-2 font-bold">{{ __('Add-Ons') }}</h4>
                        <ul>
                            @foreach($subscription->items as $item)
                                @if(isset($item['stripe_product']) && $item['stripe_product'] !== 'base_subscription_product_id' && $subscription->stripe_status === 'active') 
                                    <!-- Ensure only active subscriptions show items -->
                                    <li>{{ __('Item: ') }} {{ ucfirst($planService->getPlanNameByProductId($item['stripe_product'])) }} - {{ __('Quantity: ') }} {{ $item['quantity'] }}</li>
                                @endif
                            @endforeach
                        </ul>
                        @endif --}}
                    </div>
                @endif
            </div>            
        </div>
    </div>
</section>
