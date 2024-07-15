@php
    use App\Models\Client;
@endphp

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.js"></script>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Success message -->
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none';">
                                    <title>Close</title>
                                    <path d="M14.348 14.849a1 1 0 001.415-1.415L11.415 10l4.348-4.348a1 1 0 00-1.415-1.415L10 8.585 5.652 4.232a1 1 0 10-1.415 1.415L8.585 10l-4.348 4.348a1 1 0 001.415 1.415L10 11.415l4.348 4.348z"/>
                                </svg>
                            </span>
                        </div>
                    @endif

                    <!-- Error message -->
                    @error('file')
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ $message }}</span>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none';">
                                    <title>Close</title>
                                    <path d="M14.348 14.849a1 1 0 001.415-1.415L11.415 10l4.348-4.348a1 1 0 00-1.415-1.415L10 8.585 5.652 4.232a1 1 0 10-1.415 1.415L8.585 10l-4.348 4.348a1 1 0 001.415 1.415L10 11.415l4.348 4.348z"/>
                                </svg>
                            </span>
                        </div>
                    @enderror

                    <!-- Search form -->
                    <form method="GET" action="{{ route('clients.index') }}" class="mb-3 flex space-x-2">
                        <input type="text" class="form-input flex-grow mr-3 border rounded p-2" placeholder="Search clients" name="search[]">
                        @if (request('search'))
                            @foreach (request('search') as $search)
                                <input type="hidden" name="search[]" value="{{ $search }}">
                            @endforeach
                        @endif
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">Search</button>
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

                    <div class="flex space-x-4 mb-4">
                        <!-- Add new client button -->
                        <a href="{{ route('clients.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Add Client
                        </a>

                        <!-- Import clients button -->
                        <button id="importButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Import Clients
                        </button>
                    </div>

                    <!-- Import clients form (hidden by default) -->
                    <form id="importForm" action="{{ route('clients.import') }}" method="post" enctype="multipart/form-data" class="mt-4 hidden">
                        @csrf
                        <div class="form-group">
                            <p class="text-gray-700 text-sm mb-2">
                                To import a list of clients, please upload a CSV or a TXT file with the following columns in this exact order: name, email, cvr, phone, address. The first line should be the column names. The 'name' column is the only required field. You can add multiple clients at once by filling out the data for each client on a new line, as long as the 'name' field is on its own line or entry. Please refrain from adding any additional headers besides the ones listed. The order of the columns is crucial and should match the order in the template.
                            </p>
                            <p>
                                <strong>Accepted file types:</strong> .csv, .txt
                            </p>
                            <p>
                                <strong>Max file size:</strong> 2MB
                            </p>
                            <br>
                            <p>
                                You can download a <a href="{{ asset('csv/template.csv') }}" class="text-blue-500 hover:underline">template here</a>
                            </p>
                            <br>
                            <label for="file" class="block text-gray-700 text-sm font-bold mb-2">Upload file:</label>
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
                                            <th class="header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th class="header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                                Contact Details
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($clients as $client)
                                            <tr class="client-row cursor-pointer flex flex-col md:table-row">
                                                <td class="header-name px-6 py-4 whitespace-nowrap client-name font-bold" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $client->name }}">{{ $client->name }}</td>
                                                <div id="client-details-{{ $client->id }}">
                                                    <td class="px-6 py-4 whitespace-nowrap client-details md:table-cell">
                                                        @if ($client->phone)
                                                            <strong>Phone Number:</strong> <br>
                                                            {{ $client->phone }} <br>
                                                        @endif
                                                        @if ($client->email)
                                                            <strong>Email:</strong> <br>
                                                            {{ $client->email }}<br>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="inline-block relative w-32">
                                                            <select name="status" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline" data-client-id="{{ $client->id }}">
                                                                <option value="{{ Client::STATUS_LEAD }}" {{ $client->status == Client::STATUS_LEAD ? 'selected' : '' }}>Lead</option>
                                                                <option value="{{ Client::STATUS_CONTACTED }}" {{ $client->status == Client::STATUS_CONTACTED ? 'selected' : '' }}>Contacted</option>
                                                                <option value="{{ Client::STATUS_INTERESTED }}" {{ $client->status == Client::STATUS_INTERESTED ? 'selected' : '' }}>Interested</option>
                                                                <option value="{{ Client::STATUS_NEGOTIATION }}" {{ $client->status == Client::STATUS_NEGOTIATION ? 'selected' : '' }}>Negotiation</option>
                                                                <option value="{{ Client::STATUS_DEAL_MADE }}" {{ $client->status == Client::STATUS_DEAL_MADE ? 'selected' : '' }}>Deal Made</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap client-details">
                                                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                                            {{-- <a href="{{ route('clients.show', $client) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto text-center">View</a> --}}
                                                            <a href="{{ route('clients.edit', $client) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto text-center">Edit</a>
                                                            <form method="POST" action="{{ route('clients.destroy', $client) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="inline-block bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded  w-full sm:w-auto text-center" onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </div>
                                            </tr>
                                            @if ($client->tags->isNotEmpty())
                                                <tr id="tags-{{ $client->id }}" class="divide-y divide-gray-200">
                                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap">
                                                        <div class="tags-container">
                                                            <p class="text-lg mb-2">Tags: </p>
                                                            @foreach ($client->tags->take(5) as $tag)
                                                                <span class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200 text-gray-800' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                                            @endforeach
                                                            @if ($client->tags->count() > 5)
                                                                <button id="moreTagsButton-{{ $client->id }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full sm:w-auto text-center" type="button" onclick="toggleTags({{ $client->id }}, event);">
                                                                    +{{ $client->tags->count() - 5 }}
                                                                </button>
                                                                <div style="display: none" id="hidden-tags-{{ $client->id }}" class="hidden-tags">
                                                                    @foreach ($client->tags->slice(5) as $tag)
                                                                        <span class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200 text-gray-800' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Pagination links -->
                    <div class="mt-4 flex items-center justify-between">
                        {{ $clients->links() }}
                        <form method="GET" action="{{ route('clients.index') }}">
                            <div class="inline-block relative w-32">
                                <select name="pageSize" onchange="this.form.submit()" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="10" {{ request('pageSize') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('pageSize') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('pageSize') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('pageSize') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ request('pageSize') == 'all' ? 'selected' : '' }}>All</option> <!-- Add 'all' to the request parameters -->
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .header {
            width: 100%;
            min-width: 120px;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .header-name {
            width: 100%;
            min-width: 80px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;}

        .client-details {
            width: 100%;
            min-width: 120px;
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        select[name="status"] {
            width: 130px; /* Adjust as needed */
        }
    
        @media only screen and (max-width: 600px) {
            .header {
                font-size: 14px;
            }
            .client-row {
                font-size: 14px;
            }
        }

        .radio-group {
            display: flex;
            gap: 10px;
        }
    </style>

    <script>
        document.getElementById('importButton').addEventListener('click', function() {
            var importForm = document.getElementById('importForm');
            if (importForm.classList.contains('hidden')) {
                importForm.classList.remove('hidden');
             } else {
                 importForm.classList.add('hidden');
             }
        });

        function toggleTags(clientId, event) {
            event.stopPropagation();
            var moreTagsButton = document.getElementById('moreTagsButton-' + clientId);
            var hiddenTags = document.getElementById('hidden-tags-' + clientId);
            if (hiddenTags.style.display === 'none') {
                hiddenTags.style.display = 'block';
                moreTagsButton.textContent = 'Show Less';
            } else {
                hiddenTags.style.display = 'none';
                moreTagsButton.textContent = '+' + hiddenTags.children.length;
            }
        }
    </script>



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
        var hiddenTags = document.getElementById('hidden-tags-' + id);
        var moreTagsButton = document.getElementById('moreTagsButton-' + id);

        if (hiddenTags.style.display === "none") {
            hiddenTags.style.display = "block";
            moreTagsButton.innerHTML = '−';
        } else {
            hiddenTags.style.display = "none";
            moreTagsButton.innerHTML = '+' + (hiddenTags.children.length);
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

    // //radio button search
    // // Get the radio buttons and the search form
    // let radios = document.querySelectorAll('.radio-group input[type="radio"]');
    // let searchForm = document.querySelector('.mb-3.flex');

    // // Function to get URL parameters
    // function getUrlParams() {
    //     let params = {};
    //     window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
    //         params[key] = value;
    //     });
    //     return params;
    // }

    // // Check URL parameters and set corresponding radio buttons as checked
    // let params = getUrlParams();
    // radios.forEach(radio => {
    //     if (params['search[]'] && params['search[]'].includes(radio.value)) {
    //         radio.checked = true;
    //     }
    // });

    // // Add a change event listener to each radio button
    // radios.forEach(radio => {
    //     radio.addEventListener('change', function() {
    //         // If the radio button is selected
    //         if (this.checked) {
    //             // Create a new hidden input
    //             let input = document.createElement('input');
    //             input.type = 'hidden';
    //             input.name = 'search[]';
    //             input.value = this.value;
    //             input.id = 'input-' + this.id;

    //             // Append the input to the search form
    //             searchForm.appendChild(input);
    //         } else {
    //             // If the radio button is deselected, remove the corresponding hidden input
    //             let input = document.querySelector('#input-' + this.id);
    //             searchForm.removeChild(input);
    //         }

    //         // Submit the form
    //         searchForm.submit();
    //     });
    // });
    
    $(document).ready(function(){
    $('#has_email, #has_phone').change(function(){
        var hasEmail = $('#has_email').is(':checked') ? 1 : 0;
        var hasPhone = $('#has_phone').is(':checked') ? 1 : 0;

        $.ajax({
            url: '/filter-clients',
            method: 'GET',
            data: {
                has_email: hasEmail,
                has_phone: hasPhone
            },
            success: function(data) {
                $('#client_list').html(data);
            }
        });
    });
});
    </script>
</x-app-layout>
