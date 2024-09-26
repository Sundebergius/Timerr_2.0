<x-form-section submit="connectGoogleAccount">
    <x-slot name="title">
        {{ __('Connect Google Account') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Connect your Google account to sync your calendar and other features.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            @if (auth()->user()->google_id)
                <p class="text-sm text-gray-600">{{ __('Your Google account is connected.') }}</p>
                <form method="POST" action="{{ route('google.disconnect') }}">
                    @csrf
                    <x-button>
                        {{ __('Disconnect Google Account') }}
                    </x-button>
                </form>
            @else
                <form method="GET" action="{{ route('google.redirect') }} ">
                    <x-button>
                        {{ __('Connect Google Account') }}
                    </x-button>
                </form>
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        @if (session('status') === 'google-connected')
            <x-action-message on="saved">
                {{ __('Connected.') }}
            </x-action-message>
        @endif
    </x-slot>
</x-form-section>
