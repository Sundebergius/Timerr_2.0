
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">{{ $project->title }}</h1>

        @foreach($project->tasks as $task)
        <div class="mb-4 p-4 bg-white rounded shadow">
            <h2 class="text-xl font-bold mb-2">{{ $task->title }}</h2>
            <p class="mb-2"><strong>Client:</strong> {{ $task->client ?? 'Nobody' }}</p>
            <p class="mb-2"><strong>Type:</strong> {{ $task->task_type }}</p>

            @if($task->task_type == 'project_based')
                <p class="mb-2"><strong>Total Price:</strong> {{ $task->taskable->price }}</p>
                <p class="mb-2"><strong>Date:</strong> {{ date('d-m-Y', strtotime($task->taskable->start_date)) }}</p>
                <p class="mb-2"><strong>Location:</strong> {{ $task->taskable->project_location }}</p>
            @endif

            @if($task->task_type == 'hourly')
                <p class="mb-2"><strong>Hourly Wage:</strong> {{ $task->taskable->rate_per_hour }}</p>
                <p class="mb-2"><strong>Number of Registrations:</strong> {{ $task->taskable->registrationHourly->count() }}</p>
                @php
                    $totalSeconds = $task->taskable->registrationHourly->sum('seconds_worked');
                    $days = floor($totalSeconds / (3600*24));
                    $hours = floor(($totalSeconds / 3600) % 24);
                    $minutes = floor(($totalSeconds / 60) % 60);
                @endphp
                <p class="mb-2"><strong>Total Time:</strong> {{ sprintf("%d days, %02d hours, %02d minutes", $days, $hours, $minutes) }}</p>
            @endif

            {{-- @if($task->task_type == 'distance')
                <p class="mb-2"><strong>Distance Driven:</strong> {{ $task->taskable->registrationDistance->distance }}</p>
            @endif --}}
            

            @if($task->type == 'sale_of_products')
            <p class="mb-2"><strong>Product Sold:</strong> {{ $task->product_sold }}</p>
            <p class="mb-2"><strong>Total Price:</strong> {{ $task->total_price }}</p>
            @endif

            @if($task->task_type == 'distance')
                <p class="mb-2"><strong>Number of Registrations:</strong> {{ $task->taskable->registrationDistances->count() }}</p>
                <p class="mb-2"><strong>Total Distance Driven:</strong> {{ $task->taskable->registrationDistances->sum('distance') }} km</p>
            @endif

            @if($task->task_type == 'other')
                <p class="mb-2"><strong>Title:</strong> {{ $task->taskable->title }}</p>
                <p class="mb-2"><strong>Description:</strong> {{ \Illuminate\Support\Str::limit($task->taskable->description, 100, $end='...') }}</p>
                <a href="#" class="text-blue-500 hover:text-blue-700" onclick="alert('{{ $task->taskable->description }}')">Read More</a>
            @endif

            <a href="{{ route('projects.tasks.registrations.create', ['project' => $project->id, 'task' => $task->id]) }}" class="text-blue-500 hover:text-blue-700">
                <i class="fas fa-plus"></i> Create Registration
            </a>

            {{-- @foreach($task->registrations as $registration)
            <div class="mt-4 p-4 bg-gray-100 rounded shadow">
                <p><strong>Time Worked:</strong> {{ $registration->time_worked }}</p>
                <p><strong>Date Worked:</strong> {{ $registration->date_worked }}</p>
                <p><strong>Comment:</strong> {{ $registration->comment }}</p>
            </div>
            @endforeach --}}
        </div>
        @endforeach
    </div>
