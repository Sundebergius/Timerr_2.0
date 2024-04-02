
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">{{ $project->title }}</h1>

        @foreach($project->tasks as $task)
        <div class="mb-4 p-4 bg-white rounded shadow">
            <h2 class="text-xl font-bold mb-2">{{ $task->title }}</h2>
            <p class="mb-2"><strong>Customer:</strong> {{ $task->customer ?? 'Nobody' }}</p>
            <p class="mb-2"><strong>Type:</strong> {{ $task->task_type }}</p>

            @if($task->task_type == 'project_based')
                <p class="mb-2"><strong>Total Price:</strong> {{ $task->taskable->price }}</p>
                <p class="mb-2"><strong>Date:</strong> {{ date('d-m-Y', strtotime($task->taskable->start_date)) }}</p>
                <p class="mb-2"><strong>Location:</strong> {{ $task->taskable->location }}</p>
            @elseif($task->task_type == 'hourly')
                <p class="mb-2"><strong>Hourly Wage:</strong> {{ $task->taskable->rate_per_hour }}</p>
                <p class="mb-2"><strong>Number of Registrations:</strong> {{ optional($task->taskable->registration_hourly)->count() ?? 0 }}</p>
                @php
                    $totalMinutes = optional($task->taskable->registration_hourly)->sum('minutes_worked') ?? 0;
                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;
                @endphp
                <p class="mb-2"><strong>Total Time:</strong> {{ sprintf("%02d:%02d", $hours, $minutes) }} hours</p>
            @endif

            @if($task->type == 'sale_of_products')
            <p class="mb-2"><strong>Product Sold:</strong> {{ $task->product_sold }}</p>
            <p class="mb-2"><strong>Total Price:</strong> {{ $task->total_price }}</p>
            @endif

            <a href="{{ route('projects.tasks.edit', ['project' => $project->id, 'task' => $task->id]) }}" class="text-blue-500 hover:text-blue-700">
                <i class="fas fa-cog"></i> Edit/View
            </a>

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
