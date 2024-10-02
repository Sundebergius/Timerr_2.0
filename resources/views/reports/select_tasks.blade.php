<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Tasks for Project Report: ') . $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">
                        Select Tasks for Project Report: {{ $project->title }}
                    </h1>

                    <form method="POST" action="{{ route('projects.generateReport', $project->id) }}">
                        @csrf

                        <!-- Project-Based Tasks -->
                        @if($projectTasks->isNotEmpty())
                            <h3 class="text-lg font-semibold text-blue-600 mb-4">Project-Based Tasks</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                @foreach($projectTasks as $task)
                                    <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                        <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                                        <span class="text-gray-700">{{ $task->title }} ({{ number_format($task->taskable->price, 2) }} DKK)</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <!-- Hourly Tasks -->
                        @if($hourlyTasks->isNotEmpty())
                            <h3 class="text-lg font-semibold text-blue-600 mb-4">Hourly Tasks</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                @foreach($hourlyTasks as $task)
                                    @php
                                        $totalMinutesWorked = $task->taskable->registrationHourly->sum('minutes_worked');
                                        $hoursWorked = floor($totalMinutesWorked / 60);
                                        $minutesWorked = $totalMinutesWorked % 60;
                                        $earningsPerMinute = $task->taskable->rate_per_hour / 60;
                                        $hourlyEarnings = $totalMinutesWorked * $earningsPerMinute;
                                    @endphp
                                    <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                        <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                                        <span class="text-gray-700">
                                            {{ $task->title }} (Worked: {{ $hoursWorked }}h {{ $minutesWorked }}m, Earnings: {{ number_format($hourlyEarnings, 2) }} DKK)
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <!-- Distance-Based Tasks -->
                        @if($distanceTasks->isNotEmpty())
                            <h3 class="text-lg font-semibold text-blue-600 mb-4">Distance-Based Tasks</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                @foreach($distanceTasks as $task)
                                    @php
                                        $totalDistance = $task->taskable->registrationDistances->sum('distance');
                                        $distanceCost = $totalDistance * $task->taskable->price_per_km;
                                    @endphp
                                    <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                        <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                                        <span class="text-gray-700">
                                            {{ $task->title }} (Distance: {{ $totalDistance }} km, Cost: {{ number_format($distanceCost, 2) }} DKK)
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <!-- Product-Based Tasks -->
                        @if($productTasks->isNotEmpty())
                            <h3 class="text-lg font-semibold text-blue-600 mb-4">Product-Based Tasks</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                @foreach($productTasks as $task)
                                    @foreach($task->taskProduct as $taskProduct)
                                        <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                            <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                                            <span class="text-gray-700">{{ $task->title }} - {{ $taskProduct->product->name }} (Sold: {{ $taskProduct->quantity }}, Price: {{ number_format($taskProduct->product->price, 2) }} DKK)</span>
                                        </label>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif

                        <!-- Other Tasks -->
                        @if($otherTasks->isNotEmpty())
                            <h3 class="text-lg font-semibold text-blue-600 mb-4">Other Tasks</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                @foreach($otherTasks as $task)
                                    <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                        <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                                        <span class="text-gray-700">{{ $task->title }} (Custom Task)</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow hover:bg-blue-700 transition">
                                Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
