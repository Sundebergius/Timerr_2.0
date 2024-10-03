<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Display unread notifications from the database using the banner -->
    @php
        $unreadNotifications = auth()->user()->unreadNotifications;
    @endphp

    @if ($unreadNotifications->count())
        @foreach ($unreadNotifications as $notification)
            <x-banner message="{{ $notification->data['message'] }}" />
            @php
                $notification->markAsRead();
            @endphp
        @endforeach
    @endif

     <!-- Upgrade Banner for Free Plan Users -->
    @if (is_null(auth()->user()->current_plan) || auth()->user()->current_plan === 'free')
        <div class="bg-yellow-100 p-4 rounded-lg text-center shadow-md mb-6">
            <h3 class="text-xl font-semibold text-yellow-800">Upgrade to Freelancer Plan</h3>
            <p class="text-yellow-600 mt-2">Unlock advanced features like automation and manage up to 25 clients with the Freelancer plan for just 99 kr./mo.</p>
            <a href="{{ route('stripe.checkout', ['plan' => 'freelancer']) }}" class="mt-4 inline-block bg-yellow-500 text-white py-2 px-6 rounded-lg shadow hover:bg-yellow-600">Upgrade Now</a>
        </div>
    @endif

    <!-- Added padding at the top -->
    <div class="pt-8"> <!-- Add padding at the top -->
        <!-- Centered Welcome Section with smaller buttons -->
        <div class="max-w-4xl mx-auto bg-blue-100 p-6 rounded-lg mb-6 text-center shadow-md">
            <h3 class="text-2xl font-semibold">Welcome to Timerr!</h3>
            <p class="text-gray-600 mt-2">Manage your freelance business efficiently. Let’s get you started:</p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 justify-center">
                <a href="/projects/create" class="bg-white text-blue-500 font-semibold text-sm py-2 px-4 rounded-lg hover:bg-gray-100 shadow mx-auto">
                    Create your first project
                </a>
                <a href="/clients/create" class="bg-white text-blue-500 font-semibold text-sm py-2 px-4 rounded-lg hover:bg-gray-100 shadow mx-auto">
                    Add your clients
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Projects Section -->
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Recent Projects</h3>
        @if($projects->isEmpty())
            <p class="text-gray-600">No recent projects found. 
                <a href="/projects/create" class="text-blue-500 hover:underline">Create a project now</a>.
            </p>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($projects as $project)
                    <li class="py-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800">{{ $project->title }}</h4>
                                <p class="text-gray-600 text-sm">{{ $project->description ?? 'No description provided' }}</p>
                                <p class="text-gray-500 text-sm">Start Date: {{ $project->start_date->format('F j, Y') }} | 
                                    @if ($project->end_date) 
                                        End Date: {{ $project->end_date->format('F j, Y') }} 
                                    @else 
                                        <em>End date not provided</em> 
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-green-600 font-semibold">Total Value: DKK {{ number_format($project->total_value, 2) }}</p>
                                <a href="{{ route('projects.show', $project->id) }}" class="text-blue-500 hover:underline">View Project</a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Top Clients Section -->
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Top Clients</h3>
        @if($clients->isEmpty())
            <p class="text-gray-600">No clients found.</p>
        @else
            <div class="hidden lg:block">
                <!-- Table for larger screens -->
                <table class="min-w-full bg-white rounded-lg shadow-sm">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Client Name</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Projects</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Contact</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($clients as $client)
                            <tr class="hover:bg-gray-100 transition-all">
                                <td class="px-4 py-3 font-medium">{{ $client->name }}</td>
                                <td class="px-4 py-3">{{ $client->projects_count }}</td>
                                <td class="px-4 py-3">{{ ucfirst($client->status) }}</td>
                                <td class="px-4 py-3">
                                    <a href="mailto:{{ $client->email }}" class="text-blue-500 hover:underline">{{ $client->email }}</a>
                                    <p class="text-sm text-gray-600">{{ $client->phone ?? 'N/A' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ $client->address }}</p>
                                    <p>{{ $client->city }}, {{ $client->zip_code }}, {{ $client->country }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Card layout for smaller screens -->
            <div class="block lg:hidden">
                @foreach ($clients as $client)
                    <div class="bg-white shadow rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-lg mb-2">{{ $client->name }}</h4>
                        <p class="text-gray-600"><strong>Projects:</strong> {{ $client->projects_count }}</p>
                        <p class="text-gray-600"><strong>Status:</strong> {{ ucfirst($client->status) }}</p>
                        <p class="text-gray-600"><strong>Contact:</strong> <a href="mailto:{{ $client->email }}" class="text-blue-500 hover:underline">{{ $client->email }}</a></p>
                        <p class="text-gray-600">{{ $client->phone ?? 'N/A' }}</p>
                        <p class="text-gray-600"><strong>Location:</strong> {{ $client->address }}, {{ $client->city }}, {{ $client->zip_code }}, {{ $client->country }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Calendar Section -->
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Your Schedule</h3>
        <div class="bg-gray-50 rounded-lg shadow-inner p-6">
            <x-calendar />
        </div>
    </div>
</x-app-layout>
