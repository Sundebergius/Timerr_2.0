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

                        <div class="mb-4">
                            <label for="client_type" class="block text-sm font-bold mb-2">{{ __('Client Type') }}</label>

                            <select name="client_type" id="client_type" onchange="clientTypeChange(this)" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="individual">Individual</option>
                                <option value="company">Company</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-bold mb-2">{{ __('Name') }}</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-bold mb-2">{{ __('Email') }}</label>
                            <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>

                        <div id="company_info">
                            {{-- <div class="mb-4">
                                <label for="company" class="block text-sm font-bold mb-2">{{ __('Company') }}</label>
                                <input id="company" type="text" name="company" value="{{ old('company') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            </div> --}}

                            <div class="mb-4">
                                <label for="cvr" class="block text-sm font-bold mb-2">{{ __('CVR') }}</label>
                                <input id="cvr" type="text" name="cvr" value="{{ old('cvr') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            </div>
                        </div>
                        <!-- Additional Information -->
                        <div class="mb-4" x-data="{ open: false }">
                            <button @click="open = !open" class="text-white font-bold py-2 px-4 rounded border border-blue-500 bg-gray-300 hover:bg-gray-600 transition" type="button">
                                Additional Information <i class="fas fa-chevron-down" x-show="!open"></i><i class="fas fa-chevron-up" x-show="open"></i>
                            </button>
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
                                        <label for="country" class="block text-sm font-bold mb-2">{{ __('Country') }}</label>
                                        <input id="country" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="country" value="{{ old('country') }}" />
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