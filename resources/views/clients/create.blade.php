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
                    <form method="POST" action="{{ route('clients.store') }}" class="form-horizontal">
                        @csrf

                        <div class="form-group">
                            <label for="client_type" class="control-label">{{ __('Client Type') }}</label>

                            <select name="client_type" id="client_type" onchange="clientTypeChange(this)" class="form-control">
                                <option value="individual">Individual</option>
                                <option value="company">Company</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="name" class="control-label">{{ __('Name') }}</label>
                            <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus />
                        </div>

                        <div class="form-group">
                            <label for="email" class="control-label">{{ __('Email') }}</label>
                            <input id="email" class="form-control" type="text" name="email" value="{{ old('email') }}" required autofocus />
                        </div>

                        <div id="company_info">
                            <div class="form-group">
                                <label for="company" class="control-label">{{ __('Company') }}</label>
                                <input id="company" class="form-control" type="text" name="company" value="{{ old('company') }}" />
                            </div>

                            <div class="form-group">
                                <label for="cvr" class="control-label">{{ __('CVR') }}</label>
                                <input id="cvr" class="form-control" type="text" name="cvr" value="{{ old('cvr') }}" />
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="form-group">
                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#additionalInfo" aria-expanded="false" aria-controls="additionalInfo">
                                Additional Information <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="collapse" id="additionalInfo">
                                <div class="card card-body">
                                    <div class="form-group">
                                        <label for="phone" class="control-label">{{ __('Phone') }}</label>
                                        <input id="phone" class="form-control" type="text" name="phone" value="{{ old('phone') }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="control-label">{{ __('Address') }}</label>
                                        <input id="address" class="form-control" type="text" name="address" value="{{ old('address') }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="country" class="control-label">{{ __('Country') }}</label>
                                        <input id="country" class="form-control" type="text" name="country" value="{{ old('country') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other fields -->

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
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