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
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3"
                                onclick="this.parentElement.style.display='none';">
                                <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <title>Close</title>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Error message -->
                    @error('file')
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                            role="alert">
                            <span class="block sm:inline">{{ $message }}</span>
                            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3"
                                onclick="this.parentElement.style.display='none';">
                                <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <title>Close</title>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @enderror

                    <!-- Search form -->
                    <form method="GET" action="{{ route('clients.index') }}" class="mb-6">
                        <div class="flex items-center mb-3">
                            <input type="text" class="form-input flex-grow mr-3 border rounded p-2"
                                placeholder="Search clients" name="search">
                            @foreach ((array) request('search') as $search)
                                <input type="hidden" name="search[]" value="{{ $search }}">
                            @endforeach
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                type="submit">Search</button>
                        </div>

                        <!-- Radio buttons for status -->
                        <div class="radio-group flex flex-col sm:flex-row sm:items-center mb-3">
                            <span class="mr-3 font-semibold">Status:</span>

                            <div class="flex items-center mb-2 sm:mb-0 sm:mr-3">
                                <input type="radio" id="status_all" name="status" value=""
                                    {{ empty(request('status')) ? 'checked' : '' }}>
                                <label for="status_all" class="ml-1">All</label>
                            </div>

                            <div class="flex items-center mb-2 sm:mb-0 sm:mr-3">
                                <input type="radio" id="status_lead" name="status" value="lead"
                                    {{ request('status') === 'lead' ? 'checked' : '' }}>
                                <label for="status_lead" class="ml-1">Lead</label>
                            </div>

                            <div class="flex items-center mb-2 sm:mb-0 sm:mr-3">
                                <input type="radio" id="status_contacted" name="status" value="contacted"
                                    {{ request('status') === 'contacted' ? 'checked' : '' }}>
                                <label for="status_contacted" class="ml-1">Contacted</label>
                            </div>

                            <div class="flex items-center mb-2 sm:mb-0 sm:mr-3">
                                <input type="radio" id="status_interested" name="status" value="interested"
                                    {{ request('status') === 'interested' ? 'checked' : '' }}>
                                <label for="status_interested" class="ml-1">Interested</label>
                            </div>

                            <div class="flex items-center mb-2 sm:mb-0 sm:mr-3">
                                <input type="radio" id="status_negotiation" name="status" value="negotiation"
                                    {{ request('status') === 'negotiation' ? 'checked' : '' }}>
                                <label for="status_negotiation" class="ml-1">Negotiation</label>
                            </div>

                            <div class="flex items-center mb-2 sm:mb-0 sm:mr-3">
                                <input type="radio" id="status_deal_made" name="status" value="deal_made"
                                    {{ request('status') === 'deal_made' ? 'checked' : '' }}>
                                <label for="status_deal_made" class="ml-1">Deal Made</label>
                            </div>
                        </div>
                    </form>

                    <!-- Header Section for Clients -->
                    <div class="text-center mb-8">
                        <h1 class="text-4xl font-bold text-blue-500 mb-4">Clients</h1>

                        <!-- Client Counter -->
                        <div class="mb-6">
                            <p class="text-lg font-semibold text-gray-800">
                                You have created <span class="text-blue-500">{{ $clientCount }}</span> out of <span class="text-blue-500">{{ $clientLimit }}</span> clients.
                            </p>

                            @if ($clientCount < $clientLimit)
                                <p class="text-green-500">You can add {{ $clientLimit - $clientCount }} more clients.</p>
                            @else
                                <p class="text-red-500">You have reached your client limit.</p>
                            @endif
                        </div>

                        <!-- Create Client Button -->
                        <button type="button" 
                            onclick="window.location.href='{{ route('clients.create') }}'" 
                            class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out {{ $clientCount >= $clientLimit ? 'opacity-50 cursor-not-allowed' : '' }}"
                            @if ($clientCount >= $clientLimit) disabled @endif>
                            Create Client
                        </button>
                    </div>
                                        

                    <!-- Search tags -->
                    <div class="tags flex flex-wrap mb-6">
                        @foreach (is_array(request('search')) ? request('search') : (request('search') ? [request('search')] : []) as $search)
                            @if ($search != '')
                                <span class="tag bg-gray-400 text-white px-2 py-1 m-1 rounded">
                                    {{ $search }}
                                    <a href="{{ url('clients') }}?{{ http_build_query(array_merge(request()->except('search'), ['search' => array_diff(is_array(request('search')) ? request('search') : [request('search')], [$search])])) }}"
                                        class="ml-1 text-white">x</a>
                                </span>
                            @endif
                        @endforeach
                    </div>




                    {{-- Test delete 2 tags  --}}
                    {{-- @if (request('status'))
                        <span class="tag bg-gray-400 text-white px-2 py-1 m-1 rounded">
                            Status: {{ ucfirst(request('status')) }}
                            <a href="{{ url('clients') }}" class="ml-1 text-white">x</a>
                        </span>
                    @endif --}}
                </div>



                
                <div class="flex space-x-4 mb-6">
                    {{-- <!-- Add new client button -->
                    <a href="{{ route('clients.create') }}"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Add Client
                    </a> --}}

                    <!-- Import clients button -->
                    {{-- <button id="importButton"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Import Clients
                    </button> --}}
                </div>

                <!-- Import clients form (hidden by default) -->
                <form id="importForm" action="{{ route('clients.import') }}" method="post"
                    enctype="multipart/form-data" class="mb-6 hidden">
                    @csrf
                    <div class="form-group">
                        <p class="text-gray-700 text-sm mb-2">
                            To import a list of clients, please upload a CSV or a TXT file with the following columns in
                            this exact order: name, email, cvr, phone, address. The first line should be the column
                            names. The 'name' column is the only required field. You can add multiple clients at once by
                            filling out the data for each client on a new line, as long as the 'name' field is on its
                            own line or entry. Please refrain from adding any additional headers besides the ones
                            listed. The order of the columns is crucial and should match the order in the template.
                        </p>
                        <p>
                            <strong>Accepted file types:</strong> .csv, .txt
                        </p>
                        <p>
                            <strong>Max file size:</strong> 2MB
                        </p>
                        <br>
                        <p>
                            You can download a <a href="{{ asset('csv/template.csv') }}"
                                class="text-blue-500 hover:underline">template here</a>
                        </p>
                        <br>
                        <label for="file" class="block text-gray-700 text-sm font-bold mb-2">Upload file:</label>
                        <input type="file" name="file" id="file" accept=".csv,.txt"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <button type="submit"
                        class="mt-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Submit
                    </button>
                </form>

                <!-- Client cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($clients as $client)
                        <div class="bg-white shadow-md rounded-lg overflow-hidden flex flex-col">
                            <div class="px-6 py-4 flex-grow">
                                <div class="mb-2">
                                    <h2 class="text-lg font-semibold text-gray-800">{{ $client->name }}</h2>
                                </div>
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Phone:</strong> {{ $client->phone }}
                                </div>
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Email:</strong> {{ $client->email }}
                                </div>
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Address:</strong> {{ $client->address }}
                                </div>
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Status:</strong> {{ $client->status }}
                                </div>

                                <!-- Client tags -->
                                @if ($client->tags->isNotEmpty())
                                    <div class="tags-container mt-2">
                                        <p class="text-lg mb-2">Tags:</p>
                                        <div class="flex flex-wrap mb-2">
                                            @foreach ($client->tags->take(5) as $tag)
                                                <span
                                                    class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200 text-gray-800' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                        @if ($client->tags->count() > 5)
                                            <button id="moreTagsButton-{{ $client->id }}"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded mb-2"
                                                type="button" onclick="toggleTags({{ $client->id }}, event);">
                                                +{{ $client->tags->count() - 5 }}
                                            </button>
                                            <div style="display: none" id="hidden-tags-{{ $client->id }}"
                                                class="hidden-tags">
                                                @foreach ($client->tags->slice(5) as $tag)
                                                    <span
                                                        class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200 text-gray-800' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Dropdown for changing status -->
                            <div class="px-6 py-2 border-t border-gray-200 flex items-center bg-gray-100">
                                <div class="status-dropdown flex-grow mr-4">
                                    <form method="POST" action="{{ route('clients.updateStatus', $client) }}">
                                        @csrf
                                        <select name="status"
                                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                            data-client-id="{{ $client->id }}">
                                            <option value="{{ Client::STATUS_LEAD }}"
                                                {{ $client->status == Client::STATUS_LEAD ? 'selected' : '' }}>Lead
                                            </option>
                                            <option value="{{ Client::STATUS_CONTACTED }}"
                                                {{ $client->status == Client::STATUS_CONTACTED ? 'selected' : '' }}>Contacted
                                            </option>
                                            <option value="{{ Client::STATUS_INTERESTED }}"
                                                {{ $client->status == Client::STATUS_INTERESTED ? 'selected' : '' }}>Interested
                                            </option>
                                            <option value="{{ Client::STATUS_NEGOTIATION }}"
                                                {{ $client->status == Client::STATUS_NEGOTIATION ? 'selected' : '' }}>Negotiation
                                            </option>
                                            <option value="{{ Client::STATUS_DEAL_MADE }}"
                                                {{ $client->status == Client::STATUS_DEAL_MADE ? 'selected' : '' }}>Deal Made
                                            </option>
                                        </select>
                                    </form>
                                </div>

                                <!-- Edit and Delete buttons -->
                                <!-- Wrapper for Alpine.js state -->
                                <div x-data="{ showDeleteModal: false }">
                                    <!-- Edit and Delete buttons -->
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('clients.edit', $client) }}"
                                        class="inline-flex items-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Edit
                                        </a>

                                        <!-- Trigger for the delete confirmation modal -->
                                        <button @click="showDeleteModal = true"
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                            Delete
                                        </button>
                                    </div>

                                    <!-- Delete confirmation modal -->
                                    <div x-show="showDeleteModal" class="fixed inset-0 flex items-center justify-center z-50">
                                        <div class="bg-gray-800 bg-opacity-75 absolute inset-0" @click="showDeleteModal = false"></div>

                                        <div class="bg-white p-6 rounded shadow-md z-10 max-w-md mx-auto">
                                            <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
                                            <p class="mb-6">Are you sure you want to delete this client? This action cannot be undone.</p>

                                            <div class="flex justify-end space-x-4">
                                                <!-- Cancel button -->
                                                <button @click="showDeleteModal = false" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">
                                                    Cancel
                                                </button>

                                                <!-- Confirm Delete button -->
                                                <form method="POST" action="{{ route('clients.destroy', $client) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                                        Confirm
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination links -->
                <div class="mt-6 flex items-center justify-between">
                    {{ $clients->links() }}
                    <form method="GET" action="{{ route('clients.index') }}">
                        <div class="inline-block relative w-32">
                            <select name="pageSize" onchange="this.form.submit()"
                                class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                                <option value="10" {{ request('pageSize') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('pageSize') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('pageSize') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('pageSize') == 100 ? 'selected' : '' }}>100</option>
                                <option value="all" {{ request('pageSize') == 'all' ? 'selected' : '' }}>All</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>

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
        white-space: nowrap;
    }

    .client-details {
        width: 100%;
        min-width: 120px;
        max-width: 400px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        background-color: white;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .card-body {
        font-size: 14px;
        line-height: 1.5;
    }

    .card-actions {
        margin-top: 8px;
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

{{-- <script src="//unpkg.com/alpinejs" defer></script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle import form visibility
        const importButton = document.getElementById('importButton');
        if (importButton) {
            importButton.addEventListener('click', function() {
                const importForm = document.getElementById('importForm');
                importForm.classList.toggle('hidden');
            });
        }

        // Toggle client details visibility on mobile
        document.querySelectorAll('.client-row').forEach(function(clientRow) {
            clientRow.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && event.target.classList.contains(
                    'client-name')) {
                    clientRow.querySelector('.client-details').classList.toggle('hidden');
                }
            });
        });

        // Prevent click events from propagating up to .client-row when a button or other interactive element is clicked
        document.querySelectorAll('.client-row .client-details, .client-row .client-details *').forEach(
            function(element) {
                element.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });

        // Toggle tags visibility
        document.querySelectorAll('[id^="moreTagsButton-"]').forEach(function(button) {
            button.addEventListener('click', function(event) {
                const clientId = button.id.split('-')[1];
                toggleTags(clientId, event);
            });
        });

        function toggleTags(id, event) {
            const hiddenTags = document.getElementById('hidden-tags-' + id);
            const moreTagsButton = document.getElementById('moreTagsButton-' + id);
            if (hiddenTags.style.display === "none") {
                hiddenTags.style.display = "block";
                moreTagsButton.innerHTML = '−';
            } else {
                hiddenTags.style.display = "none";
                moreTagsButton.innerHTML = '+' + hiddenTags.children.length;
            }
            event.stopPropagation();
        }

        // Handle status change
        $('select[name="status"]').change(function() {
            const clientId = $(this).data('client-id');
            const status = $(this).val();
            const token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: `/clients/${clientId}/status`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    'status': status
                },
                success: function(response) {
                    // Update the UI or show a message based on the response
                    // alert(response.message); // Display the success message
                    console.log('Status updated successfully', response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error updating status', textStatus, errorThrown);
                    console.error('Response Text:', jqXHR.responseText);
                }
            });
        });

        // Automatically dismiss alerts
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function() {
                $(this).remove();
            });
        }, 5000);

        // Handle adding multiple search terms
        const searchInput = document.querySelector('input[name="search"]');
        const searchForm = searchInput.closest('form');

        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const newSearch = searchInput.value.trim();
            if (newSearch) {
                // Check if the new search term already exists
                let existingInputs = document.querySelectorAll('input[name="search[]"]');
                let alreadyExists = Array.from(existingInputs).some(input => input.value === newSearch);

                if (!alreadyExists) {
                    // Create a new hidden input for the new search term
                    const newHiddenInput = document.createElement('input');
                    newHiddenInput.type = 'hidden';
                    newHiddenInput.name = 'search[]';
                    newHiddenInput.value = newSearch;

                    // Append the new hidden input to the form
                    searchForm.appendChild(newHiddenInput);
                }
            }

            // Submit the form
            searchForm.submit();
        });

        // Handle radio buttons for search
        let radios = document.querySelectorAll('input[name="status"]');

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Get all existing search tags
                let searchTags = Array.from(document.querySelectorAll('input[name="search[]"]'))
                    .map(input => input.value);
                    
                // Remove any existing status tags
                searchTags = searchTags.filter(tag => !tag.startsWith('status:'));

                // If the radio button is selected and not "All"
                if (this.value) {
                    // Add the new status tag
                    searchTags.push('status:' + this.value);
                }

                // Remove all existing search inputs
                document.querySelectorAll('input[name="search[]"]').forEach(input => input.remove());

                // Add all search tags back as hidden inputs
                searchTags.forEach(tag => {
                    const newHiddenInput = document.createElement('input');
                    newHiddenInput.type = 'hidden';
                    newHiddenInput.name = 'search[]';
                    newHiddenInput.value = tag;
                    searchForm.appendChild(newHiddenInput);
                });

                // Submit the form
                searchForm.submit();
            });
        });

        // Handle adding multiple search terms
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const newSearch = searchInput.value.trim();

            if (newSearch) {
                // Check if the new search term already exists
                let existingInputs = document.querySelectorAll('input[name="search[]"]');
                let alreadyExists = Array.from(existingInputs).some(input => input.value === newSearch);

                if (!alreadyExists) {
                    // Create a new hidden input for the new search term
                    const newHiddenInput = document.createElement('input');
                    newHiddenInput.type = 'hidden';
                    newHiddenInput.name = 'search[]';
                    newHiddenInput.value = newSearch;

                    // Append the new hidden input to the form
                    searchForm.appendChild(newHiddenInput);
                }
            }

            // Submit the form after adding the search term
            searchForm.submit();
        });

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

        // Handle has_email and has_phone filters
        $('#has_email, #has_phone').change(function() {
            const hasEmail = $('#has_email').is(':checked') ? 1 : 0;
            const hasPhone = $('#has_phone').is(':checked') ? 1 : 0;
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
