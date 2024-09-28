<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @include('profile.partials.connect-google-account')
            </div>

            <x-section-border />
            
            <div class="mt-10 sm:mt-0">
                @include('profile.partials.subscription-management')
            </div>

            <x-section-border />

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
    <x-section-border />

    <div class="mt-10 sm:mt-0">
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-medium text-gray-900">
                {{ __('Delete Account') }}
            </h3>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-4">
                <x-input type="password" class="block w-full" name="password" placeholder="{{ __('Password') }}" required />
            </div>

            <div class="mt-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 disabled:opacity-25 transition">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </div>
@endif

        </div>
    </div>
</x-app-layout>
