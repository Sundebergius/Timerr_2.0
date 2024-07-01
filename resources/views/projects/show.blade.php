<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">

            <!-- Sticky Action Buttons -->
            <div class="sticky top-0 bg-white p-4 shadow-md z-10 mb-8 flex flex-col sm:flex-row justify-between items-center">
                <h1 class="text-2xl sm:text-4xl font-bold text-gray-800 mb-4 sm:mb-0">{{ $project->title }}</h1>
                <div class="flex flex-wrap gap-2 sm:gap-4">
                    <a href="{{ route('projects.tasks.create', $project) }}" class="btn-blue">Add Task</a>
                    <a href="{{ route('projects.notes.create', $project) }}" class="btn-blue">Add Note</a>
                    <a href="{{ route('projects.contracts.create', $project) }}" class="btn-blue">Add Contract</a>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Notes</h2>
                <div class="space-y-4">
                    @foreach ($project->notes as $note)
                        <div class="p-4 bg-gray-100 rounded-lg shadow-md">
                            <div class="flex justify-between items-center">
                                <p class="text-lg font-semibold text-gray-700">{{ $note->title }}</p>
                                <div class="flex space-x-2">
                                    <a href="{{ route('projects.notes.edit', [$project, $note]) }}" class="text-yellow-500 hover:text-yellow-700">
                                        <x-heroicon-s-pencil class="w-5 h-5"/>
                                    </a>
                                    <a href="{{ route('projects.notes.show', [$project, $note]) }}" class="text-green-500 hover:text-green-700">
                                        <x-heroicon-s-eye class="w-5 h-5"/>
                                    </a>
                                </div>
                            </div>
                            <p class="text-gray-700 mt-2">{{ \Illuminate\Support\Str::limit($note->content, 100, '...') }}</p>
                            @if(strlen($note->content) > 100)
                                <div id="fullContent{{ $note->id }}" class="hidden">
                                    <p class="text-gray-700 mt-2">{{ $note->content }}</p>
                                </div>
                                <a href="#" class="text-blue-500 hover:text-blue-700" onclick="document.getElementById('fullContent{{ $note->id }}').classList.toggle('hidden'); return false;">Read More</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Contracts Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Contracts</h2>
                <div class="space-y-4">
                    @foreach ($project->contracts as $contract)
                        <div class="p-4 bg-gray-100 rounded-lg shadow-md">
                            <div class="flex justify-between items-center">
                                <p class="text-lg font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') }} ({{ $contract->status }})
                                </p>
                                <div class="flex space-x-2">
                                    <a href="{{ route('projects.contracts.edit', [$project, $contract]) }}" class="text-yellow-500 hover:text-yellow-700">
                                        <x-heroicon-s-pencil class="w-5 h-5"/>
                                    </a>
                                    <a href="{{ route('projects.contracts.show', [$project, $contract]) }}" class="text-green-500 hover:text-green-700">
                                        <x-heroicon-s-eye class="w-5 h-5"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tasks Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Tasks</h2>
                @include('tasks.index', ['project' => $project])
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    .btn-blue {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        background-color: #1E40AF;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .btn-blue:hover {
        background-color: #1D4ED8;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stickyHeader = document.querySelector('.sticky');
        const observer = new IntersectionObserver(
            ([e]) => e.target.classList.toggle('isSticky', e.intersectionRatio < 1),
            {threshold: [1]}
        );

        observer.observe(stickyHeader);
    });
</script>
