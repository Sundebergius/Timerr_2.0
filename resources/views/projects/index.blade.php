<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6 text-blue-500">Projects</h1>

        <div class="mb-4">
            <a href="{{ route('projects.create') }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-500 ease-in-out">
                Create Project
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Task Summary
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Quick Stats
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Client Information
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Invoice Status
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <div class="flex items-center">
                                    <div class="ml-3">
                                        <p class="text-gray-900 whitespace-no-wrap">
                                            {{ $project->title }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $project->status == 'ongoing' ? 'bg-green-100 text-green-800' : 
                                    ($project->status == 'nearing completion' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($project->status == 'overdue' ? 'bg-red-100 text-red-800' : 
                                    ($project->status == 'completed' ? 'bg-gray-100 text-gray-800' : 
                                    'bg-blue-100 text-blue-800'))) }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <ul>
                                    @foreach ($project->tasks as $task)
                                        <li>{{ $task->title }} ({{ $task->task_type }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                @php
                                    $totalEarnings = 0;
                                @endphp
                                @if ($project->tasks->where('taskable_type', 'App\Models\TaskProject')->isNotEmpty())
                                    @php
                                        $projectEarnings = $project->tasks->where('taskable_type', 'App\Models\TaskProject')->sum(function ($task) {
                                            return $task->taskable->price;
                                        });
                                        $totalEarnings += $projectEarnings;
                                    @endphp
                                    Project Earnings: {{ $projectEarnings }} DKK<br>
                                @endif
                                @if ($project->tasks->where('taskable_type', 'App\Models\TaskHourly')->isNotEmpty())
                                    @php
                                        $secondsWorked = 0;
                                        $hourlyEarnings = 0;
                                        foreach ($project->tasks->where('taskable_type', 'App\Models\TaskHourly') as $task) {
                                            foreach ($task->taskable->registrationHourly as $registration) {
                                                $secondsWorked += $registration->seconds_worked;
                                                $hourlyEarnings += $registration->seconds_worked * ($registration->hourly_rate / 3600);
                                            }
                                        }
                                        $hoursWorked = floor($secondsWorked / 3600);
                                        $minutesWorked = floor(($secondsWorked / 60) % 60);
                                        $timeWorked = sprintf('%02d:%02d', $hoursWorked, $minutesWorked);
                                        $totalEarnings += $hourlyEarnings;
                                        @endphp
                                    Time Worked: {{ $timeWorked }}<br>
                                    Hourly Earnings: {{ $hourlyEarnings }} DKK<br>
                                @endif
                                @if ($project->tasks->where('taskable_type', 'App\Models\TaskProduct')->isNotEmpty())
                                    @php
                                        $productEarnings = $project->tasks->where('taskable_type', 'App\Models\TaskProduct')->sum('taskable.product_price');
                                        $totalEarnings += $productEarnings;
                                    @endphp
                                    Product Earnings: {{ $productEarnings }} DKK<br>
                                @endif
                                @if ($project->tasks->where('taskable_type', 'App\Models\TaskTravel')->isNotEmpty())
                                    @php
                                        $travelCosts = $project->tasks->where('taskable_type', 'App\Models\TaskTravel')->sum('taskable.travel_cost');
                                        $totalEarnings += $travelCosts;
                                    @endphp
                                    Travel Costs: {{ $travelCosts }} DKK<br>
                                @endif
                                Total Earnings: {{ $totalEarnings }} DKK
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <select name="client_id" class="client-select" style="width: 100%">
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <select name="invoice_status" onchange="updateInvoiceStatus({{ $project->id }}, this.value)">
                                    <option value="generated" {{ $project->invoice_status == 'generated' ? 'selected' : '' }}>Generated</option>
                                    <option value="sent" {{ $project->invoice_status == 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="paid" {{ $project->invoice_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="{{ route('projects.show', $project) }}" class="text-blue-500 hover:text-blue-700 transition duration-500 ease-in-out">View</a>
                                <a href="{{ route('projects.edit', $project) }}" class="ml-4 text-yellow-500 hover:text-yellow-700 transition duration-500 ease-in-out">Edit</a>
                                @if ($project->status == 'completed')
                                    <a href="{{ route('projects.invoice', $project) }}">View Invoice</a>
                                @endif
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-4 text-red-500 hover:text-red-700 transition duration-500 ease-in-out">Delete</button>
                                </form>
                                <form method="POST" action="{{ route('projects.complete', $project) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Mark as completed</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

<!-- Load jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Load Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- Load Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.client-select').select2();
    });

    function updateInvoiceStatus(projectId, status) {
        fetch('/projects/' + projectId + '/update-invoice-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        }).then(response => {
            if (!response.ok) {
                alert('Failed to update invoice status');
            }
        });
    }
</script>
```