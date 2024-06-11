<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-800">{{ $project->title }}</h1>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('projects.tasks.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Task
                </a>
                <a href="{{ route('projects.notes.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Note
                </a>
                <a href="{{ route('projects.contracts.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Contract
                </a>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Notes</h2>
            <div class="space-y-4">
                @foreach ($project->notes as $note)
                    <div class="p-4 bg-white rounded-lg shadow-md">
                        {{-- title for note --}}
                        <p class="text-gray-700 mb-2">{{ $note->title }}</p>
                        {{-- content for note --}}
                        <p class="text-gray-700 mb-2">{{ \Illuminate\Support\Str::limit($note->content, 100, $end='...') }}</p>
                        @if(strlen($note->content) > 100)
                            <div id="fullContent{{ $note->id }}" class="hidden">
                                <p class="text-gray-700 mb-2">{{ $note->content }}</p>
                            </div>
                            <a href="#" class="text-blue-500 hover:text-blue-700" onclick="document.getElementById('fullContent{{ $note->id }}').classList.toggle('hidden'); return false;">Read More</a>
                        @endif
                        <div class="flex space-x-4">
                            <a href="{{ route('projects.notes.edit', [$project, $note]) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Edit Note
                            </a>
                            <a href="{{ route('projects.notes.show', [$project, $note]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                View Note
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Contracts</h2>
            <div class="space-y-4">
                @foreach ($project->contracts as $contract)
                    <div class="p-4 bg-white rounded-lg shadow-md">
                        <p class="text-gray-700 mb-2">
                            {{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') }} ({{ $contract->status }})
                        </p>
                        <div class="flex space-x-4">
                            <a href="{{ route('projects.contracts.edit', [$project, $contract]) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Edit Contract
                            </a>
                            <a href="{{ route('projects.contracts.show', [$project, $contract]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                View Contract
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @include('tasks.index', ['project' => $project])
    </div>
</x-app-layout>
