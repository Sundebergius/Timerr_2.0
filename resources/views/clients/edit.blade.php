<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Edit Client') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <form method="POST" action="{{ route('clients.update', $client) }}" id="clientUpdateForm" class="space-y-12">
                        @csrf
                        @method('PUT')
                        
                        <!-- Client Information Section -->
                        <div class="space-y-6 bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-xl font-semibold text-gray-800">
                                {{ __('Client Information') }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Update the client's information in the form below.
                            </p>

                            <!-- Name Field -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                <label for="name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    {{ __('Name') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" id="name" name="name" value="{{ old('name', $client->name) }}" 
                                        class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                </div>
                            </div>

                            <!-- Email Field -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                <label for="email" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    {{ __('Email') }}
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" id="email" name="email" value="{{ old('email', $client->email) }}"
                                        class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                    @if ($errors->has('email'))
                                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('email') }}</p>
                                    @endif
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

                            <!-- Country -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                <label for="country" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    {{ __('Country') }}
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" id="country" name="country" value="{{ old('country', $client->country) }}" 
                                        class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        <!-- Button to Add New Notes -->
                        <div class="mt-8 text-center" x-data="{ notes: [] }">
                            <button type="button" @click.prevent="notes.push('')" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition focus:outline-none">
                                + Add New Note
                            </button>

                            <!-- Displaying the notes input fields -->
                            <template x-for="(note, index) in notes" :key="index">
                                <div class="mt-6 max-w-3xl mx-auto">
                                    <textarea :name="'new_client_notes[' + index + ']'" x-model="notes[index]" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md p-2"></textarea>
                                    <!-- Character counter for each note -->
                                    <p class="text-sm text-gray-500 mt-1">
                                        <span x-text="notes[index].length"></span> / 10000 characters used
                                    </p>
                                    <!-- Remove Note Button -->
                                    <button type="button" @click.prevent="notes.splice(index, 1)" class="text-red-500 hover:underline mt-2">
                                        Remove Note
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Button to Add New Contact Person -->
                        <div class="mt-8 text-center" x-data="{ contactPersons: [] }">
                            <button type="button" @click.prevent="contactPersons.push({ name: '', email: '', phone: '', notes: '' })" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition focus:outline-none">
                                + Add New Contact Person
                            </button>

                            <!-- Displaying the contact person input fields -->
                            <template x-for="(person, index) in contactPersons" :key="index">
                                <div class="mt-6 max-w-3xl mx-auto">
                                    <input type="text" :name="'new_contact_persons[' + index + '][name]'" x-model="person.name" placeholder="Name" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md mb-2 p-2">
                                    <input type="email" :name="'new_contact_persons[' + index + '][email]'" x-model="person.email" placeholder="Email" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md mb-2 p-2">
                                    <input type="text" :name="'new_contact_persons[' + index + '][phone]'" x-model="person.phone" placeholder="Phone" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md mb-2 p-2">
                                    
                                    <!-- Notes field with character counter -->
                                    <textarea :name="'new_contact_persons[' + index + '][notes]'" x-model="person.notes" placeholder="Notes" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md p-2"></textarea>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <span x-text="person.notes.length"></span> / <span x-text="person.maxLength"></span> 5000 characters used
                                    </p>

                                    <!-- Remove Contact Person Button -->
                                    <button type="button" @click.prevent="contactPersons.splice(index, 1)" class="text-red-500 hover:underline mt-2">
                                        Remove Contact Person
                                    </button>
                                </div>
                            </template>
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

                        <!-- Submit Button -->
                        <div class="pt-10 pb-6">
                            <div class="flex justify-start">
                                <button type="submit" class="inline-flex justify-center py-3 px-8 border border-transparent shadow-lg text-sm font-semibold rounded-lg text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-300 ease-in-out">
                                    {{ __('Update Client') }}
                                </button>
                            </div>
                        </div>
                    </form>

                        <!-- Notes Section -->
                        <div class="mb-16 pb-12 border-b border-gray-300">
                            <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Notes</h2>

                            <!-- Display Existing Notes -->
                            <div class="space-y-6">
                                @foreach ($client->clientNotes as $index => $note)
                                    <div class="max-w-3xl mx-auto p-4 bg-white border border-gray-300 rounded-lg shadow-sm hover:shadow-md transition-shadow" 
                                        x-data="{ isEditing: false, noteContent: '{{ $note->content }}', saving: false, showSuccess: false }">
                                        
                                        <!-- Success Message -->
                                        <div x-show="showSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert" x-cloak>
                                            <strong class="font-bold">Success!</strong>
                                            <span class="block sm:inline">Note updated successfully.</span>
                                            <span @click.prevent="showSuccess = false" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path d="M14.348 5.652a1 1 0 010 1.414L11.414 10l2.934 2.934a1 1 0 11-1.414 1.414L10 11.414l-2.934 2.934a1 1 0 01-1.414-1.414L8.586 10 5.652 7.066a1 1 0 011.414-1.414L10 8.586l2.934-2.934a1 1 0 011.414 0z"/>
                                                </svg>
                                            </span>
                                        </div>

                                        <!-- Note Header -->
                                        <div class="flex justify-between items-center mb-3">
                                            <p class="text-lg font-semibold text-gray-700">Note {{ $loop->iteration }}</p>
                                            <div class="flex space-x-2">
                                                <!-- Edit Button -->
                                                <button type="button" @click.prevent="isEditing = !isEditing" class="text-blue-500 hover:text-blue-700">
                                                    <x-heroicon-s-pencil class="w-5 h-5"/>
                                                </button>

                                                <!-- Delete Button -->
                                                <form action="{{ route('clients.notes.destroy', [$client, $note]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this note?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                                        <x-heroicon-s-trash class="w-5 h-5"/>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Note Content Preview and Edit Form -->
                                        <div>
                                            <!-- Display Note when not editing -->
                                            <div x-show="!isEditing">
                                                <p x-ref="noteDisplay" class="text-gray-600">{{ \Illuminate\Support\Str::limit($note->content, 100, '...') }}</p>
                                            </div>

                                            <!-- Note Editing Form -->
                                            <div x-show="isEditing" class="space-y-4">
                                                <textarea x-model="noteContent" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md p-2"></textarea>
                                                
                                                <!-- Character counter -->
                                                <p class="text-sm text-gray-500">
                                                    <span x-text="noteContent.length"></span> / 10000 characters used
                                                </p>

                                                <!-- Save and Cancel Buttons -->
                                                <div class="flex space-x-2">
                                                    <button type="button" 
                                                        @click.prevent="saveNote('{{ route('clients.notes.update', ['client' => $client->id, 'note' => $note->id]) }}', noteContent, $data)"
                                                        class="bg-green-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-600 transition">
                                                        Save Note
                                                    </button>

                                                    <button type="button" @click.prevent="isEditing = false" class="bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-400 transition">
                                                        Cancel
                                                    </button>
                                                </div>

                                                <!-- Loading Indicator -->
                                                <div x-show="saving" class="text-blue-500">Saving...</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            
                        </div>

                        <!-- Contact Persons Section -->
                        <div class="mb-16 pt-12">
                            <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Contact Persons</h2>

                            <!-- Display Existing Contact Persons -->
                            <div class="space-y-6">
                                @foreach ($client->contactPersons as $person)
                                    <div class="max-w-3xl mx-auto p-4 bg-white border border-gray-300 rounded-lg shadow-sm hover:shadow-md transition-shadow" 
                                        x-data="{ isEditing: false, name: '{{ $person->name }}', email: '{{ $person->email }}', phone: '{{ $person->phone }}', notes: '{{ $person->notes }}', saving: false, showSuccess: false }">

                                        <!-- Success Message -->
                                        <div x-show="showSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert" x-cloak>
                                            <strong class="font-bold">Success!</strong>
                                            <span class="block sm:inline">Contact person updated successfully.</span>
                                            <span @click.prevent="showSuccess = false" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path d="M14.348 5.652a1 1 0 010 1.414L11.414 10l2.934 2.934a1 1 0 11-1.414 1.414L10 11.414l-2.934 2.934a1 1 0 01-1.414-1.414L8.586 10 5.652 7.066a1 1 0 011.414-1.414L10 8.586l2.934-2.934a1 1 0 011.414 0z"/>
                                                </svg>
                                            </span>
                                        </div>

                                        <div class="flex justify-between items-center mb-3">
                                            <p x-ref="nameDisplay" class="text-lg font-semibold text-gray-700">{{ $person->name }}</p> <!-- Added x-ref here -->
                                            <div class="flex space-x-2">
                                                <!-- Edit Button -->
                                                <button type="button" @click.prevent="isEditing = !isEditing" class="text-blue-500 hover:text-blue-700">
                                                    <x-heroicon-s-pencil class="w-5 h-5" />
                                                </button>

                                                <!-- Delete Button -->
                                                <form action="{{ route('clients.contact-persons.destroy', [$client, $person]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this contact person?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                                        <x-heroicon-s-trash class="w-5 h-5"/>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Contact Person Details and Edit Form -->
                                        <div>
                                            <!-- Display Contact Person when not editing -->
                                            <div x-show="!isEditing">
                                                <p x-ref="emailDisplay" class="text-gray-600 mb-1">Email: {{ $person->email ?? 'N/A' }}</p>
                                                <p x-ref="phoneDisplay" class="text-gray-600 mb-1">Phone: {{ $person->phone ?? 'N/A' }}</p>
                                                <p x-ref="notesDisplay" class="text-gray-600 mb-1">Notes: {{ $person->notes ?? 'N/A' }}</p>
                                            </div>

                                            <!-- Contact Person Editing Form -->
                                            <div x-show="isEditing" class="space-y-4">
                                                <input type="text" x-model="name" placeholder="Name" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md p-2">
                                                <input type="email" x-model="email" placeholder="Email" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md p-2">
                                                <input type="text" x-model="phone" placeholder="Phone" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md p-2">
                                                
                                                <!-- Notes field with character counter -->
                                                <textarea x-model="notes" placeholder="Notes" class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md p-2"></textarea>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    <span x-text="notes.length"></span> / 5000 characters used
                                                </p>
                                                
                                                <!-- Save and Cancel Buttons -->
                                                <div class="flex space-x-2">
                                                    <button type="button" 
                                                        @click.prevent="saveContactPerson('{{ route('clients.contact-persons.update', ['client' => $client->id, 'contactPerson' => $person->id]) }}', { name, email, phone, notes }, $data)" 
                                                        class="bg-green-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-600 transition">
                                                        Save Contact
                                                    </button>

                                                    <button type="button" @click.prevent="isEditing = false" class="bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-400 transition">
                                                        Cancel
                                                    </button>
                                                </div>

                                                <!-- Loading Indicator -->
                                                <div x-show="saving" class="text-blue-500">Saving...</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        

                        
                </div>
            </div>
        </div>
    </div>
    <script>
        // Ensure the document is fully loaded before running the script
        document.addEventListener('DOMContentLoaded', function () {
            // Target the specific client update form using a unique selector or ID
            var form = document.getElementById('clientUpdateForm');
            
            form.addEventListener('submit', function(event) {
                // Name Validation
                var nameInput = document.getElementById('name');
                var nameValue = nameInput.value;

                // Email Validation
                var emailInput = document.getElementById('email');
                var emailValue = emailInput.value;

                // Clear any previous error messages
                document.querySelectorAll('.error-msg').forEach(function(errorElement) {
                    errorElement.remove(); // Remove existing error messages
                });

                var hasErrors = false; // Track if there are validation errors

                // Name validation: Ensure the name is provided
                if (!nameValue) {
                    event.preventDefault(); // Stop form submission
                    var nameErrorMsg = document.createElement('p');
                    nameErrorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-msg');
                    nameErrorMsg.textContent = 'The name field is required.';
                    nameInput.parentElement.appendChild(nameErrorMsg);
                    hasErrors = true;
                }

                // Email validation: Only if the email field is filled, check if it's a valid format
                var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
                if (emailValue && !emailPattern.test(emailValue)) {
                    event.preventDefault(); // Stop form submission
                    var emailErrorMsg = document.createElement('p');
                    emailErrorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-msg');
                    emailErrorMsg.textContent = 'Please enter a valid email address.';
                    emailInput.parentElement.appendChild(emailErrorMsg);
                    hasErrors = true;
                }

                // If there are no errors, allow the form to submit
                if (!hasErrors) {
                    form.submit(); // Proceed with form submission
                }
            });
        });
    
        function saveNote(updateUrl, content, alpineComponent) {
            // Ensure alpineComponent is defined and accessible
            if (!alpineComponent) {
                console.error('Alpine component is undefined.');
                return;
            }

            alpineComponent.saving = true;  // Set the saving state

            fetch(updateUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),  // Include CSRF token
                },
                body: JSON.stringify({ content: content }),  // Send the updated content
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alpineComponent.saving = false;
                    alpineComponent.isEditing = false;  // Exit editing mode
                    alpineComponent.showSuccess = true;  // Show success message

                    // Update the displayed content immediately
                    alpineComponent.$refs.noteDisplay.innerText = content;

                    // Hide the success message after a few seconds
                    setTimeout(() => alpineComponent.showSuccess = false, 3000);
                } else {
                    alert('Error updating the note.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function saveContactPerson(updateUrl, data, alpineComponent) {
            // Ensure alpineComponent is defined and accessible
            if (!alpineComponent) {
                console.error('Alpine component is undefined.');
                return;
            }

            alpineComponent.saving = true;  // Set the saving state

            fetch(updateUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),  // Include CSRF token
                },
                body: JSON.stringify(data),  // Send the updated contact person details
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alpineComponent.saving = false;
                    alpineComponent.isEditing = false;  // Exit edit mode
                    alpineComponent.showSuccess = true;  // Show success message

                    // Update the displayed content immediately
                    alpineComponent.$refs.nameDisplay.innerText = data.contact.name || 'N/A';
                    alpineComponent.$refs.emailDisplay.innerText = data.contact.email || 'N/A';
                    alpineComponent.$refs.phoneDisplay.innerText = data.contact.phone || 'N/A';
                    alpineComponent.$refs.notesDisplay.innerText = data.contact.notes || 'N/A';

                    // Hide the success message after a few seconds
                    setTimeout(() => alpineComponent.showSuccess = false, 3000);
                } else {
                    alert('Error updating the contact person.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
  
        // Existing toggleReadMore functions
        function toggleReadMore(noteId) {
            var fullContent = document.getElementById('fullContent' + noteId);
            var toggleButton = document.getElementById('toggleButton' + noteId);
            if (fullContent.style.display === 'none' || fullContent.style.display === '') {
                fullContent.style.display = 'block';
                toggleButton.innerText = 'Read Less';
            } else {
                fullContent.style.display = 'none';
                toggleButton.innerText = 'Read More';
            }
        }
    
        function toggleReadMore(sectionId, itemId) {
            var fullContent = document.getElementById(sectionId + itemId);
            var toggleButton = document.getElementById('toggleContactButton' + itemId);
            if (fullContent.style.display === 'none' || fullContent.style.display === '') {
                fullContent.style.display = 'block';
                toggleButton.innerText = 'Read Less';
            } else {
                fullContent.style.display = 'none';
                toggleButton.innerText = 'Read More';
            }
        }
    </script>    
</x-app-layout>