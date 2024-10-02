<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <!-- Form Starts Here -->
                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf

                        @if ($errors->has('name'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('name') }}</p>
                        @endif


                        <!-- Client Type -->
                        <div class="mb-6">
                            <label for="client_type" class="block text-lg font-bold text-gray-700 mb-2">
                                Client Type <span class="text-red-500">*</span>
                            </label>
                            <select name="client_type" id="client_type" onchange="clientTypeChange(this)" class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="individual">Individual 🧑</option>
                                <option value="company">Company 🏢</option>
                            </select>
                        </div>
                        
                        
                        <p class="text-gray-600 mb-4">Fields marked with <span class="text-red-500">*</span> are required.</p>

                        <!-- Client Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-lg font-bold text-gray-700 mb-2">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-lg font-bold text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-gray-600 text-sm mt-1">Optional</p>
                            </div>
                        </div>

                        <!-- Conditionally Show Company Fields -->
                        <div id="company_info" class="hidden mb-6">
                            <label for="cvr" class="block text-lg font-bold text-gray-700 mb-2">CVR</label>
                            <input type="text" name="cvr" id="cvr" value="{{ old('cvr') }}" class="w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-gray-600 text-sm mt-1">Optional</p>
                        </div>

                        <div class="mb-6">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg flex items-center focus:outline-none">
                                {{ __('Create Client') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Ensure spacing between the Submit button and the expandable sections -->
                        <div class="space-y-6">
                            <!-- Buttons for Contact Person and Additional Information -->
                            <div class="flex space-x-6">
                                <!-- Expandable "Add Contact Person" Section -->
                                <div x-data="{ open: false, contactPersons: [] }" class="w-1/2">
                                    <button type="button" @click.prevent="open = !open" class="bg-blue-500 text-white font-bold py-2 px-4 w-full rounded-lg hover:bg-blue-700 transition focus:outline-none">
                                        Add Contact Person
                                        <i class="fas fa-chevron-down" x-show="!open"></i>
                                        <i class="fas fa-chevron-up" x-show="open"></i>
                                    </button>
                                    <div x-show="open" class="mt-4">
                                        <template x-for="(contact, index) in contactPersons" :key="index">
                                            <div class="mb-4 border rounded-lg p-4 bg-gray-100">
                                                <div class="flex justify-between">
                                                    <h4 class="text-lg font-bold">Contact Person <span x-text="index + 1"></span></h4>
                                                    <button type="button" @click="contactPersons.splice(index, 1)" class="text-red-500 hover:underline">Remove</button>
                                                </div>
                                                <div class="mt-2">
                                                    <label for="contact_name" class="block text-sm font-bold mb-2">Contact Name</label>
                                                    <input type="text" :name="'contact_persons[' + index + '][name]'" x-model="contact.name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                <div class="mt-2">
                                                    <label for="contact_email" class="block text-sm font-bold mb-2">Contact Email</label>
                                                    <input type="email" :name="'contact_persons[' + index + '][email]'" x-model="contact.email" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                <div class="mt-2">
                                                    <label for="contact_phone" class="block text-sm font-bold mb-2">Contact Phone</label>
                                                    <input type="text" :name="'contact_persons[' + index + '][phone]'" x-model="contact.phone" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                <div class="mt-2">
                                                    <label for="contact_notes" class="block text-sm font-bold mb-2">Notes</label>
                                                    <textarea :name="'contact_persons[' + index + '][notes]'" x-model="contact.notes" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                                </div>
                                            </div>
                                        </template>
                                        <!-- Add More Contact Person Button -->
                                        <button type="button" @click="contactPersons.push({ name: '', email: '', phone: '', notes: '' })" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg mt-2 focus:outline-none">
                                            + Add another contact person
                                        </button>
                                    </div>
                                </div>

                                <!-- Additional Information Section -->
                                <div x-data="{ open: false }" class="w-1/2">
                                    <button type="button" @click="open = !open" class="bg-blue-500 text-white font-bold py-2 px-4 w-full rounded-lg hover:bg-blue-700 transition focus:outline-none">
                                        Additional Information
                                        <i class="fas fa-chevron-down" x-show="!open"></i>
                                        <i class="fas fa-chevron-up" x-show="open"></i>
                                    </button>
                                    <div x-show="open" x-cloak class="mt-4 border rounded-lg shadow">
                                        <div class="p-4">
                                            <div class="mb-4">
                                                <label for="phone" class="block text-sm font-bold mb-2">Phone</label>
                                                <input id="phone" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" type="text" name="phone" value="{{ old('phone') }}">
                                            </div>
                                            <div class="mb-4">
                                                <label for="address" class="block text-sm font-bold mb-2">Address</label>
                                                <input id="address" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" type="text" name="address" value="{{ old('address') }}">
                                            </div>
                                            <div class="mb-4">
                                                <label for="city" class="block text-sm font-bold mb-2">City</label>
                                                <input id="city" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" type="text" name="city" value="{{ old('city') }}">
                                            </div>
                                            <div class="mb-4">
                                                <label for="zip_code" class="block text-sm font-bold mb-2">Zip Code</label>
                                                <input id="zip_code" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" type="text" name="zip_code" value="{{ old('zip_code') }}">
                                            </div>
                                            <div class="mb-4">
                                                <label for="country" class="block text-sm font-bold mb-2">Country</label>
                                                <input id="country" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" type="text" name="country" value="{{ old('country') }}">
                                            </div>
                                            <div class="mb-4">
                                                <label for="status" class="block text-sm font-bold mb-2">Status</label>
                                                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @foreach(App\Models\Client::statuses() as $status)
                                                        <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- Notes Section (with Multiple Notes) -->
                                            <div class="mb-4" x-data="{ notes: [''] }">
                                                <label for="client_notes" class="block text-sm font-bold mb-2">
                                                    {{ __('Notes') }}
                                                </label>
                                                
                                                <!-- Existing Textarea for the First Note -->
                                                <template x-for="(note, index) in notes" :key="index">
                                                    <div class="mt-4 sm:mt-0 sm:col-span-2">
                                                        <textarea 
                                                            :id="'client_note_' + index" 
                                                            :name="'client_notes[' + index + ']'" 
                                                            x-model="notes[index]" 
                                                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md mb-2">
                                                        </textarea>

                                                        <!-- Button to remove note if more than one exists -->
                                                        <button type="button" x-show="notes.length > 1" @click="notes.splice(index, 1)" class="text-red-500 hover:underline mb-2">
                                                            Remove Note
                                                        </button>
                                                    </div>
                                                </template>
                                                <!-- Button to Add New Note -->
                                                <button type="button" @click="notes.push('')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg mt-2">
                                                    + Add another note
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- Form Ends Here -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    // Email validation and error handling
    document.querySelector('form').addEventListener('submit', function(event) {
        var emailInput = document.getElementById('email');
        var emailValue = emailInput.value;
    
        // Clear any previous error messages
        var errorElement = document.querySelector('.email-error');
        if (errorElement) {
            errorElement.remove();
        }
    
        // Simple email validation regex
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        
        if (emailValue && !emailPattern.test(emailValue)) {
            event.preventDefault(); // Stop form submission
            var errorMsg = document.createElement('p');
            errorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'email-error');
            errorMsg.textContent = 'Please enter a valid email address.';
            emailInput.parentElement.appendChild(errorMsg);
        }
    
        // Additional checks for contact person fields (optional validation logic can be added here)
    });
    
    // Client type change function to show/hide company information
    function clientTypeChange(selectObj) {
        var idx = selectObj.selectedIndex;
        var clientType = selectObj.options[idx].value;
    
        var companyInfo = document.getElementById('company_info');
        if (clientType === 'company') {
            companyInfo.style.display = 'block';
        } else {
            companyInfo.style.display = 'none';
        }
    }
    
    // Call the function immediately after the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', (event) => {
        clientTypeChange(document.getElementById('client_type'));
    });
    
    // Alpine.js for dynamically adding/removing contact persons
    document.addEventListener('alpine:init', () => {
        Alpine.data('contactPersonManager', () => ({
            contactPersons: [],
    
            // Add a new contact person
            addContactPerson() {
                this.contactPersons.push({
                    name: '',
                    email: '',
                    phone: '',
                    notes: ''
                });
            },
    
            // Remove a contact person by index
            removeContactPerson(index) {
                this.contactPersons.splice(index, 1);
            }
        }));
    });
</script>
    