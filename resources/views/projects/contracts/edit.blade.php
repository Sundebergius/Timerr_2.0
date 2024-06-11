<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Contract
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('projects.contracts.update', ['project' => $contract->project->id, 'contract' => $contract->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mt-4">
                            <label for="service_description" class="block text-sm font-medium text-gray-700">Service Description</label>
                            <textarea id="service_description" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="service_description" required>{{ $contract->service_description }}</textarea>
                        </div>

                        <div class="mt-4">
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input id="start_date" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" type="date" name="start_date" value="{{ $contract->start_date }}" required />
                        </div>
                        
                        <div class="mt-4">
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input id="end_date" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" type="date" name="end_date" value="{{ $contract->end_date }}" />
                        </div>

                        <div class="mt-4">
                            <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Amount</label>
                            <input id="total_amount" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" type="number" step="0.01" name="total_amount" value="{{ $contract->total_amount }}" required />
                        </div>

                        <!-- Dropdown for currency selection -->
                        <div class="mt-4">
                            <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                            <select id="currency" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="currency" required>
                                <option value="DKK" {{ $contract->currency == 'DKK' ? 'selected' : '' }}>DKK</option>
                                <option value="EUR" {{ $contract->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="USD" {{ $contract->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                <!-- Add more options as needed -->
                            </select>
                        </div>

                        <div class="mt-4">
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input id="due_date" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" type="date" name="due_date" value="{{ $contract->due_date }}" required />
                        </div>

                        <div class="mt-4">
                            <label for="payment_terms" class="block text-sm font-medium text-gray-700">Payment Terms</label>
                            <textarea id="payment_terms" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="payment_terms" required>{{ $contract->payment_terms }}</textarea>
                        </div>

                        <div class="mt-4">
                            <label for="additional_terms" class="block text-sm font-medium text-gray-700">Additional Terms</label>
                            <textarea id="additional_terms" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="additional_terms">{{ $contract->additional_terms }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Update Contract') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
