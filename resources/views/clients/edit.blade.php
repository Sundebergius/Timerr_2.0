<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Client
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('clients.update', $client) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                            <input type="text" id="name" name="name" value="{{ $client->name }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
        
                        <div class="mb-4">
                            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                            <select id="status" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="{{ $statuses['lead'] }}" {{ $client->status == $statuses['lead'] ? 'selected' : '' }}>Lead</option>
                                <option value="{{ $statuses['contacted'] }}" {{ $client->status == $statuses['contacted'] ? 'selected' : '' }}>Contacted</option>
                                <option value="{{ $statuses['interested'] }}" {{ $client->status == $statuses['interested'] ? 'selected' : '' }}>Interested</option>
                                <option value="{{ $statuses['negotiation'] }}" {{ $client->status == $statuses['negotiation'] ? 'selected' : '' }}>Negotiation</option>
                                <option value="{{ $statuses['deal_made'] }}" {{ $client->status == $statuses['deal_made'] ? 'selected' : '' }}>Deal Made</option>
                            </select>
                        </div>
        
                        <div class="mb-4">
                            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                            <input type="text" id="phone" name="phone" value="{{ $client->phone }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
        
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="text" id="email" name="email" value="{{ $client->email }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
        
                        <div class="mb-4">
                            <label for="tags" class="block text-gray-700 text-sm font-bold mb-2">Tags</label>
                            <input type="text" id="tag-input" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <select id="tag-color">
                                <option value="red">Red</option>
                                <option value="blue">Blue</option>
                                <option value="green">Green</option>
                            </select>
                            <button id="add-tag" type="button">Add tag</button>
                        
                            <div id="tags-container">
                                <!-- Tags will be dynamically added here -->
                            </div>
                        
                            <!-- Hidden form fields to actually submit the tags -->
                            <div id="hidden-fields">
                                @if($client->tags)
                                    @foreach($client->tags as $index => $tag)
                                        <input type="hidden" name="tags[]" value="{{ $tag }}">
                                        <input type="hidden" name="tag_colors[]" value="{{ $client->tag_colors[$index] }}">
                                    @endforeach
                                @endif
                            </div>
                        </div>
        
                        <div class="mb-4">
                            <label for="categories" class="block text-gray-700 text-sm font-bold mb-2">Categories</label>
                            <input type="text" id="categories" name="categories" value="{{ $client->categories }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
        
                        <div class="mb-4">
                            <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Notes</label>
                            <textarea id="notes" name="notes" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ $client->notes }}</textarea>
                        </div>

                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.getElementById('add-tag').addEventListener('click', function() {
        var tagInput = document.getElementById('tag-input');
        var tagColor = document.getElementById('tag-color');
        var tagsContainer = document.getElementById('tags-container');
        var hiddenFields = document.getElementById('hidden-fields');

        // Create the tag element
        var tagElement = document.createElement('span');
        tagElement.textContent = tagInput.value;
        tagElement.style.backgroundColor = tagColor.value;
        tagsContainer.appendChild(tagElement);

        // Create hidden fields to actually submit the tag
        var tagField = document.createElement('input');
        tagField.type = 'hidden';
        tagField.name = 'tags[]';
        tagField.value = tagInput.value;
        hiddenFields.appendChild(tagField);

        var colorField = document.createElement('input');
        colorField.type = 'hidden';
        colorField.name = 'tag_colors[]';
        colorField.value = tagColor.value;
        hiddenFields.appendChild(colorField);

        // Clear the input
        tagInput.value = '';
    });
</script>