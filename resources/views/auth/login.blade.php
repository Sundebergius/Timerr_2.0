<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Centered Login Button -->
            <div class="flex items-center justify-center mt-6">
                <x-button class="w-full justify-center">
                    {{ __('Log in') }}
                </x-button>
            </div>

            <!-- Forgot Password and Sign Up Links -->
            <div class="flex items-center justify-between mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
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
                <span class="text-gray-600 text-sm font-semibold">Sign in with Google</span>
            </a>
        </div>

        <!-- Sign Up Link (Positioned Under Google Login) -->
        <div class="text-center mt-6">
            <span class="text-sm text-gray-600">Not a member?</span>
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                Sign up for free
            </a>
        </div>

    </x-authentication-card>
</x-guest-layout>
