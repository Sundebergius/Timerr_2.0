<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <!-- Add a hidden input field for the plan -->
            <input type="hidden" name="plan" value="{{ $plan }}">

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />
                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                    'terms_of_service' => '<a target="_blank" href="https://timerr.dk/terms-of-service" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                    'privacy_policy' => '<a target="_blank" href="https://timerr.dk/privacy-policy" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <!-- Centered Register Button -->
            <div class="flex items-center justify-center mt-6">
                <x-button class="w-full justify-center">
                    {{ __('Register') }}
                </x-button>
            </div>

            <!-- Already Registered Link -->
            <div class="flex items-center justify-center mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>
            </div>
        </form>

        <!-- Separator with "or" -->
        <div class="flex items-center my-6">
            <hr class="w-full border-gray-300">
            <span class="px-2 text-sm text-gray-500">or</span>
            <hr class="w-full border-gray-300">
        </div>

        <!-- Social Login Section (Google) -->
        <div class="flex items-center justify-center">
            <a href="{{ route('google.login') }}" class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow hover:bg-gray-100 transition-colors duration-200 ease-in-out">
                <img src="https://cdn.studioninja.co/build/master-pipeline-15-5b26b/resources/dist/assets/images/icons/integration-icons/google-logo.svg" alt="Google Logo" width="28" height="28" class="mr-3">
                <span class="text-gray-600 text-sm font-semibold">Sign up with Google</span>
            </a>
        </div>

    </x-authentication-card>
</x-guest-layout>
