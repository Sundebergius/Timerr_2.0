<div class="container mx-auto px-4 font-sans">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h1 class="text-3xl font-bold mb-4">Invoice for Project {{ $project->id }}</h1>
            <p class="text-xl">Client Name: {{ $project->client->name }}</p>
            <p class="text-xl">Client Email: {{ $project->client->email }}</p>
        </div>
        <div class="text-right">
            <p class="text-xl">Invoice Date: {{ now()->toFormattedDateString() }}</p>
            <p class="text-xl">Invoice ID: #{{ $project->id }}</p>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Project-Based Tasks</h2>
        @php $total = 0 @endphp
        @foreach ($projectTasks as $task)
            <div class="border p-4 mb-4">
                <p class="text-xl">Title: {{ $task->taskable->title }}</p>
                <p class="text-xl">Price: {{ $task->taskable->price }}</p>
            </div>
            @php $total += $task->taskable->price @endphp
        @endforeach
    </div>

    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Hourly Tasks</h2>
        @foreach ($hourlyTasks as $task)
            <div class="border p-4 mb-4">
                <p class="text-xl">Title: {{ $task->taskable->title }}</p>
                <p class="text-xl">Rate per Hour: {{ $task->taskable->rate_per_hour }}</p>
                <p class="text-xl">Total Hours Worked: {{ $task->taskable->registrations->sum('seconds_worked') / 3600 }}</p>
            </div>
            @php $total += $hours * $task->taskable->rate_per_hour @endphp
        @endforeach
    </div>

    <div class="text-right mt-8">
        <p class="text-2xl font-bold">Total: {{ $total }}</p>
    </div>
</div>