<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Set favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('Timerr_icon.svg') }}">


        <!-- FullCalendar CSS -->
        {{-- <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/core/main.min.css' rel='stylesheet' />
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/daygrid/main.min.css' rel='stylesheet' />
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/timegrid/main.min.css' rel='stylesheet' /> --}}

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Include Heroicons -->
        {{-- <script src="https://unpkg.com/heroicons@1.0.6/dist/outline.min.js"></script> --}}

        <!-- Include Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <!-- Include Blade UI Kit for Heroicons -->
        {{-- @bladeUiIcons --}}
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
