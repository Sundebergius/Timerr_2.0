<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6 text-blue-500">Edit Invoice</h1>

        @if ($errors->any())
            <div class="bg-red-500 text-white p-4 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Project Information Section -->
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-2">Project Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="project_id" class="block text-sm font-medium text-gray-700">Project</label>
                        <input type="text" id="project_id" value="{{ $invoice->project->title }}" readonly class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                    </div>
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700">Client</label>
                        <input type="text" id="client_id" value="{{ $invoice->client->name ?? 'No Client' }}" readonly class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                    </div>
                </div>
            </div>

            <!-- Invoice Details Section -->
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-2">Invoice Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" value="{{ $invoice->title }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300" required>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300" required>
                            <option value="draft" {{ $invoice->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ $invoice->status == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <div>
                        <label for="issue_date" class="block text-sm font-medium text-gray-700">Issue Date</label>
                        <input type="date" name="issue_date" id="issue_date" value="{{ $invoice->issue_date }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                    </div>
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                        <div class="flex items-center mt-1">
                            <input type="date" name="due_date" id="due_date" value="{{ $invoice->due_date }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                            <!-- Dropdown for relative due date options -->
                            <select name="relative_due_date" id="relative_due_date" class="w-32 border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300" onchange="updateDueDate()">
                                <option value="0">Select Due Date</option>
                                <option value="14">14 days after issue</option>
                                <option value="30">30 days after issue (Net 30)</option>
                                <option value="60">60 days after issue</option>
                                <!-- Add more options as needed -->
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                        <select name="currency" id="currency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                            <option value="DKK" {{ $invoice->currency == 'DKK' ? 'selected' : '' }}>DKK</option>
                            <option value="EUR" {{ $invoice->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="USD" {{ $invoice->currency == 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                </div>
            </div>

             <!-- Financial Information Section -->
             <div class="mb-6">
                <h2 class="text-xl font-bold mb-2">Financial Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="subtotal" class="block text-sm font-medium text-gray-700">Subtotal</label>
                        <input type="number" name="subtotal" id="subtotal" value="{{ $invoice->subtotal }}" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                    </div>
                    <div>
                        <label for="discount" class="block text-sm font-medium text-gray-700">Discount (%)</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" name="discount" id="discount" value="{{ $invoice->discount }}" step="1" placeholder="Enter discount as a percentage" class="block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">%</span>
                        </div>
                    </div>
                    <div>
                        <label for="vat" class="block text-sm font-medium text-gray-700">VAT (%)</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" name="vat" id="vat" value="{{ $invoice->vat }}" step="0.01" placeholder="Enter VAT as a percentage" class="block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">%</span>
                        </div>
                    </div>
                    <div>
                        <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="number" name="total" id="total" value="{{ $invoice->total }}" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300" required>
                    </div>
                </div>
            </div>

            <!-- Payment Information Section -->
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-2">Payment Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payment_terms" class="block text-sm font-medium text-gray-700">Payment Terms</label>
                        <input type="text" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', $invoice->payment_terms) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300" readonly>
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                            <option value="Bank Transfer" {{ old('payment_method', $invoice->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="Credit Card" {{ old('payment_method', $invoice->payment_method) == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="PayPal" {{ old('payment_method', $invoice->payment_method) == 'PayPal' ? 'selected' : '' }}>PayPal</option>
                            <!-- Add more options as needed -->
                        </select>
                    </div>
                    <div>
                        <label for="last_reminder_sent" class="block text-sm font-medium text-gray-700">Last Reminder Sent</label>
                        <input type="date" name="last_reminder_sent" id="last_reminder_sent" value="{{ $invoice->last_reminder_sent }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300">
                    </div>
                </div>
            </div>

            <!-- Hidden Fields for IDs -->
            <input type="hidden" name="client_id" value="{{ $invoice->client_id }}">
            <input type="hidden" name="project_id" value="{{ $invoice->project_id }}">

            <!-- Submit Button -->
            <div class="mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring focus:border-blue-300 transition duration-500 ease-in-out">
                    Update Invoice
                </button>
            </div>
        </form>
    </div>

    <script>
        function updateDueDate() {
            const issueDateElement = document.getElementById('issue_date');
            const dueDateElement = document.getElementById('due_date');
            const relativeDueDateElement = document.getElementById('relative_due_date');
            const paymentTermsElement = document.getElementById('payment_terms');

            const issueDate = new Date(issueDateElement.value);
            let dueDate = new Date(issueDate);

            // Calculate due date based on selection
            const daysToAdd = parseInt(relativeDueDateElement.value, 10);
            if (!isNaN(daysToAdd)) {
                dueDate.setDate(issueDate.getDate() + daysToAdd);
                dueDateElement.valueAsDate = dueDate;
            }

            // Update payment terms
            const daysBetween = (dueDate - issueDate) / (1000 * 60 * 60 * 24);
            if (!isNaN(daysBetween)) {
                paymentTermsElement.value = `Net ${daysBetween} days`;
            } else {
                // Handle default or invalid case
                paymentTermsElement.value = 'Net 30 days'; // Default or fallback value
            }
        }
    </script>
</x-app-layout>
