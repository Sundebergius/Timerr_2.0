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
                    $subscription = $user->subscription();
                @endphp
            
                @if(!$subscription || $subscription->canceled() && !$subscription->onGracePeriod())
                    <!-- If no subscription or subscription is fully canceled (past grace period), show subscribe button -->
                    <form method="POST" action="{{ route('stripe.subscribe') }}">
                        @csrf
                        <x-primary-button>
                            {{ __('Subscribe to Freelancer Plan') }}
                        </x-primary-button>
                    </form>
                @else
                    <!-- If user has an active or canceled subscription within the grace period, direct to Stripe's billing portal -->
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
                        <p>{{ __('Status:') }} {{ ucfirst($subscription->stripe_status) }}</p>
            
                        @if($subscription->active() && $subscription->ends_at)
                            <p>{{ __('Current Billing Period Ends:') }} {{ $subscription->ends_at->format('F j, Y') }}</p>
                        @elseif($subscription->canceled() && $subscription->ends_at)
                            <p>{{ __('Ends At:') }} {{ $subscription->ends_at->format('F j, Y') }}</p>
                        @endif
            
                        @if($subscription->onTrial())
                            <p>{{ __('Trial Ends At:') }} {{ $subscription->trial_ends_at->format('F j, Y') }}</p>
                        @endif
            
                        <!-- Show add-ons if they exist -->
                        @if(!empty($subscription->items))
                            <h4 class="mt-2 font-bold">{{ __('Add-Ons') }}</h4>
                            <ul>
                                @foreach($subscription->items as $item)
                                    @if($item['stripe_product'] !== 'base_subscription_product_id') <!-- Use your base product ID -->
                                        <li>{{ __('Item: ') }} {{ ucfirst($planService->getPlanNameByProductId($item['stripe_product'])) }} - {{ __('Quantity: ') }} {{ $item['quantity'] }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>            
        </div>
    </div>
</section>
