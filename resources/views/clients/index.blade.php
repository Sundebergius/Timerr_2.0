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
                    <form method="GET" action="{{ route('clients.index') }}">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Search clients" name="search">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <!-- Add new client form -->
                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf
                    <!-- Add new client button -->
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">Add Client</a>
                        {{-- <!-- Form fields for client details -->
                        <button type="submit" class="btn btn-primary">Add Client</button> --}}
                    </form>

                    <!-- Client table -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Contact Details</th>
                                <th scope="col">Status</th>
                                {{-- <th scope="col">Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->contact_details }}</td>
                                    <td>{{ $client->status }}</td>
                                    <td>
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-info">View</a>
                                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">Edit</a>
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>