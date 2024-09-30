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

                        <!-- Search Bar for CVR API -->
                        <div class="mb-4">
                            <label for="company_search" class="block text-sm font-bold mb-2">{{ __('Search for Company') }}</label>
                            <input id="company_search" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Search by company name or CVR" oninput="searchCompany(this.value)" />
                            <ul id="company_results" class="bg-white border rounded w-full mt-2 max-h-40 overflow-y-auto"></ul>
                        </div>

                        <!-- Client Info Fields (auto-filled when company is selected) -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-bold mb-2">{{ __('Name') }}</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-bold mb-2">{{ __('Email') }}</label>
                            <input id="email" type="text" name="email" value="{{ old('email') }}" autofocus class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>

                        <div class="mb-4">
                            <label for="cvr" class="block text-sm font-bold mb-2">{{ __('CVR') }}</label>
                            <input id="cvr" type="text" name="cvr" value="{{ old('cvr') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>

                       <!-- Additional Client Info (optional) -->
                       <div id="additional_info" class="mt-4">
                            <div x-show="open" class="mt-4 border rounded shadow">
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
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Create Client') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    async function searchCompany(query) {
        const resultsContainer = document.getElementById('company_results');
        resultsContainer.innerHTML = ''; // Clear previous results

        if (query.length < 3) return; // Only search if query is at least 3 characters

        try {
            const response = await fetch(`/api/cvr-search?query=${query}`);
            const data = await response.json();

            if (data && data.name) {
                // We have a single result from the API; let's populate the form fields
                const listItem = document.createElement('li');
                listItem.textContent = `${data.name} (${data.vat})`;
                listItem.classList.add('p-2', 'hover:bg-gray-200', 'cursor-pointer');
                listItem.onclick = () => selectCompany(data); // Call the selectCompany function
                resultsContainer.appendChild(listItem);
            } else {
                resultsContainer.innerHTML = '<li class="p-2">No results found</li>';
            }
        } catch (error) {
            console.error('Error fetching company data:', error);
        }
    }

    function selectCompany(company) {
        // Populate form fields with company data from the CVR API response
        document.getElementById('name').value = company.name || '';
        document.getElementById('email').value = company.email || '';
        document.getElementById('cvr').value = company.vat || '';
        document.getElementById('address').value = company.address || '';
        document.getElementById('city').value = company.city || '';
        document.getElementById('zip_code').value = company.zipcode || '';
        document.getElementById('country').value = 'DK'; // Assuming Denmark as default

        // Clear search results after a selection is made
        document.getElementById('company_results').innerHTML = '';
    }
</script>
