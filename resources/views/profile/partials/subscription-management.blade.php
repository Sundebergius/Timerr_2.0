<section class="md:grid md:grid-cols-3 md:gap-6">
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Manage Your Subscription') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('View and manage your subscription plan in the billing portal.') }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ implode(', ', $errors->all()) }}
        </div>
    @endif

    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
            <p class="mt-4 text-sm text-gray-600">
                {{ __('Manage your subscription and billing details in the secure Stripe portal.') }}
            </p>

            <div class="space-y-4 mt-4">
                @inject('planService', 'App\Services\PlanService') <!-- Inject the PlanService -->
            
                @if(!$user->subscribed('default'))
                    <form method="POST" action="{{ route('stripe.subscribe') }}">
                        @csrf
                        <x-primary-button>
                            {{ __('Subscribe to Freelancer Plan') }}
                        </x-primary-button>
                    </form>
                @else
                    <!-- Manage Subscription Button -->
                    <form method="GET" action="{{ route('billing.portal') }}">
                        <x-primary-button>
                            {{ __('Manage Subscription') }}
                        </x-primary-button>
                    </form>
                @endif
            
                @if($subscription = $user->subscription('default'))
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h3 class="font-bold text-lg">{{ __('Subscription Details') }}</h3>
                        <p>{{ __('Plan:') }} {{ ucfirst($planService->getPlanNameByPriceId($subscription->stripe_price)) }}</p>
                        <p>{{ __('Status:') }} {{ ucfirst($subscription->stripe_status) }}</p>
            
                        @if($subscription->ends_at)
                            <p>{{ __('Ends At:') }} {{ $subscription->ends_at->format('F j, Y') }}</p>
                        @endif
            
                        @if($subscription->onTrial())
                            <p>{{ __('Trial Ends At:') }} {{ $subscription->trial_ends_at->format('F j, Y') }}</p>
                        @endif
            
                        <!-- Show subscription items if necessary -->
                        @if(!empty($subscription->items))
                            <h4 class="mt-2 font-bold">{{ __('Subscription Items') }}</h4>
                            <ul>
                                @foreach($subscription->items as $item)
                                    <li>{{ __('Item: ') }} {{ ucfirst($planService->getPlanNameByProductId($item['stripe_product'])) }} - {{ __('Quantity: ') }} {{ $item['quantity'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            
                <!-- Resume Subscription Button (Only show if the subscription is canceled but within the grace period) -->
                @if($subscription && $subscription->canceled() && !$subscription->ended())
                    <form method="POST" action="{{ route('subscription.resume') }}">
                        @csrf
                        <x-primary-button>
                            {{ __('Resume Subscription') }}
                        </x-primary-button>
                    </form>
                @endif
            </div>
            
        </div>
    </div>
</section>
