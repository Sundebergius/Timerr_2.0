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
                    <form method="POST" action="{{ route('clients.update', $client) }}"
                        class="space-y-8 divide-y divide-gray-200">
                        @csrf
                        @method('PUT')
    
                        <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Client Information
                                </h3>
                            </div>
                            <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                                <!-- Name -->
                                <div
                                    class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Name
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="name" name="name" value="{{ $client->name }}"
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
    
                                <!-- Status -->
                                <div
                                    class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="status" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Status
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <select id="status" name="status"
                                            class="max-w-lg block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:max-w-xs sm:text-sm border-gray-300 rounded-md">
                                            <option value="{{ $statuses['lead'] }}"
                                                {{ $client->status == $statuses['lead'] ? 'selected' : '' }}>Lead</option>
                                            <option value="{{ $statuses['contacted'] }}"
                                                {{ $client->status == $statuses['contacted'] ? 'selected' : '' }}>Contacted
                                            </option>
                                            <option value="{{ $statuses['interested'] }}"
                                                {{ $client->status == $statuses['interested'] ? 'selected' : '' }}>
                                                Interested</option>
                                            <option value="{{ $statuses['negotiation'] }}"
                                                {{ $client->status == $statuses['negotiation'] ? 'selected' : '' }}>
                                                Negotiation</option>
                                            <option value="{{ $statuses['deal_made'] }}"
                                                {{ $client->status == $statuses['deal_made'] ? 'selected' : '' }}>Deal Made
                                            </option>
                                        </select>
                                    </div>
                                </div>
    
                                <!-- Phone -->
                                <div
                                    class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="phone" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Phone
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="phone" name="phone" value="{{ $client->phone }}"
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
    
                                <!-- Email -->
                                <div
                                    class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="email" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Email
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="email" name="email" value="{{ $client->email }}"
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
    
                                <!-- Tags -->
                                <div
                                    class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="tags" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Tags
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <div class="flex items-center">
                                            <input type="text" id="tag-input"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            <select id="tag-color"
                                                class="ml-3 block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:max-w-xs sm:text-sm border-gray-300 rounded-md">
                                                <option value="red">Red</option>
                                                <option value="blue">Blue</option>
                                                <option value="green">Green</option>
                                            </select>
                                            <button id="add-tag" type="button"
                                                class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Add
                                                tag</button>
                                        </div>
                                        <div id="tags-container" class="mt-2 space-y-1">
                                            <!-- Tags will be dynamically added here -->
                                            @foreach ($client->tags as $index => $tag)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $client->tag_colors[$index] }}-100 text-{{ $client->tag_colors[$index] }}-800">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                        <!-- Hidden form fields to actually submit the tags -->
                                        <div id="hidden-fields">
                                            @if ($client->tags)
                                                @foreach ($client->tags as $index => $tag)
                                                    <input type="hidden" name="tags[]" value="{{ $tag }}">
                                                    <input type="hidden" name="tag_colors[]"
                                                        value="{{ $client->tag_colors[$index] }}">
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Categories -->
                                <div
                                    class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="categories"
                                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Categories
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="categories" name="categories"
                                            value="{{ $client->categories }}"
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
    
                                <!-- Notes -->
                                <div
                                    class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Notes
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <textarea id="notes" name="notes"
                                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md">{{ $client->notes }}</textarea>
                                    </div>
                                </div>
    
                                <div class="pt-5">
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Update
                                        </button>
                                    </div>
                                </div>
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
    