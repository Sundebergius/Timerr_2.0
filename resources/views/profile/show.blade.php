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

            {{-- @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>
                <x-section-border />
            @endif --}}

            <div class="mt-10 sm:mt-0">
                @include('profile.partials.connect-google-account')
            </div>

            <x-section-border />
            
            <div class="mt-10 sm:mt-0">
                @include('profile.partials.subscription-management')
            </div>

            {{-- <x-section-border /> --}}

            {{-- <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div> --}}

            <!-- Start Delete Account Section -->
            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <section class="md:grid md:grid-cols-3 md:gap-6">
                    <div class="md:col-span-1">
                        <div class="px-4 sm:px-0">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Delete Account') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Permanently delete your account and all its resources.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 md:mt-0 md:col-span-2">
                        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-lg">
                            <form method="POST" action="{{ route('profile.destroy') }}" id="delete-account-form">
                                @csrf
                                @method('DELETE')

                                <p class="text-sm text-gray-600">
                                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please ensure you have downloaded any data you wish to keep.') }}
                                </p>

                                <!-- Password input initially hidden -->
                                <div class="mt-4 hidden" id="password-container">
                                    <label for="password" class="block font-medium text-sm text-gray-700">{{ __('Password') }}</label>
                                    <input type="password" id="password" name="password" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" placeholder="{{ __('Password') }}" required>
                                    @if ($errors->has('password'))
                                        <span class="text-sm text-red-600">{{ $errors->first('password') }}</span>
                                    @endif
                                </div>

                                <!-- Delete Account Button -->
                                <div class="mt-4">
                                    <button type="button" id="show-password" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 disabled:opacity-25 transition">
                                        {{ __('Delete Account') }}
                                    </button>

                                    <!-- Final Delete Button (visible after password field is shown) -->
                                    <button type="submit" id="confirm-delete" class="hidden inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 disabled:opacity-25 transition">
                                        {{ __('Confirm Deletion') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            @endif
            <!-- End Delete Account Section -->

        </div>
    </div>
</x-app-layout>

<script>
    document.getElementById('show-password').addEventListener('click', function() {
        // Show password input field and the final delete button
        document.getElementById('password-container').classList.remove('hidden');
        document.getElementById('confirm-delete').classList.remove('hidden');
        
        // Hide the initial delete button
        document.getElementById('show-password').classList.add('hidden');
    });
</script>
