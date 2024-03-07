<x-app-layout>
    
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">Add New Task</h1>
        <form action="{{ route('projects.tasks.store', $project) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                <input type="text" id="title" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div id="app">
                <task-creator></task-creator>
            </div>

            {{-- <div class="mb-4">
                <label for="start_date" class="block text-gray-700 text-sm font-bold mb-2">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label for="end_date" class="block text-gray-700 text-sm font-bold mb-2">End Date:</label>
                <input type="checkbox" id="end_date_checkbox" onclick="toggleEndDate()">
                <label for="end_date_checkbox">Does this task have an end date?</label>
                <input type="date" id="end_date" name="end_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" disabled>
            </div>

            <div class="mb-4">
                <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Location:</label>
                <input type="text" id="location" name="location" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Type:</label>
                <select id="type" name="type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="project_based">Project Based</option>
                    <option value="hourly">Hourly</option>
                    <option value="sale_of_products">Sale of Products</option>
                    <option value="distance_driven">Distance Driven</option>
                    <option value="other">Other</option>
                </select>
            </div> --}}

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-96">
                    Add Task
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
    function toggleEndDate() {
        var checkbox = document.getElementById('end_date_checkbox');
        var end_date = document.getElementById('end_date');
    
        end_date.disabled = !checkbox.checked;
    }
</script>