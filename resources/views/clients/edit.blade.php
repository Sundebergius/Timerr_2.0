<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Edit Client') }}
            </h2>
        </div>
        @vite('resources/js/app.js')
        <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-8 divide-y divide-gray-300">
                        @csrf
                        @method('PUT')
                        
                        <!-- Client Information Section -->
                        <div class="pt-6 space-y-8 divide-y divide-gray-200 sm:space-y-5">
                            <div class="pb-6"> <!-- Add bottom padding here -->
                                <h3 class="text-xl font-semibold text-gray-800">
                                    {{ __('Client Information') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Update the client's information in the form below.
                                </p>
                            </div>
                            <!-- Add extra padding-top to ensure spacing between dividing line and the name field -->
                            <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5 pt-6"> <!-- Add pt-6 to create space -->
                                <!-- Name Field with Required Validation -->
                                <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5 pt-6">
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                        <label for="name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                            {{ __('Name') }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                                            <input type="text" id="name" name="name" value="{{ old('name', $client->name) }}" 
                                                class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Field with Error Display -->
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                    <label for="email" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        {{ __('Email') }}
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="email" name="email" value="{{ old('email', $client->email) }}"
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                        
                                        <!-- Check for email validation errors -->
                                        @if ($errors->has('email'))
                                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('email') }}</p>
                                        @endif
                                        
                                        <p class="text-gray-600 text-sm mt-1">Optional</p>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                    <label for="status" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        {{ __('Status') }}
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <select id="status" name="status" class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                            <option value="lead" {{ $client->status == 'lead' ? 'selected' : '' }}>Lead</option>
                                            <option value="contacted" {{ $client->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                            <option value="interested" {{ $client->status == 'interested' ? 'selected' : '' }}>Interested</option>
                                            <option value="negotiation" {{ $client->status == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                                            <option value="deal_made" {{ $client->status == 'deal_made' ? 'selected' : '' }}>Deal Made</option>
                                        </select>
                                    </div>
                                </div>
    
                                <!-- Phone -->
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                    <label for="phone" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        {{ __('Phone') }}
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="phone" name="phone" value="{{ old('phone', $client->phone) }}" 
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                    <label for="address" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        {{ __('Address') }}
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="address" name="address" value="{{ old('address', $client->address) }}" 
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <!-- City -->
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                    <label for="city" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        {{ __('City') }}
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="city" name="city" value="{{ old('city', $client->city) }}" 
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <!-- Zip Code -->
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                    <label for="zip_code" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        {{ __('Zip Code') }}
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code', $client->zip_code) }}" 
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                    </div>
                                </div>
    
                                <!-- Tags - currently innactive  -->
                                {{-- <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                    <label for="tags" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        Tags
                                    </label>
                                        <div id="app">
                                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                                <div class="flex flex-col sm:flex-row items-center">
                                                    <tag-editor client="{{ json_encode($client) }}"></tag-editor>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                    
                                        <!-- Hidden form fields to actually submit the tags -->
                                        {{-- <div id="hidden-fields">
                                            @if ($client->tags)
                                                @foreach ($client->tags as $tag)
                                                    <input type="hidden" name="tags[]" :value="{{ $tag->name }}">
                                                    <input type="hidden" name="tag_colors[]" :value="{{ $tag->color }}">
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div> --}}

                                <!-- Notes -->
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                    <label for="client_notes" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                        {{ __('Notes') }}
                                    </label>
                                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                                        <textarea id="client_notes" name="client_notes"
                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs lg:max-w-lg xl:max-w-xl border-gray-300 rounded-md">{{ optional($client->clientNote)->content }}</textarea>
                                    </div>
                                </div>
    
                                <!-- Submit Button -->
                        <div class="pt-5">
                            <div class="flex justify-start"> <!-- Updated from 'justify-end' to 'justify-start' -->
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            // Name Validation
            var nameInput = document.getElementById('name');
            var nameValue = nameInput.value;
    
            // Email Validation
            var emailInput = document.getElementById('email');
            var emailValue = emailInput.value;
    
            // Clear any previous error messages
            document.querySelectorAll('.error-msg').forEach(function(errorElement) {
                errorElement.remove();
            });
    
            // Name validation: Ensure the name is provided
            if (!nameValue) {
                event.preventDefault(); // Stop form submission
                var nameErrorMsg = document.createElement('p');
                nameErrorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-msg');
                nameErrorMsg.textContent = 'The name field is required.';
                nameInput.parentElement.appendChild(nameErrorMsg);
            }
    
            // Email validation: Only if the email field is filled, check if it's a valid format
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (emailValue && !emailPattern.test(emailValue)) {
                event.preventDefault(); // Stop form submission
                var emailErrorMsg = document.createElement('p');
                emailErrorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-msg');
                emailErrorMsg.textContent = 'Please enter a valid email address.';
                emailInput.parentElement.appendChild(emailErrorMsg);
            }
        });
    </script>
</x-app-layout>