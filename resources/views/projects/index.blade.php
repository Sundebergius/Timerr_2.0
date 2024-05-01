<x-app-layout>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6 text-blue-500">Projects</h1>

        <div class="mb-4">
            <a href="{{ route('projects.create') }}"
                class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-500 ease-in-out">
                Create Project
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Name
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Task Summary
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Quick Stats
                        </th>
                        {{-- <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Client Information
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Invoice Status
                        </th> --}}
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
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
                                        <p class="text-gray-900 whitespace-no-wrap font-bold">
                                            {{ $project->title }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $project->status == 'ongoing'
                                        ? 'bg-green-100 text-green-800'
                                        : ($project->status == 'nearing completion'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : ($project->status == 'overdue'
                                                ? 'bg-red-100 text-red-800'
                                                : ($project->status == 'completed'
                                                    ? 'bg-gray-100 text-gray-800'
                                                    : 'bg-blue-100 text-blue-800'))) }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <ul>
                                    @foreach ($project->tasks as $index => $task)
                                        <li>{{ $index + 1 }}. <strong>{{ $task->title }}</strong>
                                            ({{ $task->task_type }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <div class="space-y-2">
                                    @php
                                        $totalEarnings = 0;
                                    @endphp
                                    @if ($project->tasks->where('taskable_type', 'App\Models\TaskProject')->isNotEmpty())
                                        @php
                                            $projectEarnings = $project->tasks
                                                ->where('taskable_type', 'App\Models\TaskProject')
                                                ->sum(function ($task) {
                                                    return $task->taskable->price;
                                                });
                                            $totalEarnings += $projectEarnings;
                                        @endphp
                                        <div class="flex justify-between">
                                            <span>Project Earnings:</span>
                                            <span>{{ $projectEarnings }} DKK</span>
                                        </div>
                                    @endif
                                    @if ($project->tasks->where('taskable_type', 'App\Models\TaskHourly')->isNotEmpty())
                                        @php
                                            $totalMinutesWorked = 0;
                                            $hourlyEarnings = 0;
                                            foreach (
                                                $project->tasks->where('taskable_type', 'App\Models\TaskHourly')
                                                as $task
                                            ) {
                                                foreach ($task->taskable->registrationHourly as $registration) {
                                                    $totalMinutesWorked += $registration->seconds_worked / 60;
                                                }
                                            }
                                            $earningsPerMinute = $registration->hourly_rate / 60;
                                            $hourlyEarnings = $totalMinutesWorked * $earningsPerMinute;

                                            // Round earnings up to the nearest whole number
                                            $hourlyEarnings = ceil($hourlyEarnings);

                                            $hoursWorkedFormatted = floor($totalMinutesWorked / 60);
                                            $minutesWorked = $totalMinutesWorked % 60;
                                            $timeWorked = sprintf('%02d:%02d', $hoursWorkedFormatted, $minutesWorked);
                                        @endphp
                                        <div class="flex justify-between">
                                            <span>Time Worked:</span>
                                            <span>{{ $timeWorked }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Hourly Earnings:</span>
                                            <span>{{ $hourlyEarnings }} DKK</span>
                                        </div>
                                    @endif
                                    @php
                                        $totalEarnings += $hourlyEarnings;
                                    @endphp
                                    @if ($project->tasks->where('taskable_type', 'App\Models\TaskProduct')->isNotEmpty())
                                        @php
                                            $productEarnings = $project->tasks
                                                ->where('taskable_type', 'App\Models\TaskProduct')
                                                ->sum('taskable.product_price');
                                            $totalEarnings += $productEarnings;
                                        @endphp
                                        <div class="flex justify-between">
                                            <span>Product Earnings:</span>
                                            <span>{{ $productEarnings }} DKK</span>
                                        </div>
                                    @endif
                                    @if ($project->tasks->where('taskable_type', 'App\Models\TaskTravel')->isNotEmpty())
                                        @php
                                            $travelCosts = $project->tasks
                                                ->where('taskable_type', 'App\Models\TaskTravel')
                                                ->sum('taskable.travel_cost');
                                            $totalEarnings += $travelCosts;
                                        @endphp
                                        <div class="flex justify-between">
                                            <span>Travel Costs:</span>
                                            <span>{{ $travelCosts }} DKK</span>
                                        </div>
                                    @endif
                                    <div class="mt-4 flex justify-between font-bold">
                                        <span>Total Earnings:</span>
                                        <span>{{ $totalEarnings }} DKK</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <div class="flex items-center space-x-4">
                                    <a href="{{ route('projects.show', $project) }}"
                                        class="text-green-600 hover:text-green-700"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('projects.edit', $project) }}"
                                        class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700"><i
                                                class="fas fa-trash"></i></button>
                                    </form>

                                    <div class="relative inline-flex" x-data="{ open: false, modalOpen: false, invoiceModalOpen: false }"
                                        @click.away="open = false">
                                        <button @click="open = !open"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            More
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right end-0"
                                            @click="open = false" style="display: none;">
                                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                                <button @click="modalOpen = true; loadSelect2();"
                                                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                                    Edit Client
                                                </button>
                                                <button @click="invoiceModalOpen = true"
                                                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                                    Edit Invoice Status
                                                </button>
                                                <a href="{{ route('projects.invoice', $project) }}"
                                                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                                    View Invoice
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('projects.toggleCompletion', $project) }}"
                                                    class="block w-full text-start">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="w-full px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                                        {{ $project->status == 'completed' ? 'Mark as ongoing' : 'Mark as completed' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <!-- Edit Client Modal -->
                                        <div x-show="modalOpen"
                                            class="fixed inset-0 flex items-center justify-center z-50">
                                            <div class="fixed inset-0 bg-black opacity-50"></div>
                                            <!-- Modal content -->
                                            <div class="fixed z-10 inset-0 overflow-y-auto pointer-events-none"
                                                id="editClientModal" tabindex="-1" role="dialog"
                                                aria-labelledby="modal-title" aria-hidden="true">
                                                <div
                                                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 pointer-events-auto">
                                                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                                        @click.away="modalOpen = false; unloadSelect2();">
                                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                                id="modal-title">
                                                                Edit Client
                                                            </h3>
                                                            <div class="mt-2">
                                                                <select name="client_id"
                                                                    class="client-select hidden-select"
                                                                    style="width: 100%"
                                                                    onchange="updateClient({{ $project->id }}, this.value)">
                                                                    @foreach ($clients as $client)
                                                                        <option value="{{ $client->id }}">
                                                                            {{ $client->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Edit Invoice Modal -->
                                        <div x-show="invoiceModalOpen"
                                            class="fixed inset-0 flex items-center justify-center z-50">
                                            <div class="fixed inset-0 bg-black opacity-50"></div>
                                            <!-- Modal content -->
                                            <!-- Edit Invoice Status Modal -->
                                            <div class="fixed z-10 inset-0 overflow-y-auto pointer-events-none"
                                                id="editInvoiceModal" tabindex="-1" role="dialog"
                                                aria-labelledby="modal-title" aria-hidden="true">
                                                <div
                                                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 pointer-events-auto">
                                                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                                        @click.away="invoiceModalOpen = false">
                                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                                id="modal-title">
                                                                Edit Invoice Status
                                                            </h3>
                                                            <div class="mt-2">
                                                                <div class="inline-block relative w-64">
                                                                    <select name="invoice_status"
                                                                        onchange="updateInvoiceStatus({{ $project->id }}, this.value)"
                                                                        class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                                                                        <option value="generated"
                                                                            {{ $project->invoice_status == 'generated' ? 'selected' : '' }}>
                                                                            Generated</option>
                                                                        <option value="sent"
                                                                            {{ $project->invoice_status == 'sent' ? 'selected' : '' }}>
                                                                            Sent</option>
                                                                        <option value="paid"
                                                                            {{ $project->invoice_status == 'paid' ? 'selected' : '' }}>
                                                                            Paid</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    {{-- <div x-data="{ open: false }" @click.away="open = false">
                                        <button @click="open = !open"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            More
                                        </button>

                                        <div x-data="{ open: false }" @keydown.escape.window="open = false"
                                            x-init="$watch('open', value => {
                                                if (value) {
                                                    loadSelect2();
                                                } else {
                                                    unloadSelect2();
                                                }
                                            })">
                                            <button
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                                @click="open = true; loadSelect2();">Edit Client
                                            </button>

                                            <div x-show="open"
                                                class="fixed inset-0 flex items-center justify-center z-50">
                                                <div class="fixed inset-0 bg-black opacity-50"></div>
                                                <!-- Modal content -->
                                                <!-- Edit Client Modal -->
                                                <div class="fixed z-10 inset-0 overflow-y-auto pointer-events-none"
                                                    id="editClientModal" tabindex="-1" role="dialog"
                                                    aria-labelledby="modal-title" aria-hidden="true">
                                                    <div
                                                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 pointer-events-auto">
                                                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                                            @click.away="open = false; unloadSelect2();">
                                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                                <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                                    id="modal-title">
                                                                    Edit Client
                                                                </h3>
                                                                <div class="mt-2">
                                                                    <select name="client_id"
                                                                        class="client-select hidden-select"
                                                                        style="width: 100%"
                                                                        onchange="updateClient({{ $project->id }}, this.value)">
                                                                        @foreach ($clients as $client)
                                                                            <option value="{{ $client->id }}">
                                                                                {{ $client->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div x-data="{ open: false }" @keydown.escape.window="open = false">
                                            <button
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                                @click="open = true">Edit Invoice Status
                                            </button>

                                            <div x-show="open"
                                                class="fixed inset-0 flex items-center justify-center z-50">
                                                <div class="fixed inset-0 bg-black opacity-50"></div>
                                                <!-- Modal content -->
                                                <!-- Edit Invoice Status Modal -->
                                                <div class="fixed z-10 inset-0 overflow-y-auto pointer-events-none"
                                                    id="editInvoiceModal" tabindex="-1" role="dialog"
                                                    aria-labelledby="modal-title" aria-hidden="true">
                                                    <div
                                                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 pointer-events-auto">
                                                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                                            @click.away="open = false">
                                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                                <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                                    id="modal-title">
                                                                    Edit Invoice Status
                                                                </h3>
                                                                <div class="mt-2">
                                                                    <select name="invoice_status"
                                                                        onchange="updateInvoiceStatus({{ $project->id }}, this.value)">
                                                                        <option value="generated"
                                                                            {{ $project->invoice_status == 'generated' ? 'selected' : '' }}>
                                                                            Generated
                                                                        </option>
                                                                        <option value="sent"
                                                                            {{ $project->invoice_status == 'sent' ? 'selected' : '' }}>
                                                                            Sent</option>
                                                                        <option value="paid"
                                                                            {{ $project->invoice_status == 'paid' ? 'selected' : '' }}>
                                                                            Paid</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($project->status == 'completed')
                                            <a href="{{ route('projects.invoice', $project) }}"
                                                class="underline">View
                                                Invoice</a>
                                        @endif
                                        <form method="POST" action="{{ route('projects.complete', $project) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Mark
                                                as
                                                completed</button>
                                        </form>
                                    </div> --}}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

{{-- <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script> --}}

{{-- <!-- Load jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

 <!-- Load Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- Load Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script> --}}

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

    // // Load Select2 when the document is ready
    // $(document).ready(function() {
    //     loadSelect2();
    // });

    // Function to load Select2
    function loadSelect2() {
        // Check if Select2 is already initialized
        if (!$('.client-select').hasClass('select2-hidden-accessible')) {
            // Load jQuery
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js';
            script.onload = function() {
                // Load Select2 CSS
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
                document.head.appendChild(link);

                // Load Select2 JS
                var script2 = document.createElement('script');
                script2.type = 'text/javascript';
                script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                script2.onload = function() {
                    // Wait until the document is fully loaded
                    $(document).ready(function() {
                        // Initialize Select2
                        $('.client-select').select2();
                        // Make the select element visible
                        $('.client-select').css('display', 'block');
                        // Remove the hidden-select class
                        $('.client-select').removeClass('hidden-select');
                    });
                }
                document.head.appendChild(script2);
            }
            document.head.appendChild(script);
        }
    }

    // Function to unload Select2
    function unloadSelect2() {
        // Check if Select2 is initialized
        if ($('.client-select').hasClass('select2-hidden-accessible')) {
            // Destroy Select2
            $('.client-select').select2('destroy');
        }

        // Remove Select2 scripts and styles
        $('head').find('script[src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"]').remove();
        $('head').find('link[href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"]')
            .remove();
        $('head').find('script[src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"]')
            .remove();
    }
</script>
