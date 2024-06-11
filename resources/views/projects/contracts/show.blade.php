<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Contract Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $project->title }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Client</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $client->name }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Service Description</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->service_description }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->start_date }}</p>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->end_date }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->total_amount }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->currency }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->due_date }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->payment_terms }}</p>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Additional Terms</label>
                        <p class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">{{ $contract->additional_terms }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
