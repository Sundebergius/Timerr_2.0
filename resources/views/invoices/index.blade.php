<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Invoices Overview</h1>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($invoices as $invoice)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-4">
                    <div class="mb-2">
                        <h2 class="text-lg font-semibold text-gray-800">{{ $invoice->title }}</h2>
                    </div>
                    <div class="mb-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ $invoice->status }}
                        </span>
                    </div>
                    <div class="mb-2 text-sm text-gray-500">
                        <strong>Issue Date:</strong> {{ $invoice->issue_date }}
                    </div>
                    <div class="mb-2 text-sm text-gray-500">
                        <strong>Due Date:</strong> {{ $invoice->due_date }}
                    </div>
                    <div class="flex justify-between items-center">
                        <form action="{{ route('invoices.updateStatus', $invoice->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="draft" {{ $invoice->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ $invoice->status == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </form>
                        <div class="flex">
                            <a href="{{ route('invoices.show', $invoice->id) }}"
                                target="_blank"
                                class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                View
                            </a>
                            <a href="{{ route('invoices.edit', $invoice->id) }}"
                                class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Edit
                            </a>
                            <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-block bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
