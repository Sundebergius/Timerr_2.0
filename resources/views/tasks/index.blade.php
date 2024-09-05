<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">{{ $project->title }}</h1>

    @foreach($project->tasks as $task)
    <details class="mb-6 p-6 bg-white rounded-lg shadow-lg border border-gray-300">
        <summary class="cursor-pointer flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-semibold mb-1 text-gray-800">{{ $task->title }}</h2>
                <p class="text-gray-600"><strong>Client:</strong> {{ $task->client->name ?? 'Nobody' }}</p>
                <p class="text-gray-600"><strong>Type:</strong> {{ $task->task_type }}</p>

                {{-- Task-specific summary data --}}
                @if($task->task_type == 'project_based')
                <p class="text-gray-700"><strong>Total Price:</strong> {{ $task->taskable->price }}</p>
                <p class="text-gray-700"><strong>Date:</strong> {{ date('d-m-Y', strtotime($task->taskable->start_date)) }}</p>
                <p class="text-gray-700"><strong>Location:</strong> {{ $task->taskable->project_location }}</p>
                @elseif($task->task_type == 'hourly')
                @php
                    $totalMinutes = $task->taskable->registrationHourly->sum('minutes_worked');
                    $days = floor($totalMinutes / (60*24));
                    $hours = floor(($totalMinutes / 60) % 24);
                    $minutes = $totalMinutes % 60;
                @endphp
                <p class="text-gray-700"><strong>Hourly Wage:</strong> {{ $task->taskable->rate_per_hour }}</p>
                <p class="text-gray-700"><strong>Number of Registrations:</strong> {{ $task->taskable->registrationHourly->count() }}</p>
                <p class="text-gray-700"><strong>Total Time:</strong> {{ sprintf("%d days, %02d hours, %02d minutes", $days, $hours, $minutes) }}</p>
                @elseif($task->task_type == 'product')
                <div class="space-y-2">
                    @foreach($task->taskProduct as $taskProduct)
                        <p class="text-gray-700"><strong>Product Sold:</strong> {{ $taskProduct->product->title }}</p>
                    @endforeach
                </div>
                @elseif($task->task_type == 'distance')
                <p class="text-gray-700"><strong>Number of Registrations:</strong> {{ $task->taskable->registrationDistances->count() }}</p>
                <p class="text-gray-700"><strong>Total Distance Driven:</strong> {{ $task->taskable->registrationDistances->sum('distance') }} km</p>
                @elseif($task->task_type == 'other')
                <div class="space-y-2">
                    @if(!empty($task->taskable->description))
                        <p class="text-gray-700 font-bold">Description:</p>
                        <p class="text-gray-600">{{ \Illuminate\Support\Str::limit($task->taskable->description, 100, $end='...') }}</p>
                        @if(strlen($task->taskable->description) > 100)
                            <div id="fullDescription{{ $task->id }}" class="hidden">
                                <p class="text-gray-600">{{ $task->taskable->description }}</p>
                            </div>
                            <a href="#" class="text-blue-500 hover:text-blue-700" onclick="document.getElementById('fullDescription{{ $task->id }}').classList.toggle('hidden'); return false;">Read More</a>
                        @endif
                    @endif
                </div>
                @endif
            </div>
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </summary>

        <div class="mt-4">
            {{-- Task-specific expandable data --}}
            @if($task->task_type == 'project_based')
            <div class="space-y-2">
                <p class="text-gray-700"><strong>End Date:</strong> {{ $task->taskable->end_date ? date('d-m-Y', strtotime($task->taskable->end_date)) : 'N/A' }}</p>
                <p class="text-gray-700"><strong>Currency:</strong> {{ $task->taskable->currency }}</p>
            </div>
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
            @elseif($task->task_type == 'other')
            <div class="space-y-2">
                @if($task->customFields->count() > 0)
                    <p class="text-gray-700 font-bold">Custom Fields:</p>
                    <ul class="list-disc pl-5 text-gray-600">
                        @foreach($task->customFields as $field)
                            <li>{{ $field->field }}</li>
                        @endforeach
                    </ul>
                @endif

                @if($task->checklistSections->count() > 0)
                    <p class="text-gray-700 font-bold">Checklist Sections:</p>
                    <div class="pl-5 text-gray-600">
                        @foreach($task->checklistSections as $section)
                            <p class="font-bold text-lg mb-1 text-gray-800">{{ $section->title }}</p>
                            @if($section->checklistItems->count() > 0)
                                <ul class="list-disc pl-5">
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

            <div class="mt-6 flex space-x-3">
                {{-- <a href="{{ route('projects.tasks.show', ['project' => $project, 'task' => $task]) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">View</a> --}}
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

    <style>
        details > summary {
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        details > summary::-webkit-details-marker {
            display: none;
        }
    </style>
</div>
