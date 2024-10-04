<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Meta Tags for SEO -->
        <meta name="description" content="Timerr - The ultimate project management tool for freelancers and small businesses.">
        <meta name="keywords" content="project management, freelancers, small businesses, tasks, clients, calendar">
        <meta name="author" content="MatSoft">

        <!-- Open Graph for Social Sharing -->
        <meta property="og:title" content="Timerr - The Project Management Tool for Freelancers">
        <meta property="og:description" content="Manage projects, tasks, clients, and your business with ease.">
        <meta property="og:image" content="{{ asset('Timerr_icon.svg') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:card" content="summary_large_image">

        <!-- Title and Favicon -->
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('Timerr_icon.svg') }}">

        <!-- Font -->
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>
            body {
                font-family: 'Figtree', sans-serif, system-ui;
            }
        </style>

        <!-- Alpine.js for handling interactive UI components -->
        {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

        <!-- Livewire Global Error Handling -->
        <script>
            document.addEventListener('livewire:load', function () {
                window.livewire.on('error', (message) => {
                    alert(message); // Display alert or use a better UI element
                });
            });
        </script>

        <!-- CookieBot Script -->
        {{-- <script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" data-cbid="a702dbe6-6020-43c6-ab79-b0aa42911391" data-blockingmode="auto" type="text/javascript" defer></script> --}}

        <!-- Font Awesome Icons -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js" defer></script>

        <!-- Scripts and Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
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
