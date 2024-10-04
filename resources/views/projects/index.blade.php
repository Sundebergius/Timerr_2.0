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

        <!-- Header Section for Projects -->
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

            <!-- CTA for Upgrade when Project Limit is Reached -->
            @if ($projectCount >= $projectLimit)
                <div class="mt-6 bg-yellow-100 p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-yellow-800">Need to manage more projects?</h3>
                    <p class="text-yellow-600">Upgrade to the Freelancer plan to manage up to 10 projects and access more features.</p>
                    <a href="{{ route('stripe.checkout', ['plan' => 'freelancer']) }}" class="mt-4 inline-block bg-yellow-500 text-white py-2 px-6 rounded-lg shadow hover:bg-yellow-600">Upgrade Now</a>
                </div>
            @endif
        </div>


        <div class="container mx-auto px-4">
            {{-- <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">Project Dashboard</h1> --}}
        
            <div class="flex flex-wrap justify-center gap-8">
                @foreach ($projects as $project)
                <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-w-xs flex flex-col relative" x-data="{ openProjectDetails: true }">
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

                     <!-- Expand/Collapse Button -->
                    <button @click="openProjectDetails = !openProjectDetails" class="bg-blue-500 text-white px-3 py-2 rounded-lg hover:bg-blue-600 transition-all duration-300 w-full mb-4">
                        <span x-show="!openProjectDetails">View Details</span>
                        <span x-show="openProjectDetails">Hide Details</span>
                    </button>

                    <!-- Expandable Section (Project Details) -->
                    <div x-show="openProjectDetails" x-collapse class="transition-all duration-300">
        
                    <!-- Task Summary -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Task Summary</h3>
                    
                        <!-- Grouped Tasks by Type -->
                        <div class="space-y-8">
                    
                            <!-- Project-Based Tasks -->
                            @if($project->tasks->where('task_type', 'project_based')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-blue-500 mb-2">Project-Based Tasks</h4>
                                @foreach ($project->tasks->where('task_type', 'project_based') as $index => $task)
                                <div class="mt-6 p-4 bg-blue-50 rounded-lg shadow-md flex flex-col items-center justify-center cursor-pointer" @click="openTaskDetails = openTaskDetails === {{ $task->id }} ? null : {{ $task->id }}">
                                    <i class="fas fa-project-diagram text-blue-500 text-2xl mb-2"></i>
                                    <div class="flex-grow text-center">
                                        <h4 class="text-lg font-bold text-gray-800">{{ $task->title }}</h4>
                                        <p class="text-sm text-gray-600">Project-Based</p>
                                        <span class="text-xs text-gray-500">{{ $loop->iteration }}</span>
                                    </div>
                                    <!-- Expandable Details -->
                                    <div x-show="openTaskDetails === {{ $task->id }}" class="mt-4 w-full" x-cloak>
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <!-- Conditionally display Start Date if it exists -->
                                            @if (!empty($task->taskable->start_date))
                                                <p class="text-sm text-gray-700"><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($task->taskable->start_date)->format('d-m-Y') }}</p>
                                            @else
                                                <p class="text-sm text-gray-700"><strong>Start Date:</strong> No start date provided</p>
                                            @endif

                                            <!-- Conditionally display End Date if it exists -->
                                            @if (!empty($task->taskable->end_date))
                                                <p class="text-sm text-gray-700"><strong>End Date:</strong> {{ \Carbon\Carbon::parse($task->taskable->end_date)->format('d-m-Y') }}</p>
                                            @else
                                                <p class="text-sm text-gray-700"><strong>End Date:</strong> No end date provided</p>
                                            @endif
                                            <p class="text-sm text-gray-700"><strong>Location:</strong> {{ $task->taskable->project_location ?? 'No location provided' }}</p>
                                            <p class="text-sm text-gray-700"><strong>Price:</strong> {{ number_format($task->taskable->price ?? 0, 2) }} DKK</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                    
                            <!-- Hourly Tasks -->
                            @if($project->tasks->where('task_type', 'hourly')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-green-500 mb-2">Hourly Tasks</h4>
                                @foreach ($project->tasks->where('task_type', 'hourly') as $index => $task)
                                    @php
                                        // Initialize variables
                                        $totalMinutesWorked = 0;
                                        $hourlyEarnings = 0;
                                        if ($task->taskable) {
                                            $earningsPerMinute = $task->taskable->rate_per_hour ? $task->taskable->rate_per_hour / 60 : 0;
                                            
                                            // Loop through registrations to calculate total minutes worked and earnings
                                            foreach ($task->taskable->registrationHourly as $registration) {
                                                $totalMinutesWorked += $registration->minutes_worked ?? 0;
                                                $hourlyEarnings += ($registration->minutes_worked ?? 0) * $earningsPerMinute;
                                            }
                                        }

                                        // Convert total minutes to days, hours, and minutes
                                        $totalDays = floor($totalMinutesWorked / (60 * 24));
                                        $totalHours = floor(($totalMinutesWorked / 60) % 24);
                                        $totalMinutes = $totalMinutesWorked % 60;
                                        $timeWorked = sprintf('%dd %dh %dm', $totalDays, $totalHours, $totalMinutes);
                                    @endphp
                                    <div class="mt-6 p-4 bg-green-50 rounded-lg shadow-md flex flex-col items-center justify-center cursor-pointer" @click="openTaskDetails = openTaskDetails === {{ $task->id }} ? null : {{ $task->id }}">
                                        <i class="fas fa-clock text-green-500 text-2xl mb-2"></i>
                                        <div class="flex-grow text-center">
                                            <h4 class="text-lg font-bold text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">Hourly</p>
                                            <span class="text-xs text-gray-500">{{ $loop->iteration }}</span>
                                        </div>
                                        
                                        <!-- Expandable Details -->
                                        <div x-show="openTaskDetails === {{ $task->id }}" class="mt-4 w-full" x-cloak>
                                            <div class="p-2 bg-green-100 rounded-lg">
                                                <p class="text-sm text-gray-700"><strong>Total Time Worked:</strong> {{ $timeWorked }}</p>
                                                <p class="text-sm text-gray-700"><strong>Rate Per Hour:</strong> {{ number_format($task->taskable->rate_per_hour ?? 0, 2) }} DKK</p>
                                                <p class="text-sm text-gray-700"><strong>Hourly Earnings:</strong> {{ number_format(ceil($hourlyEarnings), 2) }} DKK</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                    
                            <!-- Product-Based Tasks -->
                            @if($project->tasks->where('task_type', 'product')->isNotEmpty())
                            <div>
                                <h4 class="text-xl font-bold text-yellow-600 mb-4">Product-Based Tasks</h4>
                                @foreach ($project->tasks->where('task_type', 'product') as $index => $task)
                                <div class="mt-6 p-6 bg-yellow-50 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-200 ease-in-out flex flex-col items-center justify-center cursor-pointer" @click="openTaskDetails = openTaskDetails === {{ $task->id }} ? null : {{ $task->id }}">
                                    <i class="fas fa-box-open text-yellow-500 text-3xl mb-3"></i>
                                    <div class="flex-grow text-center">
                                        <h4 class="text-2xl font-extrabold text-gray-800">{{ $task->title }}</h4>
                                        <p class="text-sm text-gray-600">Product-Based Task</p>
                                        <span class="text-xs text-gray-500">{{ $loop->iteration }}</span>
                                    </div>

                                    <!-- Expandable Details -->
                                    <div x-show="openTaskDetails === {{ $task->id }}" class="mt-4 w-full transition-all duration-300 ease-in-out" x-cloak>
                                        @php
                                        // Fetch the task products or services and calculate totals
                                        $taskProducts = \App\Models\TaskProduct::where('task_id', $task->id)->with('product')->get();
                                        $taskTotal = 0; // Total earnings for this task
                                        @endphp

                                        @foreach ($taskProducts as $taskProduct)
                                            @if($taskProduct->product)
                                            @php
                                            $productPrice = $taskProduct->product->price ?? 0;
                                            $quantitySold = $taskProduct->quantity ?? 0;
                                            $productTotal = $productPrice * $quantitySold; // Total for this individual product/service

                                            // Check if it's a service and handle attributes
                                            $isService = $taskProduct->product->type === 'service';
                                            $serviceAttributes = json_decode($taskProduct->attributes, true) ?? [];
                                            @endphp

                                            <!-- Display for products -->
                                            @if(!$isService)
                                            <div class="p-4 bg-yellow-100 rounded-lg mb-3 border border-yellow-300">
                                                <h5 class="text-lg font-semibold text-gray-800 mb-2">Product Details</h5>
                                                <p class="text-sm text-gray-700"><strong>Product Name:</strong> {{ $taskProduct->product->title }}</p>
                                                <p class="text-sm text-gray-700"><strong>Price per unit:</strong> {{ number_format($productPrice, 2) }}</p>
                                                <p class="text-sm text-gray-700"><strong>Quantity Sold:</strong> {{ $quantitySold }}</p>
                                                <p class="text-sm text-gray-700"><strong>Total for this product:</strong> {{ number_format($productTotal, 2) }}</p>
                                            </div>
                                            @else
                                            <!-- Display for services -->
                                            <div class="p-4 bg-yellow-100 rounded-lg mb-3 border border-yellow-300">
                                                <h5 class="text-lg font-semibold text-gray-800 mb-2">Service Details</h5>
                                                <p class="text-sm text-gray-700"><strong>Service Name:</strong> {{ $taskProduct->product->title }}</p>
                                                <p class="text-sm text-gray-700"><strong>Standard Price:</strong> {{ number_format($productPrice, 2) }}</p>
                                                <p class="text-sm text-gray-700"><strong>Quantity Sold:</strong> {{ $quantitySold }}</p>

                                                <!-- Display attributes -->
                                                <div class="mt-3">
                                                    <h6 class="text-sm font-semibold text-gray-700">Service Attributes:</h6>
                                                    <ul class="list-disc list-inside text-sm">
                                                        @foreach ($serviceAttributes as $attribute)
                                                            @php
                                                                $attributeTotal = ($attribute['price'] ?? 0) * ($attribute['quantity'] ?? 0);
                                                                $productTotal += $attributeTotal; // Add attribute price to the total
                                                            @endphp
                                                            <li>
                                                                <strong>Attribute:</strong> {{ $attribute['attribute'] ?? 'N/A' }} - 
                                                                <strong>Price:</strong> {{ number_format($attribute['price'] ?? 0, 2) }}
                                                                <ul class="ml-6"> <!-- Add indentation with margin -->
                                                                    <li><strong>Quantity:</strong> {{ $attribute['quantity'] ?? 'N/A' }}</li>
                                                                    <li><strong>Total for this attribute:</strong> {{ number_format($attributeTotal, 2) }}</li>
                                                                </ul>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                <p class="text-sm text-gray-700 mt-2"><strong>Total for this service:</strong> {{ number_format($productTotal, 2) }}</p>
                                            </div>
                                            @endif

                                            @php
                                            // Add this product/service total to the task total
                                            $taskTotal += $productTotal;
                                            @endphp
                                            @endif
                                        @endforeach

                                        <!-- Show total earnings for the task -->
                                        <div class="p-3 bg-yellow-200 rounded-lg border border-yellow-400">
                                            <h5 class="text-md font-bold text-gray-800">Total Earnings for this task:</h5>
                                            <p class="text-lg text-gray-800">{{ number_format($taskTotal, 2) }} DKK</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                    
                            <!-- Distance-Based Tasks -->
                            @if($project->tasks->where('task_type', 'distance')->isNotEmpty())
                            <div>
                                <h4 class="text-md font-semibold text-purple-500 mb-2">Distance-Based Tasks</h4>
                                @foreach ($project->tasks->where('task_type', 'distance') as $index => $task)
                                    @php
                                        // Initialize variables
                                        $totalDistanceCovered = 0;
                                        $totalTravelCost = 0;

                                        if ($task->taskable) {
                                            // Loop through the registrationDistances relationship
                                            foreach ($task->taskable->registrationDistances as $registration) {
                                                $totalDistanceCovered += $registration->distance ?? 0;
                                            }
                                            // Calculate the travel cost based on distance covered
                                            $totalTravelCost = $task->taskable->price_per_km * $totalDistanceCovered;
                                        }
                                    @endphp
                                    <div class="mt-6 p-4 bg-purple-50 rounded-lg shadow-md flex flex-col items-center justify-center cursor-pointer" @click="openTaskDetails = openTaskDetails === {{ $task->id }} ? null : {{ $task->id }}">
                                        <i class="fas fa-road text-purple-500 text-2xl mb-2"></i>
                                        <div class="flex-grow text-center">
                                            <h4 class="text-lg font-bold text-gray-800">{{ $task->title }}</h4>
                                            <p class="text-sm text-gray-600">Distance-Based</p>
                                            <span class="text-xs text-gray-500">{{ $loop->iteration }}</span>
                                        </div>
                                        
                                        <!-- Expandable Details -->
                                        <div x-show="openTaskDetails === {{ $task->id }}" class="mt-4 w-full" x-cloak>
                                            <div class="p-2 bg-purple-100 rounded-lg">
                                                <p class="text-sm text-gray-700"><strong>Total Distance Covered:</strong> {{ number_format($totalDistanceCovered, 2) }} km</p>
                                                <p class="text-sm text-gray-700"><strong>Price per km:</strong> {{ number_format($task->taskable->price_per_km ?? 0, 2) }} DKK</p>
                                                <p class="text-sm text-gray-700"><strong>Total Travel Cost:</strong> {{ number_format($totalTravelCost, 2) }} DKK</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                    
                            @if($project->tasks->where('task_type', 'other')->isNotEmpty())
                                <div>
                                    <h4 class="text-md font-semibold text-gray-500 mb-2">Other Tasks</h4>
                                    @foreach ($project->tasks->where('task_type', 'other') as $index => $task)
                                        <div class="mt-6 p-4 bg-gray-100 rounded-lg shadow-md flex flex-col items-center justify-center cursor-pointer" @click="openTaskDetails = openTaskDetails === {{ $task->id }} ? null : {{ $task->id }}">
                                            <i class="fas fa-tasks text-gray-500 text-2xl mb-2"></i>
                                            <div class="flex-grow text-center">
                                                <h4 class="text-lg font-bold text-gray-800">{{ $task->title }}</h4>
                                                <p class="text-sm text-gray-600">Other Task</p>
                                                <span class="text-xs text-gray-500">{{ $loop->iteration }}</span>
                                            </div>

                                            <!-- Expandable Details -->
                                            <div x-show="openTaskDetails === {{ $task->id }}" class="mt-4 w-full" x-cloak>
                                                <!-- Description Section -->
                                                @if($task->taskable && $task->taskable->description)
                                                    <div class="space-y-2">
                                                        <p class="text-gray-600 font-semibold"><strong>Description:</strong></p>
                                                        <p class="text-gray-800">{{ \Illuminate\Support\Str::limit($task->taskable->description, 100, '...') }}</p>
                                                        @if(strlen($task->taskable->description) > 100)
                                                            <a href="#" class="text-blue-500 hover:text-blue-700" onclick="document.getElementById('fullDescription{{ $task->id }}').classList.toggle('hidden'); this.innerText = this.innerText === 'Read More' ? 'Read Less' : 'Read More'; return false;">
                                                                Read More
                                                            </a>
                                                            <div id="fullDescription{{ $task->id }}" class="hidden mt-2">
                                                                <p class="text-gray-800">{{ $task->taskable->description }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p class="text-gray-600"><strong>No description available</strong></p>
                                                @endif

                                                <!-- Custom Fields Section -->
                                                @if($task->customFields->count() > 0)
                                                    <div class="space-y-2">
                                                        <p class="text-gray-600 font-bold"><strong>Custom Fields:</strong></p>
                                                        <ul class="list-disc pl-6 space-y-1 text-gray-800">
                                                            @foreach($task->customFields as $field)
                                                                <li><span class="font-semibold">{{ $field->label ?? 'Field' }}:</span> {{ $field->field }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                <!-- Checklist Sections -->
                                                @if($task->checklistSections->count() > 0)
                                                    <div class="space-y-4">
                                                        <p class="text-gray-600 font-bold"><strong>Checklist Sections:</strong></p>
                                                        @foreach($task->checklistSections as $section)
                                                            <div class="pl-4 bg-gray-100 rounded-lg p-4 shadow-inner">
                                                                <h4 class="font-bold text-lg text-gray-800">{{ $section->title }}</h4>
                                                                @if($section->checklistItems->count() > 0)
                                                                    <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
                                                                        @foreach($section->checklistItems as $item)
                                                                            <li>{{ $item->item }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    <p class="text-gray-700">No checklist items available.</p>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
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

                        <!-- Stats Flex (Stacked Layout) -->
                        <div class="space-y-4"> <!-- Use space-y-4 to add vertical spacing between sections -->

                            <!-- Project Earnings -->
                            @if ($project->tasks->where('taskable_type', 'App\Models\TaskProject')->isNotEmpty())
                            @php
                                $projectEarnings = $project->tasks
                                    ->where('taskable_type', 'App\Models\TaskProject')
                                    ->sum(function ($task) {
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

                            <!-- Hourly Earnings and Time Worked (Grouped) -->
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
                            <!-- Group Hourly Earnings and Time Worked -->
                            <div class="p-4 bg-white rounded-lg shadow-md">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-green-500 text-xl mr-4"></i>
                                    <div class="flex-grow">
                                        <p class="text-sm text-gray-600">Time Worked</p>
                                        <h4 class="text-lg font-bold text-gray-800">{{ $timeWorked }}</h4>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center">
                                    <i class="fas fa-money-bill-wave text-green-500 text-xl mr-4"></i>
                                    <div class="flex-grow">
                                        <p class="text-sm text-gray-600">Hourly Earnings</p>
                                        <h4 class="text-lg font-bold text-gray-800">{{ number_format($hourlyEarnings, 2) }} DKK</h4>
                                    </div>
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
                                            if ($taskProduct->product->type === 'service') {
                                                $serviceAttributes = json_decode($taskProduct->attributes, true) ?? [];
                                                $productEarnings += ($taskProduct->product->price ?? 0) * ($taskProduct->quantity ?? 0);
                                                foreach ($serviceAttributes as $attribute) {
                                                    $productEarnings += ($attribute['price'] ?? 0) * ($attribute['quantity'] ?? 0);
                                                }
                                            } else {
                                                $productEarnings += ($taskProduct->product->price ?? 0) * ($taskProduct->quantity ?? 0);
                                            }
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
                        </div>
                    </div>
                </div>

                            <!-- Total Earnings - Full Width Section -->
                            <div class="mt-6 p-4 pb-8 bg-blue-50 rounded-lg shadow-md flex items-center justify-center mb-4"> <!-- Added mb-4 here -->
                                <i class="fas fa-piggy-bank text-blue-500 text-2xl mr-4"></i>
                                <div class="flex-grow text-center">
                                    <p class="text-sm text-blue-700">Total Earnings</p>
                                    <h4 class="text-xl font-extrabold text-blue-700">{{ number_format($totalEarnings, 2) }} DKK</h4>
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
                                        <a href="{{ route('projects.selectTasks', $project->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
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
