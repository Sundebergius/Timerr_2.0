<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">{{ $project->title }}</h1>

    @foreach($project->tasks as $task)
    <details class="mb-6 p-6 bg-white rounded-lg shadow-lg border border-gray-300">
        <summary class="cursor-pointer flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-semibold mb-1 text-gray-800">{{ $task->title }}</h2>

                {{-- Enhanced Task Type Badge --}}
                <div class="flex items-center space-x-2 mb-4"> <!-- Added margin-bottom (mb-4) for extra space -->
                    <p class="text-gray-600"><strong>Type:</strong></p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $task->task_type == 'project_based' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $task->task_type == 'hourly' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $task->task_type == 'product' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $task->task_type == 'distance' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $task->task_type == 'other' ? 'bg-gray-100 text-gray-800' : '' }}">
                        {{ ucfirst($task->task_type) }}
                    </span>
                </div>
                
                {{-- Task-specific summary data --}}
                @if($task->task_type == 'project_based')
                    <div class="space-y-4 bg-gray-50 p-4 rounded-lg shadow-md">
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>Total Price:</strong></p>
                            <p class="text-gray-800">{{ $task->taskable->price ? number_format($task->taskable->price, 2) . ' ' . $task->taskable->currency : 'N/A' }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>Start Date:</strong></p>
                            <p class="text-gray-800">{{ $task->taskable->start_date ? date('d-m-Y', strtotime($task->taskable->start_date)) : 'Not Set' }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>End Date:</strong></p>
                            <p class="text-gray-800">{{ $task->taskable->end_date ? date('d-m-Y', strtotime($task->taskable->end_date)) : 'Ongoing' }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>Location:</strong></p>
                            <p class="text-gray-800 ml-6">{{ $task->taskable->project_location ?? 'No location provided' }}</p>
                        </div>
                    </div>

                @elseif($task->task_type == 'hourly')
                @php
                    $totalMinutes = $task->taskable->registrationHourly->sum('minutes_worked');
                    $days = floor($totalMinutes / (60 * 24)); 
                    $hours = floor(($totalMinutes / 60) % 24); 
                    $minutes = $totalMinutes % 60;
                    $hourlyRate = $task->taskable->rate_per_hour;
                    $totalWages = ($totalMinutes / 60) * $hourlyRate;
                @endphp

                <div class="bg-gray-50 p-4 rounded-lg shadow-md space-y-4">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Hourly Task Summary</h3>
                    <div class="space-y-4">
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>Hourly Wage:</strong></p>
                            <p class="text-gray-800">{{ number_format($task->taskable->rate_per_hour, 2) }} {{ $task->taskable->currency ?? 'DKK' }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>Total Time Worked:</strong></p>
                            <p class="text-gray-800 ml-6">{{ sprintf("%d days, %02d hours, %02d minutes", $days, $hours, $minutes) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>Total Wages:</strong></p>
                            <p class="text-gray-800">{{ number_format($totalWages, 2) }} {{ $task->taskable->currency ?? 'DKK' }}</p>
                        </div>
                    </div>
                </div>

                @elseif($task->task_type == 'product')
                <div class="bg-gray-50 p-4 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Product/Service Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($task->taskProduct as $taskProduct)
                            @php
                                $product = $taskProduct->product;
                                $price = $product->price;
                                $quantity = $taskProduct->quantity;
                                $totalPrice = $price * $quantity;
                                $attributes = $taskProduct->attributes ? (is_string($taskProduct->attributes) ? json_decode($taskProduct->attributes, true) : $taskProduct->attributes) : [];
                            @endphp
                            <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $product->title }}</h3>
                                <p class="text-gray-600"><strong>Type:</strong> {{ ucfirst($product->type) }}</p>
                                <p class="text-gray-700"><strong>Quantity:</strong> {{ $quantity }}</p>
                                @if($product->type == 'product')
                                    <p class="text-gray-700"><strong>Price per Unit:</strong> {{ number_format($price, 2) }} {{ $product->currency ?? 'DKK' }}</p>
                                    <p class="text-gray-700"><strong>Total Price:</strong> {{ number_format($totalPrice, 2) }} {{ $product->currency ?? 'DKK' }}</p>
                                @elseif($product->type == 'service' && count($attributes))
                                    <div class="mt-2">
                                        <p class="text-gray-700"><strong>Attributes:</strong></p>
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach($attributes as $attribute)
                                                <li>{{ $attribute['attribute'] ?? 'N/A' }}: {{ $attribute['quantity'] ?? 'N/A' }} (Price: {{ $attribute['price'] ?? 'N/A' }} {{ $product->currency ?? 'DKK' }})</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @elseif($task->task_type == 'distance')
                <div class="bg-gray-50 p-4 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Distance Task Summary</h3>
                    @php
                        $totalDistanceKm = $task->taskable->registrationDistances->sum('distance');
                        $totalDistanceMiles = $totalDistanceKm * 0.621371; 
                    @endphp
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-white p-4 rounded-md shadow-sm flex justify-between items-center">
                            <p class="text-gray-600"><strong>Total Distance Driven:</strong></p>
                            <p class="text-gray-800">{{ number_format($totalDistanceKm, 2) }} km ({{ number_format($totalDistanceMiles, 2) }} miles)</p>
                        </div>
                    </div>
                </div>

                @elseif($task->task_type == 'other')
                <div class="bg-gray-50 p-4 rounded-lg shadow-md space-y-4">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Other Task Summary</h3>
                    <div class="space-y-2">
                        <p class="text-gray-600"><strong>Description:</strong></p>
                        <p class="text-gray-800">{{ \Illuminate\Support\Str::limit($task->taskable->description, 100, '...') }}</p>
                        @if(strlen($task->taskable->description) > 100)
                            <a href="#" class="text-blue-500 hover:text-blue-700" onclick="document.getElementById('fullDescription{{ $task->id }}').classList.toggle('hidden'); this.innerText = this.innerText === 'Read More' ? 'Read Less' : 'Read More'; return false;">Read More</a>
                            <div id="fullDescription{{ $task->id }}" class="hidden">
                                <p class="text-gray-800">{{ $task->taskable->description }}</p>
                            </div>
                        @endif
                    </div>
                    @if($task->customFields->count() > 0)
                        <p class="text-gray-600 font-bold">Custom Fields:</p>
                        <ul class="list-disc pl-5 space-y-1 text-gray-800">
                            @foreach($task->customFields as $field)
                                <li>{{ $field->field }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($task->checklistSections->count() > 0)
                        <p class="text-gray-600 font-bold">Checklist Sections:</p>
                        <div class="pl-5 space-y-4">
                            @foreach($task->checklistSections as $section)
                                <p class="font-bold text-lg text-gray-800">{{ $section->title }}</p>
                                @if($section->checklistItems->count() > 0)
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($section->checklistItems as $item)
                                            <li>{{ $item->item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </summary>

        {{-- Expanded section for detailed data --}}
        <div class="mt-4">
            @if($task->task_type == 'project_based')
                {{-- Additional data specific to project-based tasks can go here --}}
            @elseif($task->task_type == 'hourly')
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-gray-800">Registrations</h3>
                    @foreach ($task->taskable->registrationHourly as $registration)
                        @php
                            $hours = floor($registration->minutes_worked / 60);
                            $minutes = $registration->minutes_worked % 60;
                        @endphp
                        <div class="p-4 bg-gray-100 rounded-lg shadow-inner">
                            <strong class="block text-gray-700">Registration #{{ $loop->iteration }}</strong>
                            <p class="text-gray-600">Hours: {{ $hours }}</p>
                            <p class="text-gray-600">Minutes: {{ $minutes }}</p>
                        </div>
                    @endforeach
                </div>
            @elseif($task->task_type == 'distance')
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-gray-800">Registrations</h3>
                    @foreach ($task->taskable->registrationDistances as $registration)
                        <div class="p-4 bg-gray-100 rounded-lg shadow-inner">
                            <strong class="block text-gray-700">Registration #{{ $loop->iteration }}</strong>
                            <p class="text-gray-600">Distance: {{ $registration->distance }} km</p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Action buttons --}}
            <div class="mt-6 flex space-x-3">
                <a href="{{ route('projects.tasks.edit', ['project' => $project, 'task' => $task]) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Edit</a>
                <form action="{{ route('projects.tasks.destroy', ['project' => $project, 'task' => $task]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Delete</button>
                </form>
            </div>
        </div>
    </details>
    @endforeach
</div>


<script>
    function addToCalendar(taskId, title, startDate, endDate) {
        // Implement the function to add the task to the calendar
        alert(`Add task ${title} to calendar from ${startDate} to ${endDate}`);
    }
</script>
