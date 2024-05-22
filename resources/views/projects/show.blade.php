<x-app-layout>
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">{{ $project->title }}</h1>

        <div class="mb-4">
            <a href="{{ route('projects.tasks.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Task
            </a>
        </div>

        <div class="mb-4">
            <h2 class="text-xl font-bold mb-4">Notes</h2>
            @foreach ($project->notes as $note)
                <div class="mb-2">
                    <p>{{ $note->content }}</p>
                    <a href="{{ route('projects.notes.edit', [$project, $note]) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Edit Note
                    </a>
                    <a href="{{ route('projects.notes.show', [$project, $note]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        View Note
                    </a>
                </div>
            @endforeach
            <a href="{{ route('projects.notes.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Note
            </a>
        </div>

        <div class="mb-4">
            <h2 class="text-xl font-bold mb-4">Contracts</h2>
            @foreach ($project->contracts as $contract)
                <div class="mb-2">
                    <p>{{ $contract->title }}</p> <!-- Replace with actual contract property -->
                    <a href="{{ route('projects.contracts.edit', [$project, $contract]) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Edit Contract
                    </a>
                    <a href="{{ route('projects.contracts.show', [$project, $contract]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        View Contract
                    </a>
                </div>
            @endforeach
            <a href="{{ route('projects.contracts.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Contract
            </a>
        </div>

        @include('tasks.index', ['project' => $project])
    </div>
</x-app-layout>