<x-app-layout>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <div class="container mx-auto px-4 py-6">
        <!-- Flash Messages -->
        <div x-data="{ showAlert: false, alertType: '', alertMessage: '' }" x-init="
            @if (session('success'))
                showAlert = true;
                alertType = 'success';
                alertMessage = '{{ session('success') }}';
            @elseif (session('error'))
                showAlert = true;
                alertType = 'error';
                alertMessage = '{{ session('error') }}';
            @endif
        ">
            <template x-if="showAlert">
                <div :class="{'bg-green-100 border border-green-400 text-green-700': alertType === 'success', 'bg-red-100 border border-red-400 text-red-700': alertType === 'error'}"
                     class="px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline" x-text="alertMessage"></span>
                    <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="showAlert = false">
                        <svg class="h-6 w-6" :class="{'text-green-500': alertType === 'success', 'text-red-500': alertType === 'error'}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <title>Close</title>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
        
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-500 mb-4">Projects</h1>

            <!-- Project Counter -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-gray-800">
                    You have created <span class="text-blue-500">{{ $projectCount }}</span> out of <span class="text-blue-500">{{ $projectLimit }}</span> projects.
                </p>

                @if ($projectCount < $projectLimit)
                    <p class="text-green-500">You can create {{ $projectLimit - $projectCount }} more projects.</p>
                @else
                    <p class="text-red-500">You have reached your project limit.</p>
                @endif
            </div>

            <!-- Create Project Button -->
            <a href="{{ route('projects.create') }}"
                class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out {{ $projectCount >= $projectLimit ? 'opacity-50 cursor-not-allowed' : '' }}"
                @if ($projectCount >= $projectLimit) disabled @endif>
                Create Project
            </a>
        </div>

        <div class="flex flex-wrap justify-center gap-6">
            @foreach ($projects as $project)
            <div class="bg-white rounded-lg shadow-lg p-4 w-80 max-w-xs flex flex-col">
                <h2 class="text-xl font-bold mb-2">{{ $project->title }}</h2>
                <p class="mb-2">
                    <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $project->status == 'ongoing'
                            ? 'bg-green-100 text-green-800'
                            : ($project->status == 'nearing completion'
                                ? 'bg-yellow-100 text-yellow-800'
                                : ($project->status == 'overdue'
                                    ? 'bg-red-100 text-red-800'
                                    : ($project->status == 'completed'
                                        ? 'bg-gray-100 text-gray-800'
                                        : 'bg-blue-100 text-blue-800'))) }}">
                        {{ ucfirst($project->status) }}
                    </span>
                </p>
                <div class="mb-4">
                    <h3 class="text-sm font-semibold">Task Summary:</h3>
                    <ul class="list-disc pl-5">
                        @foreach ($project->tasks as $index => $task)
                            <li>{{ $index + 1 }}. <strong>{{ $task->title }}</strong>
                                ({{ $task->task_type }})
                            </li>
                        @endforeach
                        @if($project->client)
                            <li class="mt-2">
                                <i class="fas fa-user-check"></i>
                                Client: <strong>{{ $project->client->name }}</strong>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm font-semibold">Quick Stats:</h3>
                    <div class="space-y-2">
                        @php
                            $totalEarnings = 0;
                            $hourlyEarnings = 0;
                        @endphp
                        @if ($project->tasks->where('taskable_type', 'App\Models\TaskProject')->isNotEmpty())
                            @php
                                $projectEarnings = $project->tasks
                                    ->where('taskable_type', 'App\Models\TaskProject')
                                    ->sum(function ($task) {
                                        return $task->taskable->price;
                                    });
                                $totalEarnings += $projectEarnings;
                            @endphp
                            <div class="flex justify-between">
                                <span>Project Earnings:</span>
                                <span>{{ $projectEarnings }} DKK</span>
                            </div>
                        @endif
                        @if ($project->tasks->where('taskable_type', 'App\Models\TaskHourly')->isNotEmpty())
                            @php
                                $totalMinutesWorked = 0;
                                $hourlyEarnings = 0;
                                foreach (
                                    $project->tasks->where('taskable_type', 'App\Models\TaskHourly')
                                    as $task
                                ) {
                                    $earningsPerMinute = $task->taskable->rate_per_hour / 60;
                                    foreach ($task->taskable->registrationHourly as $registration) {
                                        $totalMinutesWorked += $registration->minutes_worked;
                                        $hourlyEarnings +=
                                            $registration->minutes_worked * $earningsPerMinute;
                                    }
                                }
                                $hourlyEarnings = ceil($hourlyEarnings);

                                $totalDays = floor($totalMinutesWorked / (60 * 24));
                                $totalHours = floor(($totalMinutesWorked / 60) % 24);
                                $totalMinutes = $totalMinutesWorked % 60;
                                $timeWorked = sprintf(
                                    '%dd %dh %dm',
                                    $totalDays,
                                    $totalHours,
                                    $totalMinutes,
                                );
                            @endphp
                            <div class="flex justify-between">
                                <span>Time Worked:</span>
                                <span>{{ $timeWorked }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Hourly Earnings:</span>
                                <span>{{ $hourlyEarnings }} DKK</span>
                            </div>
                        @endif
                        @php
                            $totalEarnings += $hourlyEarnings;
                        @endphp

                        @if ($project->tasks->where('taskable_type', 'App\Models\TaskProduct')->isNotEmpty())
                            @php
                                $productEarnings = 0;
                                $taskProducts = $project->tasks->where('taskable_type', 'App\Models\TaskProduct');
                            @endphp

                            @foreach ($taskProducts as $task)
                                @php
                                    $relatedTaskProducts = \App\Models\TaskProduct::where('task_id', $task->id)->with('product')->get();
                                @endphp

                                @foreach ($relatedTaskProducts as $taskProduct)
                                    @php
                                        $productEarnings += $taskProduct->product->price * $taskProduct->total_sold;
                                    @endphp
                                @endforeach
                            @endforeach

                            @php
                                $totalEarnings += $productEarnings;
                            @endphp

                            <div class="flex justify-between">
                                <span>Product Earnings:</span>
                                <span>{{ $productEarnings }} DKK</span>
                            </div>
                        @endif

                        @if ($project->tasks->where('taskable_type', 'App\Models\TaskDistance')->isNotEmpty())
                            @php
                                $travelCosts = 0;
                                foreach ($project->tasks->where('taskable_type', 'App\Models\TaskDistance') as $task) {
                                    $taskDistance = $task->taskable;
                                    $totalDistance = $taskDistance->registrationDistances->sum('distance');
                                    $travelCosts += $taskDistance->price_per_km * $totalDistance;
                                }
                                $totalEarnings += $travelCosts;
                            @endphp
                            <div class="flex justify-between">
                                <span>Travel Costs:</span>
                                <span>{{ $travelCosts }} DKK</span>
                            </div>
                        @endif
                        <div class="mt-4 flex justify-between font-bold">
                            <span>Total Earnings:</span>
                            <span>{{ $totalEarnings }} DKK</span>
                        </div>
                    </div>
                </div>
                <div class="mt-auto flex items-center space-x-4">
                    <a href="{{ route('projects.show', $project) }}" class="text-green-600 hover:text-green-700"><i class="fas fa-eye"></i></a>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirmDeletion()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                    </form>
                    <div class="relative inline-flex" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            More
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 max-h-60 overflow-y-auto">
                            <div class="py-1">
                                <a href="{{ route('projects.edit', $project) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                    Edit Project
                                </a>
                                <button @click="modalOpen = true" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
                                    Edit Client
                                </button>

                                <!-- Add to Calendar Button with Alpine.js modal -->
<div x-data="calendarComponent()">
    <!-- Trigger Add to Calendar Modal -->
    <button @click="openModal({{ $project->id }}, '{{ $project->title }}', '{{ $project->start_date ? $project->start_date->format('Y-m-d\TH:i') : 'N/A' }}', '{{ $project->end_date ? $project->end_date->format('Y-m-d\TH:i') : 'N/A' }}')"
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
        Add to Calendar
    </button>

    <!-- Add to Calendar Confirmation Modal -->
    <div x-show="showAddToCalendarModal" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-gray-800 bg-opacity-75 absolute inset-0" @click="closeModal"></div>

        <div class="bg-white p-6 rounded shadow-md z-10 max-w-md mx-auto">
            <h2 class="text-xl font-bold mb-4">Confirm Add to Calendar</h2>
            <p class="mb-6">Do you want to add this project to the calendar?</p>

            <div class="flex justify-end space-x-4">
                <!-- Cancel button -->
                <button @click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">
                    Cancel
                </button>

                <!-- Confirm Add to Calendar button -->
                <button @click="confirmAddToCalendar()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Confirm
                </button>
            </div>
        </div>
    </div>
                                </div>


                                {{-- Invoice implementation - not applicable until later and properly tested
                                <button @click="invoiceModalOpen = true" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
                                    Edit Invoice Status
                                </button>
                                @if($project->status == 'completed')
                                    <a href="{{ route('projects.invoice', $project) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                        View Invoice
                                    </a>
                                @endif --}}
                                <form method="POST" action="{{ route('projects.toggleCompletion', $project) }}" class="block text-start">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
                                        {{ $project->status == 'completed' ? 'Mark as ongoing' : 'Mark as completed' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination Links -->
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    </div>
</x-app-layout>




{{-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2" defer></script> --}}


{{-- <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script> --}}

{{-- <!-- Load jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

 <!-- Load Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- Load Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script> --}}

<script>
    function updateInvoiceStatus(projectId, status) {
        fetch('/projects/' + projectId + '/update-invoice-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: status
            })
        }).then(response => {
            if (!response.ok) {
                alert('Failed to update invoice status');
            }
        });
    }

    function updateClient(projectId, clientId) {
        fetch(`/projects/${projectId}/update-client`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    client_id: clientId
                })
            }).then(response => response.json())
            .then(data => console.log(data))
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    // // Load Select2 when the document is ready
    // $(document).ready(function() {
    //     loadSelect2();
    // });

    // Function to load Select2
    function loadSelect2() {
        // Check if Select2 is already initialized
        if (!$('.client-select').hasClass('select2-hidden-accessible')) {
            // Load jQuery
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js';
            script.onload = function() {
                // Load Select2 CSS
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
                document.head.appendChild(link);

                // Load Select2 JS
                var script2 = document.createElement('script');
                script2.type = 'text/javascript';
                script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                script2.onload = function() {
                    // Wait until the document is fully loaded
                    $(document).ready(function() {
                        // Initialize Select2
                        $('.client-select').select2();
                        // Make the select element visible
                        $('.client-select').css('display', 'block');
                        // Remove the hidden-select class
                        $('.client-select').removeClass('hidden-select');
                    });
                }
                document.head.appendChild(script2);
            }
            document.head.appendChild(script);
        }
    }

    // Function to unload Select2
    function unloadSelect2() {
        // Check if Select2 is initialized
        if ($('.client-select').hasClass('select2-hidden-accessible')) {
            // Destroy Select2
            $('.client-select').select2('destroy');
        }

        // Remove Select2 scripts and styles
        $('head').find('script[src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"]').remove();
        $('head').find('link[href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"]')
            .remove();
        $('head').find('script[src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"]')
            .remove();
    }

    function confirmDeletion() {
        return confirm('Are you sure you want to delete this project? This action cannot be undone.');
    }

    function calendarComponent() {
            return {
                showAddToCalendarModal: false,
                projectId: null,
                title: '',
                start: '',
                end: '',
                openModal(projectId, title, start, end) {
                    this.projectId = projectId;
                    this.title = title;
                    this.start = start;
                    this.end = end;
                    this.showAddToCalendarModal = true;
                },
                closeModal() {
                    this.showAddToCalendarModal = false;
                },
                confirmAddToCalendar() {
                    fetch('/events', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            title: this.title,
                            start: this.start,
                            end: this.end,
                            project_id: this.projectId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error("Failed to add to calendar: " + data.error);
                        } else {
                            console.log("Project added to calendar successfully!");
                        }
                        // Close modal
                        this.closeModal();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Close modal
                        this.closeModal();
                    });
                }
            }
        }
</script>
