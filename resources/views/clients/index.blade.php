@php
    use App\Models\Client;
@endphp

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.js"></script>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Client Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Success message -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Error message -->
                    @error('file')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @enderror

                    <!-- Search form -->
                    <form method="GET" action="{{ route('clients.index') }}" class="mb-3 flex">
                        <input type="text" class="form-input flex-grow mr-3" placeholder="Search clients"
                            name="search[]">
                        @if (request('search'))
                            @foreach (request('search') as $search)
                                <input type="hidden" name="search[]" value="{{ $search }}">
                            @endforeach
                        @endif
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </form>

                    <!-- Search tags -->
                    <div class="tags flex flex-wrap">
                        @if (request('search') && count(request('search')) > 0)
                            @foreach (request('search') as $search)
                                @if ($search != '')
                                    <span class="tag bg-gray-400 text-white px-2 py-1 m-1 rounded">
                                        {{ $search }}
                                        <a href="{{ url('clients') }}?search[]={{ implode('&search[]=', array_diff(request('search'), [$search])) }}"
                                            class="ml-1 text-white">x</a>
                                    </span>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <div class="flex space-x-4">
                    <!-- Add new client form -->
                    <!-- Add new client button -->
                    <a href="{{ route('clients.create') }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Client
                    </a>
                    {{-- <form method="POST" action="{{ route('clients.store') }}">
                        @csrf
                        <!-- Add new client button -->
                        <a href="{{ route('clients.create') }}" class="btn btn-primary">Add Client</a>
                    </form> --}}

                    <!-- Import clients button -->
                    <button id="importButton" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Import Clients
                    </button>
                    </div>

                    <!-- Import clients form (hidden by default) -->
                    <form id="importForm" action="{{ route('clients.import') }}" method="post" enctype="multipart/form-data" class="mt-4 hidden">
                        @csrf
                        <div class="form-group">
                            <p class="text-gray-700 text-sm mb-2">
                                To import a list of clients, please upload a CSV or a TXT file with the following columns in this exact order: name, email, cvr, phone, address. The first line should be the column names. The 'name' column is the only required field. You can add multiple clients at once by filling out the data for each client on a new line, as long as the 'name' field is on its own line or entry. Please do not add any other headers besides the ones listed. The order of the columns is important and should match the order in the template. You can download a <a href="{{ asset('csv/template.csv') }}" class="text-blue-500 hover:underline">template here</a>.
                            </p>
                            <label for="file" class="block text-gray-700 text-sm font-bold mb-2">Upload CSV file:</label>
                            <input type="file" name="file" id="file" accept=".csv,.txt" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <button type="submit" class="mt-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Submit
                        </button>
                    </form>

                    <!-- Client table -->
                    <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200" id="clientTable">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                                Contact Details
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                                Status
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tags
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($clients as $client)
                                            <tr class="client-row cursor-pointer flex flex-col md:table-row">
                                                <td class="px-6 py-4 whitespace-nowrap client-name">{{ $client->name }}</td>
                                                <div id="client-details-{{ $client->id }}">
                                                    <td class="px-6 py-4 whitespace-nowrap client-details md:table-cell">
                                                        @if ($client->phone)
                                                            <strong>Phone Number:</strong> {{ $client->phone }}<br>
                                                        @endif
                                                        @if ($client->email)
                                                            <strong>Email:</strong> {{ $client->email }}
                                                        @endif
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap client-details">
                                                        {{ $client->status }}
                                                        <select name="status" class="form-select block w-full mt-1"
                                                            data-client-id="{{ $client->id }}">
                                                            <option value="{{ Client::STATUS_LEAD }}"
                                                                {{ $client->status == Client::STATUS_LEAD ? 'selected' : '' }}>
                                                                Lead</option>
                                                            <option value="{{ Client::STATUS_CONTACTED }}"
                                                                {{ $client->status == Client::STATUS_CONTACTED ? 'selected' : '' }}>
                                                                Contacted</option>
                                                            <option value="{{ Client::STATUS_INTERESTED }}"
                                                                {{ $client->status == Client::STATUS_INTERESTED ? 'selected' : '' }}>
                                                                Interested</option>
                                                            <option value="{{ Client::STATUS_NEGOTIATION }}"
                                                                {{ $client->status == Client::STATUS_NEGOTIATION ? 'selected' : '' }}>
                                                                Negotiation</option>
                                                            <option value="{{ Client::STATUS_DEAL_MADE }}"
                                                                {{ $client->status == Client::STATUS_DEAL_MADE ? 'selected' : '' }}>
                                                                Deal Made</option>
                                                        </select>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if ($client->tags->isNotEmpty())
                                                            <button
                                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto text-center"
                                                                type="button"
                                                                onclick="toggleTags({{ $client->id }}, event);">
                                                                Show Tags
                                                            </button>
                                                            {{-- <!-- Tags section for mobile view, needs rework-->
                                                            <div style="display: none" id="tags-{{ $client->id }}" class="sm:hidden">
                                                                <div style="display: flex; flex-wrap: wrap;">
                                                                    @foreach ($client->tags as $tag)
                                                                        <span
                                                                            class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200 text-gray-800' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div> --}}
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap client-details">
                                                        <div
                                                            class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                                            <a href="{{ route('clients.show', $client) }}"
                                                                class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto text-center">View</a>
                                                            <a href="{{ route('clients.edit', $client) }}"
                                                                class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto text-center">Edit</a>
                                                            <form method="POST"
                                                                action="{{ route('clients.destroy', $client) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="inline-block bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded  w-full sm:w-auto text-center"
                                                                    onclick="return confirm('Are you sure you want to delete this item?')">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </div>
                                            </tr>
                                            <tr style="display: none" id="tags-{{ $client->id }}" class="md:block">
                                                <td colspan="5" class="px-6 py-4 whitespace-nowrap">
                                                    <div style="display: flex; flex-wrap: wrap;">
                                                        @foreach ($client->tags as $tag)
                                                            <span
                                                                class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200 text-gray-800' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Pagination links -->
                    <div class="mt-4">
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('select[name="status"]').change(function() {
                var clientId = $(this).data('client-id');
                var status = $(this).val();

                $.ajax({
                    url: '/clients/' + clientId + '/status',
                    method: 'POST',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'status': status
                    },
                    success: function(response) {
                        // The server responded with a success status code
                        // 'response' contains the data sent back by the server
                        console.log('Status updated successfully');
                        console.log(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // The server responded with an error status code
                        // 'jqXHR' is an object with information about the failed request
                        // 'textStatus' is a string describing the type of error
                        // 'errorThrown' is an optional exception object, if one occurred
                        console.log('Error updating status');
                        console.log(textStatus, errorThrown);
                    }
                });
            });
            $('.client-row').on('click', function(event) {
            // Check if the window's width is less than or equal to 768px
            if (window.innerWidth <= 768) {
                // Check if the event target has the 'client-name' class
                if ($(event.target).hasClass('client-name')) {
                    $(this).find('.client-details').toggleClass('hidden sm:block md:table-cell');
                }
            }
        });
            // Prevent click events from propagating up to .client-row when a button or other interactive element is clicked
            $('.client-row .client-details, .client-row .client-details *').on('click', function(event) {
                event.stopPropagation();
            });
        });

        function toggleClientDetails(id) {
            var details = document.getElementById('client-details-' + id);
            details.classList.toggle('hidden');
        }

        function toggleTags(id, event) {
            var tags = document.getElementById('tags-' + id);
            if (tags.style.display === "none") {
                tags.style.display = "table-row";
            } else {
                tags.style.display = "none";
            }
            event.stopPropagation();
        }
        document.getElementById('importButton').addEventListener('click', function() {
        var form = document.getElementById('importForm');
        form.classList.toggle('hidden');
    });
    // Automatically dismiss the alert after 5 seconds
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, 5000);
    </script>
</x-app-layout>
