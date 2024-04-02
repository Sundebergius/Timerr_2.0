<h1>Invoice for Project {{ $project->id }}</h1>

<h2>Project-Based Tasks</h2>
@php $total = 0 @endphp
@foreach ($projectTasks as $task)
    <p>
        Title: {{ $task->taskable->title }}<br>
        Price: {{ $task->taskable->price }}
    </p>
    @php $total += $task->taskable->price @endphp
@endforeach

<h2>Hourly Tasks</h2>
@foreach ($hourlyTasks as $task)
    <p>
        Title: {{ $task->taskable->title }}<br>
        Rate per Hour: {{ $task->taskable->rate_per_hour }}<br>
        Total Hours Worked: {{ $task->taskable->registrations->sum('seconds_worked') / 3600 }}
    </p>
    @php $total += $hours * $task->taskable->rate_per_hour @endphp
@endforeach