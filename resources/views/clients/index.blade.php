@php
    use App\Models\Client;
@endphp

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.js"></script>

<style>
    /* General Alert Styling */
    .alert {
        position: relative;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: opacity 0.3s ease-in-out;
    }
    /* Success and Warning Alerts */
    .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .alert-warning { background-color: #fff3cd; border-color: #ffeeba; color: #856404; }

    /* Close Button Styling */
    .alert button {
        background: transparent;
        border: none;
        cursor: pointer;
    }
    .alert svg {
        height: 1.5rem;
        width: 1.5rem;
        transition: transform 0.2s;
    }
    .alert svg:hover {
        transform: scale(1.1);
    }

    /* Main Grid Container for Client Cards */
    .client-card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        justify-content: center;
        width: 100%;
        margin: 0 auto;
    }
    
    /* Card Styling */
    .client-card {
        display: flex;
        flex-direction: column;
        max-width: 450px;
        margin: 0 auto;
        width: 100%;
        justify-content: center;
    }

    /* Center Incomplete Rows */
    .client-card-container.align-last-row { justify-items: center; }

    /* Button & Link Hover Effect */
    .client-card-container button,
    .client-card-container a.inline-flex {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .client-card-container button:hover,
    .client-card-container a.inline-flex:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    /* Tooltip Styling */
    .tooltip {
        position: absolute;
        background-color: rgba(0, 0, 0, 0.75);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.875rem;
        z-index: 1000;
        pointer-events: none;
    }

    /* Responsive Adjustments */
    @media (max-width: 1024px) {
        .client-card-container { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .client-card-container { grid-template-columns: 1fr; }
    }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Management') }}
        </h2>
    </x-slot>
    <div x-data="{ showDeleteModal: false, showSettingsModal: false, showImportModal: false }">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="alert alert-success bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                            <span class="block sm:inline font-semibold">{{ session('success') }}</span>
                            <button type="button" class="absolute top-0 right-0 p-2 focus:outline-none" onclick="this.closest('.alert').remove()">
                                <svg class="fill-current h-6 w-6 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                                    <title>Close</title>
                                    <path d="M6 6L14 14M14 6L6 14"></path>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Warning Message (No Auto Dismiss) -->
                    @if (session('warning'))
                        <div class="alert alert-warning bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg relative" role="alert">
                            <span class="block sm:inline font-semibold">{!! session('warning') !!}</span>
                            <button type="button" class="absolute top-0 right-0 p-2 focus:outline-none" onclick="this.closest('.alert').remove()">
                                <svg class="fill-current h-6 w-6 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                                    <title>Close</title>
                                    <path d="M6 6L14 14M14 6L6 14"></path>
                                </svg>
                            </button>
                        </div>
                    @endif

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
                            class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transform transition-transform hover:scale-105 focus:outline-none focus:shadow-outline duration-300 ease-in-out {{ $clientCount >= $clientLimit ? 'opacity-50 cursor-not-allowed' : '' }}"
                            @if ($clientCount >= $clientLimit) disabled @endif>
                            Create Client
                        </button>

                        <!-- CTA for Upgrade when Client Limit is Reached -->
                        @if ($clientCount >= $clientLimit)
                            <div class="mt-6 bg-yellow-100 p-4 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold text-yellow-800">Need to manage more clients?</h3>
                                <p class="text-yellow-600">Upgrade to the Freelancer plan to manage up to 25 clients and unlock more advanced features.</p>
                                <a href="{{ route('stripe.checkout', ['plan' => 'freelancer']) }}" class="mt-4 inline-block bg-yellow-500 text-white py-2 px-6 rounded-lg shadow hover:bg-yellow-600">Upgrade Now</a>
                            </div>
                        @endif
                    </div>

                    <!-- Secondary Action Buttons (Centered with Icons) -->
                    <div class="flex justify-center mb-4 space-x-4">
                        <!-- Settings button with gear icon -->
                        <button @click="showSettingsModal = true" 
                                class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded flex items-center space-x-2">
                            <i class="fas fa-cog"></i> <!-- Font Awesome gear icon -->
                            <span>Customize</span>
                        </button>

                        <!-- Import Clients button with cloud upload icon -->
                        <button @click="showImportModal = true"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Import</span>
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
                </div>

                <div class="client-card-container {{ count($clients) === 1 || count($clients) % 3 === 1 ? 'single-card' : '' }}">
                    @foreach ($clients as $client)
                        <div x-data="{ showDeleteModal: false }" class="client-card bg-white shadow-md rounded-lg overflow-hidden flex flex-col w-full max-w-md">
                            <div class="px-6 py-4 flex-grow">
                                <div class="mb-2">
                                    <h2 class="text-xl font-bold text-gray-800">{{ $client->name }}</h2>
                                </div>
                                
                                <!-- Conditionally display the email if the user has enabled it in their settings -->
                                @if($settings->show_email)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Email:</strong> {{ $client->email ?? 'N/A' }}
                                </div>
                                @endif
                                
                                <!-- Conditionally display the address if the user has enabled it in their settings -->
                                @if($settings->show_address)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Address:</strong> {{ $client->address ?? 'N/A' }}
                                </div>
                                @endif
                                
                                <!-- Conditionally display the phone number if the user has enabled it in their settings -->
                                @if($settings->show_phone)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Phone:</strong> {{ $client->phone ?? 'N/A' }}
                                </div>
                                @endif
                                
                                <!-- Conditionally display CVR if the user has enabled it in their settings -->
                                @if($settings->show_cvr)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>CVR:</strong> {{ $client->cvr ?? 'N/A' }}
                                </div>
                                @endif
                                
                                <!-- Conditionally display the city if the user has enabled it in their settings -->
                                @if($settings->show_city)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>City:</strong> {{ $client->city ?? 'N/A' }}
                                </div>
                                @endif
                                
                                <!-- Conditionally display the zip code if the user has enabled it in their settings -->
                                @if($settings->show_zip_code)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Zip Code:</strong> {{ $client->zip_code ?? 'N/A' }}
                                </div>
                                @endif
                                
                                <!-- Conditionally display the country if the user has enabled it in their settings -->
                                @if($settings->show_country)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Country:</strong> {{ $client->country ?? 'N/A' }}
                                </div>
                                @endif
                                
                                <!-- Conditionally display notes if the user has enabled it in their settings -->
                                @if($settings->show_notes)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Notes:</strong> 
                                    @if($client->clientNotes->isNotEmpty())
                                        <ul>
                                            @foreach($client->clientNotes as $note)
                                                <li>
                                                    <div class="note-content">
                                                        <!-- Display only the first 100 characters initially -->
                                                        <span class="note-preview">
                                                            {{ Str::limit($note->content, 100) }}
                                                        </span>

                                                        <!-- Full note content hidden initially -->
                                                        <span class="note-full" style="display: none;">
                                                            {{ $note->content }}
                                                        </span>

                                                        <!-- Read more/less button -->
                                                        @if(strlen($note->content) > 100)
                                                            <a href="#" class="text-blue-500 read-more" onclick="event.preventDefault(); toggleNote(this);">Read more</a>
                                                        @endif

                                                        <!-- Show the date -->
                                                        <div class="text-gray-400 text-xs">
                                                            ({{ $note->updated_at->format('d M Y') }})
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        N/A
                                    @endif
                                </div>
                                @endif
                                
                                <!-- Conditionally display contact persons if the user has enabled it in their settings -->
                                @if($settings->show_contact_persons)
                                <div class="mb-2 text-sm text-gray-500">
                                    <strong>Contact Persons:</strong> 
                                    @if($client->contactPersons->isNotEmpty())
                                        <ul>
                                            @foreach($client->contactPersons as $contactPerson)
                                                <li>{{ $contactPerson->name }} ({{ $contactPerson->phone ?? 'Phone N/A' }})</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        N/A
                                    @endif
                                </div>
                                @endif
                                
                
                                <!-- Client Tags -->
                                @if ($client->tags->isNotEmpty())
                                <div class="tags-container mt-2">
                                    <p class="text-sm text-gray-600 mb-1">Tags:</p>
                                    <div class="flex flex-wrap mb-2">
                                        @foreach ($client->tags->take(5) as $tag)
                                        <span class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                    @if ($client->tags->count() > 5)
                                    <button id="moreTagsButton-{{ $client->id }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded mb-2" type="button" onclick="toggleTags({{ $client->id }}, event);">
                                        +{{ $client->tags->count() - 5 }}
                                    </button>
                                    <div style="display: none" id="hidden-tags-{{ $client->id }}" class="hidden-tags">
                                        @foreach ($client->tags->slice(5) as $tag)
                                        <span class="inline-block {{ $colorClasses[$tag->color] ?? 'bg-gray-200' }} rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                
                            <!-- Dropdown for Changing Status -->
                            <div class="px-6 py-2 border-t border-gray-200 flex items-center bg-gray-100">
                                <div class="status-dropdown flex-grow mr-4">
                                    <form method="POST" action="{{ route('clients.updateStatus', $client) }}">
                                        @csrf
                                        <select name="status" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" data-client-id="{{ $client->id }}">
                                            <option value="{{ Client::STATUS_LEAD }}" {{ $client->status == Client::STATUS_LEAD ? 'selected' : '' }}>Lead</option>
                                            <option value="{{ Client::STATUS_CONTACTED }}" {{ $client->status == Client::STATUS_CONTACTED ? 'selected' : '' }}>Contacted</option>
                                            <option value="{{ Client::STATUS_INTERESTED }}" {{ $client->status == Client::STATUS_INTERESTED ? 'selected' : '' }}>Interested</option>
                                            <option value="{{ Client::STATUS_NEGOTIATION }}" {{ $client->status == Client::STATUS_NEGOTIATION ? 'selected' : '' }}>Negotiation</option>
                                            <option value="{{ Client::STATUS_DEAL_MADE }}" {{ $client->status == Client::STATUS_DEAL_MADE ? 'selected' : '' }}>Deal Made</option>
                                        </select>
                                    </form>
                                </div>
                
                                <!-- Edit and Delete Buttons -->
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                                    <button @click="showDeleteModal = true" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                                </div>
                            </div>
                
                            <!-- Delete Confirmation Modal -->
                            <div x-show="showDeleteModal" class="fixed inset-0 flex items-center justify-center z-50">
                                <div class="bg-gray-800 bg-opacity-75 absolute inset-0" @click="showDeleteModal = false"></div>
                                <div class="bg-white p-6 rounded shadow-md z-10 max-w-md mx-auto">
                                    <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
                                    <p class="mb-6">Are you sure you want to delete this client? This action cannot be undone.</p>
                                    <div class="flex justify-end space-x-4">
                                        <button type="button" @click="showDeleteModal = false" class="bg-gray-300 hover:bg-gray-400 text-black py-2 px-4 rounded">Cancel</button>
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded">Confirm</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>                

                <!-- Import Clients Modal -->
                <div x-show="showImportModal" class="fixed inset-0 flex items-center justify-center z-50">
                    <div class="bg-gray-800 bg-opacity-75 absolute inset-0" @click="showImportModal = false"></div>
                    <div class="bg-white p-6 rounded shadow-md z-10 max-w-md mx-auto">
                        <h2 class="text-xl font-bold mb-4">Import Clients</h2>
                        <form id="importForm" action="{{ route('clients.import') }}" method="post" enctype="multipart/form-data" class="mb-6">
                            @csrf
                            <div class="form-group">
                                <!-- Tooltip for file structure instructions -->
                                <p class="text-gray-700 text-sm mb-2">
                                    To import a list of clients, please upload a CSV or a TXT file with the following columns:
                                    <span class="underline cursor-pointer" data-tooltip="The order should be: name, email, cvr, phone, address. The name column is required.">Correct Column Order</span>
                                </p>

                                <!-- Tooltip for accepted file types -->
                                <p>
                                    <strong>Accepted file types:</strong> 
                                    <span class="underline cursor-pointer" title="Only .csv and .txt formats are allowed.">.csv, .txt</span>
                                </p>

                                <!-- Tooltip for file size -->
                                <p>
                                    <strong>Max file size:</strong> 
                                    <span class="underline cursor-pointer" title="File should not exceed 2MB.">2MB</span>
                                </p>

                                <br>

                                <p>
                                    You can download a 
                                    <a href="{{ route('download-template') }}" class="text-blue-500 hover:underline" title="Download a CSV template file to ensure correct formatting.">template here</a>.
                                </p>

                                <br>

                                <!-- File Upload Section -->
                                <label for="file" class="block text-gray-700 text-sm font-bold mb-2">Upload file:</label>
                                <input type="file" name="file" id="file" accept=".csv,.txt"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <button type="submit" class="mt-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Submit
                            </button>
                        </form>

                        <div class="flex justify-end space-x-4">
                            <button type="button" @click="showImportModal = false" class="bg-gray-300 hover:bg-gray-400 text-black py-2 px-4 rounded">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Settings Modal -->
                <div x-show="showSettingsModal" class="fixed inset-0 flex items-center justify-center z-50">
                    <div class="bg-gray-800 bg-opacity-75 absolute inset-0" @click="showSettingsModal = false"></div>
                    <div class="bg-white p-6 rounded shadow-md z-10 max-w-md mx-auto">
                        <h2 class="text-xl font-bold mb-4">Customize Client Display</h2>
                        <!-- Form with POST action to the new route -->
                        <form id="settings-form" action="{{ route('clients.saveSettings') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <input type="hidden" name="show_email" value="0">
                                <input type="checkbox" id="show_email" name="show_email" value="1" 
                                       {{ $settings->show_email ? 'checked' : '' }}>
                                <label for="show_email" class="text-gray-700">Show Email</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_address" value="0">
                                <input type="checkbox" id="show_address" name="show_address" value="1" 
                                       {{ $settings->show_address ? 'checked' : '' }}>
                                <label for="show_address" class="text-gray-700">Show Address</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_phone" value="0">
                                <input type="checkbox" id="show_phone" name="show_phone" value="1" 
                                       {{ $settings->show_phone ? 'checked' : '' }}>
                                <label for="show_phone" class="text-gray-700">Show Phone</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_cvr" value="0">
                                <input type="checkbox" id="show_cvr" name="show_cvr" value="1" {{ $settings->show_cvr ? 'checked' : '' }}>
                                <label for="show_cvr" class="text-gray-700">Show CVR</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_city" value="0">	
                                <input type="checkbox" id="show_city" name="show_city"value="1" {{ $settings->show_city ? 'checked' : '' }}>
                                <label for="show_city" class="text-gray-700">Show City</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_zip_code" value="0">
                                <input type="checkbox" id="show_zip_code" name="show_zip_code"value="1" {{ $settings->show_zip_code ? 'checked' : '' }}>
                                <label for="show_zip_code" class="text-gray-700">Show Zip Code</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_country" value="0">
                                <input type="checkbox" id="show_country" name="show_country"value="1" {{ $settings->show_country ? 'checked' : '' }}>
                                <label for="show_country" class="text-gray-700">Show Country</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_notes" value="0">
                                <input type="checkbox" id="show_notes" name="show_notes"value="1" {{ $settings->show_notes ? 'checked' : '' }}>
                                <label for="show_notes" class="text-gray-700">Show Notes</label>
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="show_contact_persons" value="0">
                                <input type="checkbox" id="show_contact_persons" name="show_contact_persons"value="1" {{ $settings->show_contact_persons ? 'checked' : '' }}>
                                <label for="show_contact_persons" class="text-gray-700">Show Contact Persons</label>
                            </div>
                            <div class="flex justify-end space-x-4">
                                <!-- Submit button to save the settings -->
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded">Apply</button>
                                <button type="button" @click="showSettingsModal = false" class="bg-gray-300 hover:bg-gray-400 text-black py-2 px-4 rounded">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($clients->total() > 10)
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
                @endif
            </div>
        </div>
    </div>
</div>
</x-app-layout>

{{-- <script src="//unpkg.com/alpinejs" defer></script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss success alerts only after 5 seconds
        document.querySelectorAll('.alert-success').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300); // Remove after fade-out
            }, 5000); // 5-second display
        });

        // Warning/confirmation alerts remain visible until manually closed
        document.querySelectorAll('.alert-warning').forEach(alert => {
            alert.querySelector('button').addEventListener('click', () => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300); // Remove after fade-out
            });
        });

        // Tooltip initialization (if needed)
        function initializeTooltips() {
            const tooltipElements = document.querySelectorAll('[data-tooltip]');

            tooltipElements.forEach(function(el) {
                el.addEventListener('mouseenter', function() {
                    const tooltipText = el.getAttribute('data-tooltip');
                    const tooltipDiv = document.createElement('div');
                    tooltipDiv.classList.add('tooltip');
                    tooltipDiv.textContent = tooltipText;
                    document.body.appendChild(tooltipDiv);

                    const rect = el.getBoundingClientRect();
                    tooltipDiv.style.left = rect.left + 'px';
                    tooltipDiv.style.top = rect.top - tooltipDiv.offsetHeight - 10 + 'px';

                    el.addEventListener('mouseleave', function() {
                        tooltipDiv.remove();
                    });
                });
            });
        }

        initializeTooltips(); // Initialize tooltips on page load

    // Toggle Modals and reinitialize tooltips when a modal is shown
    function toggleModal(modalId, show) {
        const modal = document.getElementById(modalId);
        if (show) {
            modal.classList.remove('hidden');
            initializeTooltips(); // Reinitialize tooltips when modal opens
        } else {
            modal.classList.add('hidden');
        }
    }
});


    // Alpine.js data function for managing modals
    function data() {
        return {
            showImportModal: false,
            showSettingsModal: false
        };
    }

        // Toggle client details visibility on mobile
        document.querySelectorAll('.client-row').forEach(function(clientRow) {
            clientRow.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && event.target.classList.contains('client-name')) {
                    clientRow.querySelector('.client-details').classList.toggle('hidden');
                }
            });
        });

        // Prevent click events from propagating up to .client-row when a button or other interactive element is clicked
        document.querySelectorAll('.client-row .client-details, .client-row .client-details *').forEach(function(element) {
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
                    console.log('Status updated successfully', response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error updating status', textStatus, errorThrown);
                    console.error('Response Text:', jqXHR.responseText);
                }
            });
        });

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
                    const newHiddenInput = document.createElement('input');
                    newHiddenInput.type = 'hidden';
                    newHiddenInput.name = 'search[]';
                    newHiddenInput.value = newSearch;
                    searchForm.appendChild(newHiddenInput);
                }
            }

            searchForm.submit();
        });

        // Handle radio buttons for search
        let radios = document.querySelectorAll('input[name="status"]');

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                let searchTags = Array.from(document.querySelectorAll('input[name="search[]"]'))
                    .map(input => input.value);

                searchTags = searchTags.filter(tag => !tag.startsWith('status:'));

                if (this.value) {
                    searchTags.push('status:' + this.value);
                }

                document.querySelectorAll('input[name="search[]"]').forEach(input => input.remove());

                searchTags.forEach(tag => {
                    const newHiddenInput = document.createElement('input');
                    newHiddenInput.type = 'hidden';
                    newHiddenInput.name = 'search[]';
                    newHiddenInput.value = tag;
                    searchForm.appendChild(newHiddenInput);
                });

                searchForm.submit();
            });
        });

        // To toggle the modals (both settings and delete)
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }

        function toggleNote(element) {
            var noteContent = element.previousElementSibling; // Full note
            var notePreview = noteContent.previousElementSibling; // Preview note

            if (noteContent.style.display === "none") {
                // Show full note and change the link to "Read less"
                noteContent.style.display = "inline";
                notePreview.style.display = "none";
                element.innerHTML = "Read less";
            } else {
                // Show only the preview and change the link to "Read more"
                noteContent.style.display = "none";
                notePreview.style.display = "inline";
                element.innerHTML = "Read more";
            }
        }

        // Toggle the settings modal
        const settingsButton = document.getElementById('settingsButton');
        if (settingsButton) {
            settingsButton.addEventListener('click', function() {
                toggleModal('settingsModal', true);
            });
        }

        // Toggle the delete modal
        document.querySelectorAll('[data-delete-id]').forEach(button => {
            button.addEventListener('click', function() {
                toggleModal('deleteModal', true);
            });
        });

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
</script>
