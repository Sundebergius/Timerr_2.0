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

        <div class="container mx-auto px-4">
            {{-- <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">Project Dashboard</h1> --}}
        
            <div class="flex flex-wrap justify-center gap-8">
                @foreach ($projects as $project)
                <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-w-xs flex flex-col">
                    <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-4 tracking-wide border-b-4 border-blue-500"> {{ $project->title }} </h2>
        
                    <!-- Status Badge -->
                    <p class="mb-4 text-center">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $project->status == 'ongoing' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $project->status == 'nearing completion' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $project->status == 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $project->status == 'completed' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </p>
        
                    <!-- Task Summary -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Task Summary</h3>

                        <!-- Grouped Tasks by Type -->
                        <div class="space-y-8">
                            
                            <!-- Project-Based Tasks -->
                            @if($project->tasks->where('task_type', 'project_based')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-blue-500 mb-2">Project-Based Tasks</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($project->tasks->where('task_type', 'project_based') as $index => $task)
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200">
                                        <div class="flex-shrink-0 mr-4">
                                            <i class="fas fa-project-diagram text-blue-500 text-2xl"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="text-md font-bold text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">Project-Based</p>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-auto">{{ $loop->iteration }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <!-- Hourly Tasks -->
                            @if($project->tasks->where('task_type', 'hourly')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-green-500 mb-2">Hourly Tasks</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($project->tasks->where('task_type', 'hourly') as $index => $task)
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200">
                                        <div class="flex-shrink-0 mr-4">
                                            <i class="fas fa-clock text-green-500 text-2xl"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="text-md font-bold text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">Hourly</p>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-auto">{{ $loop->iteration }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Product-Based Tasks -->
                            @if($project->tasks->where('task_type', 'product')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-yellow-500 mb-2">Product-Based Tasks</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($project->tasks->where('task_type', 'product') as $index => $task)
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200">
                                        <div class="flex-shrink-0 mr-4">
                                            <i class="fas fa-box-open text-yellow-500 text-2xl"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="text-md font-bold text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">Product</p>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-auto">{{ $loop->iteration }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Distance-Based Tasks -->
                            @if($project->tasks->where('task_type', 'distance')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-purple-500 mb-2">Distance-Based Tasks</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($project->tasks->where('task_type', 'distance') as $index => $task)
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200">
                                        <div class="flex-shrink-0 mr-4">
                                            <i class="fas fa-road text-purple-500 text-2xl"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="text-md font-bold text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">Distance</p>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-auto">{{ $loop->iteration }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Other Tasks -->
                            @if($project->tasks->where('task_type', 'other')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-gray-500 mb-2">Other Tasks</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($project->tasks->where('task_type', 'other') as $index => $task)
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200">
                                        <div class="flex-shrink-0 mr-4">
                                            <i class="fas fa-tasks text-gray-500 text-2xl"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="text-md font-bold text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">Other</p>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-auto">{{ $loop->iteration }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Client Information -->
                        @if($project->client)
                        <div class="mt-6 flex items-center space-x-2 bg-blue-100 text-blue-700 p-2 rounded-md">
                            <i class="fas fa-user-check text-blue-500"></i>
                            <span>Client: <strong>{{ $project->client->name }}</strong></span>
                        </div>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Stats</h3>

                        @php
                            $totalEarnings = 0; // Initialize total earnings at the start
                        @endphp

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- Project Earnings -->
                            @if ($project->tasks->where('taskable_type', 'App\Models\TaskProject')->isNotEmpty())
                            @php
                            $projectEarnings = $project->tasks
                                ->where('taskable_type', 'App\Models\TaskProject')
                                ->sum(function ($task) {
                                    // Check for null values
                                    return $task->taskable ? $task->taskable->price ?? 0 : 0;
                                });
                            $totalEarnings += $projectEarnings;
                            @endphp
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
                                <i class="fas fa-coins text-yellow-500 text-xl mr-4"></i>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-600">Project Earnings</p>
                                    <h4 class="text-lg font-bold text-gray-800">{{ number_format($projectEarnings, 2) }} DKK</h4>
                                </div>
                            </div>
                            @endif

                            <!-- Hourly Earnings -->
                            @if ($project->tasks->where('taskable_type', 'App\Models\TaskHourly')->isNotEmpty())
                            @php
                            $totalMinutesWorked = 0;
                            $hourlyEarnings = 0;
                            foreach ($project->tasks->where('taskable_type', 'App\Models\TaskHourly') as $task) {
                                if ($task->taskable) {
                                    $earningsPerMinute = $task->taskable->rate_per_hour ? $task->taskable->rate_per_hour / 60 : 0;
                                    foreach ($task->taskable->registrationHourly as $registration) {
                                        $totalMinutesWorked += $registration->minutes_worked ?? 0;
                                        $hourlyEarnings += ($registration->minutes_worked ?? 0) * $earningsPerMinute;
                                    }
                                }
                            }
                            $hourlyEarnings = ceil($hourlyEarnings);
                            $totalDays = floor($totalMinutesWorked / (60 * 24));
                            $totalHours = floor(($totalMinutesWorked / 60) % 24);
                            $totalMinutes = $totalMinutesWorked % 60;
                            $timeWorked = sprintf('%dd %dh %dm', $totalDays, $totalHours, $totalMinutes);
                            $totalEarnings += $hourlyEarnings;
                            @endphp
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
                                <i class="fas fa-clock text-green-500 text-xl mr-4"></i>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-600">Time Worked</p>
                                    <h4 class="text-lg font-bold text-gray-800">{{ $timeWorked }}</h4>
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
                                <i class="fas fa-money-bill-wave text-green-500 text-xl mr-4"></i>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-600">Hourly Earnings</p>
                                    <h4 class="text-lg font-bold text-gray-800">{{ number_format($hourlyEarnings, 2) }} DKK</h4>
                                </div>
                            </div>
                            @endif

                            <!-- Product Earnings -->
                            @if ($project->tasks->where('taskable_type', 'App\Models\TaskProduct')->isNotEmpty())
                            @php
                            $productEarnings = 0;
                            foreach ($project->tasks->where('taskable_type', 'App\Models\TaskProduct') as $task) {
                                $taskProducts = \App\Models\TaskProduct::where('task_id', $task->id)->with('product')->get();
                                foreach ($taskProducts as $taskProduct) {
                                    if ($taskProduct->product) {
                                        $productEarnings += ($taskProduct->product->price ?? 0) * ($taskProduct->quantity ?? 0);
                                    }
                                }
                            }
                            $totalEarnings += $productEarnings;
                            @endphp
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
                                <i class="fas fa-box-open text-yellow-500 text-xl mr-4"></i>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-600">Product Earnings</p>
                                    <h4 class="text-lg font-bold text-gray-800">{{ number_format($productEarnings, 2) }} DKK</h4>
                                </div>
                            </div>
                            @endif

                            <!-- Travel Costs -->
                            @if ($project->tasks->where('taskable_type', 'App\Models\TaskDistance')->isNotEmpty())
                            @php
                            $travelCosts = $project->tasks
                                ->where('taskable_type', 'App\Models\TaskDistance')
                                ->sum(function ($task) {
                                    return $task->taskable 
                                        ? $task->taskable->price_per_km * $task->taskable->registrationDistances->sum('distance') 
                                        : 0;
                                });
                            $totalEarnings += $travelCosts;
                            @endphp
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
                                <i class="fas fa-road text-purple-500 text-xl mr-4"></i>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-600">Travel Costs</p>
                                    <h4 class="text-lg font-bold text-gray-800">{{ number_format($travelCosts, 2) }} DKK</h4>
                                </div>
                            </div>
                            @endif

                            <!-- Total Earnings -->
                            <div class="flex items-center p-4 bg-blue-50 rounded-lg shadow-md">
                                <i class="fas fa-piggy-bank text-blue-500 text-xl mr-4"></i>
                                <div class="flex-grow">
                                    <p class="text-sm text-blue-700">Total Earnings</p>
                                    <h4 class="text-xl font-extrabold text-blue-700">{{ number_format($totalEarnings, 2) }} DKK</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center space-x-4">
                        <!-- Plus icon (create task) -->
                        <a href="{{ route('projects.tasks.create', $project) }}" class="flex items-center bg-blue-500 text-white px-3 py-2 rounded-full hover:bg-blue-600 transition-all duration-300">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <span>Add Task</span>
                        </a>
                    
                        <!-- Eye icon (view project) -->
                        <a href="{{ route('projects.show', $project) }}" class="text-green-600 hover:text-green-700">
                            <i class="fas fa-eye text-xl"></i>
                        </a>
                    
                        <!-- Trash icon (delete project) -->
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirmDeletion()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash text-xl"></i>
                            </button>
                        </form>
                    
                        <!-- More button and dropdown (always opens upwards) -->
                        <div class="relative inline-block" x-data="{ open: false }" @click.away="open = false">
                            <!-- More button -->
                            <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none">
                                More
                            </button>
                    
                            <!-- Dropdown for more actions, opening upwards -->
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute z-50 bottom-full mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 overflow-y-auto max-h-60">
                                <div class="py-1">
                                    <!-- Edit Project -->
                                    <a href="{{ route('projects.edit', $project) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                        Edit Project
                                    </a>
                                    <!-- Modal for editing client -->
                                    <div x-data="{ modalOpen: false }">
                                        <!-- Trigger Edit Client button -->
                                        <button @click="modalOpen = true" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
                                            Edit Client
                                        </button>

                                        <!-- Modal structure -->
                                        <div x-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50">
                                            <!-- Background overlay -->
                                            <div class="bg-gray-800 bg-opacity-75 absolute inset-0" @click="modalOpen = false"></div>

                                            <!-- Modal box -->
                                            <div class="bg-white p-6 rounded shadow-md z-10 max-w-md mx-auto">
                                                <h2 class="text-xl font-bold mb-4">Edit Client</h2>

                                                <!-- Client select dropdown -->
                                                <select id="clientSelect" class="w-full" name="client_id">
                                                    <option value="">Select Client</option>
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client->id }}" {{ $client->id == $project->client_id ? 'selected' : '' }}>
                                                            {{ $client->name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <!-- Save and cancel buttons -->
                                                <div class="mt-6 flex justify-end space-x-4">
                                                    <button @click="modalOpen = false" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">
                                                        Cancel
                                                    </button>
                                                    <button @click="updateClient({{ $project->id }}, document.getElementById('clientSelect').value); modalOpen = false" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-data="calendarComponent()">
                                        <!-- Add to Calendar Button -->
                                        <button @click="openModal({{ $project->id }}, '{{ $project->title }}', '{{ $project->start_date ? $project->start_date->format('Y-m-d\TH:i') : 'N/A' }}', '{{ $project->end_date ? $project->end_date->format('Y-m-d\TH:i') : 'N/A' }}')"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
                                            Add to Calendar
                                        </button>
                                    
                                        <!-- Modal for adding to calendar -->
                                        <div x-show="showAddToCalendarModal" class="fixed inset-0 flex items-center justify-center z-50">
                                            <div class="bg-gray-800 bg-opacity-75 absolute inset-0" @click="closeModal"></div>
                                    
                                            <div class="bg-white p-6 rounded shadow-md z-10 max-w-md mx-auto">
                                                <h2 class="text-xl font-bold mb-4">Confirm Add to Calendar</h2>
                                                <p class="mb-6">Do you want to add this project to the calendar?</p>
                                    
                                                <div class="flex justify-end space-x-4">
                                                    <button @click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">Cancel</button>
                                                    <button @click="confirmAddToCalendar()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Confirm</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Toggle Completion -->
                                    <form method="POST" action="{{ route('projects.toggleCompletion', $project) }}" class="block text-start">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
                                            {{ $project->status == 'completed' ? 'Mark as Ongoing' : 'Mark as Completed' }}
                                        </button>
                                    </form>
                                    <!-- Check if the project is completed -->
                                    @if($project->status == 'completed')
                                        <!-- Show the Select Tasks for Report Button -->
                                        <a href="{{ route('projects.selectTasks', $project->id) }}" class="btn btn-primary">
                                            Select Tasks for Report
                                        </a>
                                    @endif
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

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

    function checkDirection(dropdown) {
        // Get the bounding rectangle of the dropdown
        const rect = dropdown.getBoundingClientRect();

        // Check if there's enough space at the bottom of the viewport
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;

        // Return 'up' if there's more space above, 'down' otherwise
        return spaceBelow < 200 && spaceAbove > spaceBelow ? 'up' : 'down';
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
