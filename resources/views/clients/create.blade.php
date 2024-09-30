<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf

                        @if ($errors->has('name'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('name') }}</p>
                        @endif


                        <div class="mb-4">
                            <label for="client_type" class="block text-sm font-bold mb-2">
                                {{ __('Client Type') }}
                            </label>
                            <select name="client_type" id="client_type" onchange="clientTypeChange(this)" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="individual">Individual 🧑</option>
                                <option value="company">Company 🏢</option>
                            </select>
                        </div>
                        
                        
                        <p class="text-gray-600 mb-4">Fields marked with <span class="text-red-500">*</span> are required.</p>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-bold mb-2">
                                {{ __('Name') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>
                        

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-bold mb-2">{{ __('Email') }}</label>
                            <input id="email" type="text" name="email" value="{{ old('email') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            @if ($errors->has('email'))
                                <p class="text-red-500 text-sm mt-1">{{ $errors->first('email') }}</p>
                            @endif
                            <p class="text-gray-600 text-sm mt-1">Optional</p>
                        </div>

                        <!-- Conditionally show company fields -->
                        <div id="company_info" class="hidden">
                            <div class="mb-4">
                                <label for="cvr" class="block text-sm font-bold mb-2">{{ __('CVR') }}</label>
                                <input id="cvr" type="text" name="cvr" value="{{ old('cvr') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                                <p class="text-gray-600 text-sm mt-1">Optional</p>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-4" x-data="{ open: false }">
                            <button @click="open = !open" class="text-white font-bold py-2 px-4 rounded border border-blue-500 bg-gray-300 hover:bg-gray-600 transition" type="button">
                                Additional Information <i class="fas fa-chevron-down" x-show="!open"></i><i class="fas fa-chevron-up" x-show="open"></i>
                            </button>
                            <div x-show="open" x-cloak class="mt-4 border rounded shadow">
                                <div class="p-4">
                                    <div class="mb-4">
                                        <label for="phone" class="block text-sm font-bold mb-2">{{ __('Phone') }}</label>
                                        <input id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="phone" value="{{ old('phone') }}" />
                                    </div>
                                    <div class="mb-4">
                                        <label for="address" class="block text-sm font-bold mb-2">{{ __('Address') }}</label>
                                        <input id="address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="address" value="{{ old('address') }}" />
                                    </div>
                                    <div class="mb-4">
                                        <label for="city" class="block text-sm font-bold mb-2">{{ __('City') }}</label>
                                        <input id="city" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="city" value="{{ old('city') }}" />
                                    </div>
                                    <div class="mb-4">
                                        <label for="zip_code" class="block text-sm font-bold mb-2">{{ __('Zip Code') }}</label>
                                        <input id="zip_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="zip_code" value="{{ old('zip_code') }}" />
                                    </div>
                                    <div class="mb-4">
                                        <label for="country" class="block text-sm font-bold mb-2">{{ __('Country') }}</label>
                                        <input id="country" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="country" value="{{ old('country') }}" />
                                    </div>
                                    <div class="mb-4">
                                        <label for="status" class="block text-sm font-bold mb-2">{{ __('Status') }}</label>
                                        <select id="status" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                            @foreach(App\Models\Client::statuses() as $status)
                                                <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other fields -->

                        <div class="form-group">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
                                {{ __('Create Client') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
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
});

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
</script>
