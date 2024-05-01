<!-- resources/views/registrations/create_distance.blade.php -->

<x-app-layout>
    <div class="container mx-auto px-4">
        <form method="POST" action="{{ route('projects.tasks.registrations.storeDistance', ['project' => $project->id, 'task' => $task->id]) }}" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="distance_driven">
                    Distance Driven:
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="number" id="distance_driven" name="distance_driven" required>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Create Registration
                </button>
            </div>
        </form>
    </div>
</x-app-layout>