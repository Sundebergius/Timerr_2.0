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
                <!-- Instead of using <a>, we use a form with method GET and an x-primary-button component for styling -->
                <form method="GET" action="{{ route('billing.portal') }}">
                    <x-primary-button>
                        {{ __('Manage Subscription') }}
                    </x-primary-button>
                </form>
                @if($user->subscription('default'))
                    <pre>{{ print_r($user->subscription('default')->toArray(), true) }}</pre>
                @endif
                <!-- Resume Subscription Button (Only show if the subscription is canceled but within the grace period) -->
                @if($user->subscription('default') && $user->subscription('default')->canceled() && !$user->subscription('default')->ended())
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
