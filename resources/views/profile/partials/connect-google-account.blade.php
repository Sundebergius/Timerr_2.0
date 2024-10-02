<section class="md:grid md:grid-cols-3 md:gap-6">
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Connect Google Account') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Connect your Google account to sync your calendar and other features.') }}
            </p>
        </div>
    </div>
    
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
            <p class="mt-4 text-sm text-gray-600">
                {{ __('You can connect your Google account to sync your calendar and enjoy automatic updates with other services.') }}
            </p>
            <div class="space-y-4">

                @if (session('link_google_prompt'))
                    <!-- Prompt the user to link Google account -->
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">{{ __('Google Account Detected!') }}</strong>
                        <span class="block sm:inline">{{ __('This Google account is already associated with another account. Would you like to log into that account instead or link this account to your existing account?') }}</span>
                        <form method="POST" action="{{ route('link-google') }}">
                            @csrf
                            <input type="hidden" name="google_user_id" value="{{ session('google_user_id') }}">
                            <input type="hidden" name="google_user_token" value="{{ session('google_user_token') }}">
                            <div class="mt-4">
                                <x-primary-button>{{ __('Link Google Account') }}</x-primary-button>
                                <a href="{{ route('login') }}" class="underline text-sm text-gray-600 hover:text-gray-900">{{ __('Log into the Google Account') }}</a>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Normal Google account connection flow -->
                    @if (auth()->user()->google_id)
                        <p class="text-sm text-gray-600">{{ __('Your Google account is connected.') }}</p>
                        <form method="POST" action="{{ route('google.disconnect') }}">
                            @csrf
                            <div class="flex items-center mt-5">
                                <x-primary-button>{{ __('Disconnect Google Account') }}</x-primary-button>
                            </div>
                        </form>
                    @else
                        <form method="GET" action="{{ route('google.redirect') }}">
                            <div class="flex items-center mt-5">
                                <x-primary-button>{{ __('Connect Google Account') }}</x-primary-button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>

            @if (session('status') === 'google-connected')
                <div class="text-sm text-gray-600 mt-4">
                    {{ __('Connected.') }}
                </div>
            @endif
        </div>
    </div>
</section>
