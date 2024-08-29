<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Webhook Integrations</h1>

        <!-- Display Success or Error Messages -->
        @if(session('success') || $errors->any())
            <div id="flash-messages" class="mb-6">
                @if(session('success'))
                    <div class="message bg-green-100 text-green-900 border border-green-200 flex justify-between items-center p-3 rounded-md shadow-md transition duration-300 ease-in-out">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 4v6a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2h.586a1 1 0 0 0 .707-.293l1.414-1.414a2 2 0 0 1 2.828 0L15.707 8.707a1 1 0 0 0 .707.293H17a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" class="ml-2 text-green-900 hover:text-green-700 close-btn">
                            &times;
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="message bg-red-100 text-red-900 border border-red-200 flex justify-between items-center p-3 rounded-md shadow-md transition duration-300 ease-in-out">
                        <div>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="ml-2 text-red-900 hover:text-red-700 close-btn">
                            &times;
                        </button>
                    </div>
                @endif
            </div>
        @endif

        <!-- Create New Webhook Form -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Create New Webhook</h2>
            <form method="POST" action="{{ route('integrations.webhooks.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Webhook Name</label>
                    <input type="text" id="name" name="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700">Webhook URL</label>
                    <input type="url" id="url" name="url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="{{ old('url') }}" required>
                </div>
                <div>
                    <label for="event" class="block text-sm font-medium text-gray-700">Event</label>
                    <select id="event" name="event" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                        <option value="">Select Event</option>
                        <option value="send_project_data" {{ old('event') == 'send_project_data' ? 'selected' : '' }}>Send Project Data</option>
                        <option value="task_created" {{ old('event') == 'task_created' ? 'selected' : '' }}>Task Created</option>
                        <option value="task_completed" {{ old('event') == 'task_completed' ? 'selected' : '' }}>Task Completed</option>
                        <option value="project_created" {{ old('event') == 'project_created' ? 'selected' : '' }}>Project Created</option>
                        <option value="project_completed" {{ old('event') == 'project_completed' ? 'selected' : '' }}>Project Completed</option>
                        <option value="client_created" {{ old('event') == 'client_created' ? 'selected' : '' }}>Client Created</option>
                        <option value="client_status_updated" {{ old('event') == 'client_status_updated' ? 'selected' : '' }}>Client Status Updated</option>
                        {{-- <option value="user_signed_up" {{ old('event') == 'user_signed_up' ? 'selected' : '' }}>User Signed Up</option> --}}
                        <!-- Add more predefined options here -->
                    </select>
                </div>

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out">Create Webhook</button>
                </div>
            </form>
        </div>

        <!-- Trigger Webhook Form -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Trigger Webhook</h2>
            <form method="POST" action="{{ route('integrations.webhooks.trigger') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="webhook_id" class="block text-sm font-medium text-gray-700">Select Webhook</label>
                    <select id="webhook_id" name="webhook_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                        <option value="">Select Webhook</option>
                        @foreach($webhooks as $webhook)
                            <option value="{{ $webhook->id }}" data-event="{{ $webhook->event }}">{{ $webhook->name }} ({{ $webhook->url }})</option>
                        @endforeach
                    </select>
                </div>
                <div id="project-fields" class="hidden">
                    <label for="project_id_trigger" class="block text-sm font-medium text-gray-700">Select Project</label>
                    <select id="project_id_trigger" name="project_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out">Trigger Webhook</button>
                </div>
            </form>
        </div>

        <!-- List of Webhooks -->
        <div>
            <h2 class="text-2xl font-bold mb-4">Existing Webhooks</h2>
            @if(isset($webhooks) && $webhooks->isEmpty())
                <div class="bg-yellow-100 text-yellow-700 p-4 rounded-md">
                    <p>No webhooks available. Please create a new webhook to get started.</p>
                </div>
            @elseif(isset($webhooks))
                <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($webhooks as $webhook)
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="p-4">
                                <h3 class="text-xl font-semibold mb-1">{{ $webhook->name }}</h3>
                                <p class="text-gray-600 mb-2"><span class="font-medium">URL:</span> {{ $webhook->url }}</p>
                                <p class="text-gray-500 mb-2"><span class="font-medium">Event:</span> {{ $webhook->event }}</p>
                                <p class="text-sm">
                                    <span class="inline-block px-2 py-1 rounded-full text-white 
                                        {{ $webhook->active ? 'bg-green-500' : 'bg-red-500' }}">
                                        {{ $webhook->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            <div class="flex justify-end p-4 border-t border-gray-200">
                                <form action="{{ route('integrations.webhooks.toggle', $webhook) }}" method="POST" class="mr-2">
                                    @csrf
                                    <button type="submit" class="flex items-center bg-{{ $webhook->active ? 'red' : 'green' }}-500 text-white px-4 py-2 rounded-md shadow-sm hover:bg-{{ $webhook->active ? 'red' : 'green' }}-600 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $webhook->active ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}" />
                                        </svg>
                                        {{ $webhook->active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('integrations.webhooks.destroy', $webhook) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center bg-red-500 text-white px-4 py-2 rounded-md shadow-sm hover:bg-red-600 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>        

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const webhookSelect = document.getElementById('webhook_id');
                const projectFields = document.getElementById('project-fields');
        
                webhookSelect.addEventListener('change', function () {
                    // Get the selected option
                    const selectedOption = webhookSelect.options[webhookSelect.selectedIndex];
                    // Get the event type associated with the selected webhook
                    const eventType = selectedOption.getAttribute('data-event');
        
                    // Show or hide the project fields based on the event type
                    if (eventType === 'send_project_data') {
                        projectFields.classList.remove('hidden');
                    } else {
                        projectFields.classList.add('hidden');
                    }
                });

                // Auto-hide flash messages with a smooth fade-out
                setTimeout(() => {
                    const flashMessages = document.querySelectorAll('#flash-messages .message');
                    flashMessages.forEach(message => {
                        message.style.transition = 'opacity 1s ease-out'; // Smooth transition for fading out
                        message.style.opacity = 0;
                        setTimeout(() => message.remove(), 1000); // Remove after fade-out transition
                    });
                }, 5000);
                
                // Add click event listeners to close buttons
                document.querySelectorAll('.close-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const message = this.closest('.message');
                        message.style.transition = 'opacity 1s ease-out'; // Smooth transition for fading out
                        message.style.opacity = 0;
                        setTimeout(() => message.remove(), 1000); // Remove after fade-out transition
                    });
                });
            });
        </script>
    </div>
</x-app-layout>
