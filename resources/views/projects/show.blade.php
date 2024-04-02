<x-app-layout>
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">{{ $project->title }}</h1>

        <div class="mb-4">
            <a href="{{ route('projects.tasks.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Task
            </a>
        </div>

        <div class="mb-4">
            <a href="{{ route('projects.notes.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Note
            </a>
        </div>

        <div class="mb-4">
            <a href="{{ route('projects.contracts.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Contract
            </a>
        </div>

        @include('tasks.index', ['project' => $project])
        
        <!-- Tasks table -->
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($project->tasks as $task)
                    <tr>
                        <td class="border px-4 py-2">{{ $task->title }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('projects.tasks.show', ['project' => $project, 'task' => $task]) }}" class="text-blue-500 hover:text-blue-700">View</a>
                            <a href="{{ route('projects.tasks.edit', ['project' => $project, 'task' => $task]) }}" class="text-blue-500 hover:text-blue-700">Edit</a>
                            <form action="{{ route('projects.tasks.destroy', ['project' => $project, 'task' => $task]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>