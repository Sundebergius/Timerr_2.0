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
                    <!-- Search form -->
                    <form method="GET" action="{{ route('clients.index') }}" class="mb-3 flex">
                        <input type="text" class="form-input flex-grow mr-3" placeholder="Search clients" name="search[]">
                        @if(request('search'))
                            @foreach(request('search') as $search)
                                <input type="hidden" name="search[]" value="{{ $search }}">
                            @endforeach
                        @endif
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </form>

                    <!-- Search tags -->
                    <div class="tags flex flex-wrap">
                        @if(request('search') && count(request('search')) > 0)
                            @foreach(request('search') as $search)
                                @if($search != '')
                                    <span class="tag bg-gray-400 text-white px-2 py-1 m-1 rounded">
                                        {{ $search }}
                                        <a href="{{ url('clients') }}?search[]={{ implode('&search[]=', array_diff(request('search'), [$search])) }}" class="ml-1 text-white">x</a>
                                    </span>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <!-- Add new client form -->
                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf
                        <!-- Add new client button -->
                        <a href="{{ route('clients.create') }}" class="btn btn-primary">Add Client</a>
                    </form>

                    <!-- Client table -->
                    <table class="table-auto w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">
                                    <a href="{{ route('clients.index', ['sortField' => 'name', 'sortDirection' => request('sortField') === 'name' && request('sortDirection') === 'asc' ? 'desc' : 'asc']) }}">
                                        Name
                                        @if(request('sortField') === 'name')
                                            <i class="fas fa-sort-{{ request('sortDirection') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-2">Contact Details</th>
                                <th class="px-4 py-2">
                                    <a href="{{ route('clients.index', ['sortField' => 'status', 'sortDirection' => request('sortField') === 'status' && request('sortDirection') === 'asc' ? 'desc' : 'asc']) }}">
                                        Status
                                        @if(request('sortField') === 'status')
                                            <i class="fas fa-sort-{{ request('sortDirection') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                            <tr>
                                <td class="border px-4 py-2">{{ $client->name }}</td>
                                <td class="border px-4 py-2">
                                    @if($client->phone)
                                    <strong>Phone Number:</strong>  {{ $client->phone }}<br>
                                    @endif
                                    @if($client->email)
                                    <strong>Email:</strong>  {{ $client->email }}
                                    @endif
                                </td>
                                    <td class="border px-4 py-2">
                                        {{ $client->status }}
                                        <select name="status" class="form-select block w-full mt-1" data-client-id="{{ $client->id }}">
                                            <option value="{{ Client::STATUS_LEAD }}" {{ $client->status == Client::STATUS_LEAD ? 'selected' : '' }}>Lead</option>
                                            <option value="{{ Client::STATUS_CONTACTED }}" {{ $client->status == Client::STATUS_CONTACTED ? 'selected' : '' }}>Contacted</option>
                                            <option value="{{ Client::STATUS_INTERESTED }}" {{ $client->status == Client::STATUS_INTERESTED ? 'selected' : '' }}>Interested</option>
                                            <option value="{{ Client::STATUS_NEGOTIATION }}" {{ $client->status == Client::STATUS_NEGOTIATION ? 'selected' : '' }}>Negotiation</option>
                                            <option value="{{ Client::STATUS_DEAL_MADE }}" {{ $client->status == Client::STATUS_DEAL_MADE ? 'selected' : '' }}>Deal Made</option>
                                        </select>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                            <a href="{{ route('clients.show', $client) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">View</a>
                                            <a href="{{ route('clients.edit', $client) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                                            <form method="POST" action="{{ route('clients.destroy', $client) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-block bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to delete this item?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
});
    </script>
</x-app-layout>