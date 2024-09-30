<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Tasks for Project Report: ') . $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1>Select Tasks for Project Report: {{ $project->title }}</h1>

                    <form method="POST" action="{{ route('projects.generateReport', $project->id) }}">
                        @csrf

                        <!-- Project-Based Tasks -->
                        @if($projectTasks->isNotEmpty())
                            <h3>Project-Based Tasks</h3>
                            @foreach($projectTasks as $task)
                                <label>
                                    <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked>
                                    {{ $task->title }} ({{ number_format($task->taskable->price, 2) }} DKK)
                                </label><br>
                            @endforeach
                        @endif

                        <!-- Hourly Tasks -->
                        @if($hourlyTasks->isNotEmpty())
                            <h3>Hourly Tasks</h3>
                            @foreach($hourlyTasks as $task)
                                <label>
                                    <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked>
                                    {{ $task->title }} (Rate: {{ number_format($task->taskable->rate_per_hour, 2) }} DKK)
                                </label><br>
                            @endforeach
                        @endif

                        <!-- Add checkboxes for Products and Distance Tasks if needed -->

                        <button type="submit" class="btn btn-primary mt-4">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
