<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">

            <!-- Sticky Action Buttons -->
            <div class="sticky top-0 bg-white p-4 shadow-md z-10 mb-8 flex flex-col sm:flex-row justify-between items-center">
                <!-- Removed duplicate project title -->
                <div class="flex flex-wrap gap-2 sm:gap-4">
                    <a href="{{ route('projects.tasks.create', $project) }}" class="btn-primary">Add Task</a>
                    <a href="{{ route('projects.notes.create', $project) }}" class="btn-secondary">Add Note</a>
                    <a href="{{ route('projects.contracts.create', $project) }}" class="btn-secondary">Add Contract</a>
                </div>
            </div>

            <!-- Notes Section (conditional) -->
            @if ($project->notes->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Notes</h2>
                    <div class="space-y-4">
                        @foreach ($project->notes as $note)
                            <div class="p-4 bg-gray-50 border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center">
                                    <p class="text-lg font-semibold text-gray-700">{{ $note->title }}</p>
                                    <div class="flex space-x-2">
                                        <!-- Edit Button -->
                                        <a href="{{ route('projects.notes.edit', [$project, $note]) }}" class="text-blue-500 hover:text-blue-700">
                                            <x-heroicon-s-pencil class="w-5 h-5"/>
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('projects.notes.destroy', [$project, $note]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this note?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <x-heroicon-s-trash class="w-5 h-5"/>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Note Content Preview -->
                                <p class="text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($note->content, 100, '...') }}</p>

                                @if(strlen($note->content) > 100)
                                    <!-- Full Content Toggle -->
                                    <div id="fullContent{{ $note->id }}" class="hidden">
                                        <p class="text-gray-700 mt-2">{{ $note->content }}</p>
                                    </div>

                                    <!-- Read More/Less Button -->
                                    <a href="#" class="text-blue-500 hover:text-blue-700" 
                                    onclick="toggleReadMore({{ $note->id }}); return false;" 
                                    id="toggleButton{{ $note->id }}">
                                    Read More
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Contracts Section (conditional) -->
            @if ($project->contracts->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Contracts</h2>
                <div class="space-y-4">
                    @foreach ($project->contracts as $contract)
                        <div class="p-4 bg-gray-50 border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-center">
                                <p class="text-lg font-semibold text-gray-700">
                                    {{ $contract->title ? $contract->title : 'Contract ' . $contract->id }}
                                </p>
                                <div class="flex space-x-2">
                                    <a href="{{ route('projects.contracts.edit', [$project, $contract]) }}" class="text-blue-500 hover:text-blue-700">
                                        <x-heroicon-s-pencil class="w-5 h-5"/>
                                    </a>
                                    <!-- Delete Button -->
                                    <form action="{{ route('projects.contracts.destroy', [$project, $contract]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this contract?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <x-heroicon-s-trash class="w-5 h-5"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-gray-600 mt-2">
                                {{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') }} ({{ $contract->status }})
                            </p>
                            <button onclick="toggleDetails('details{{ $contract->id }}', this)" class="mt-2 text-blue-500 hover:text-blue-700">
                                Show Details
                            </button>
                            <div id="details{{ $contract->id }}" class="mt-2 hidden">
                                <p><strong>Service Description:</strong> {{ $contract->service_description }}</p>
                                <p><strong>Total Amount:</strong> {{ number_format($contract->total_amount, 2) }} {{ $contract->currency }}</p>
                                <p><strong>Due Date:</strong> {{ $contract->due_date ? $contract->due_date->format('Y-m-d') : 'N/A' }}</p>
                                <p><strong>Payment Terms:</strong> {{ $contract->payment_terms }}</p>
                                <p><strong>Additional Terms:</strong> {{ $contract->additional_terms ?? 'None' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Tasks Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Tasks</h2>
                @include('tasks.index', ['project' => $project])
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    .btn-primary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        background-color: #1E40AF;
        color: white;
        padding: 10px 16px;
        border-radius: 4px;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-primary:hover {
        background-color: #1D4ED8;
        transform: scale(1.05);
    }

    .btn-secondary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        background-color: #3B82F6;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #60A5FA;
    }

    .isSticky {
        border-bottom: 2px solid #E5E7EB;
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

    // Define the toggleReadMore function outside the DOMContentLoaded event listener
    function toggleReadMore(noteId) {
        const fullContent = document.getElementById('fullContent' + noteId);
        const toggleButton = document.getElementById('toggleButton' + noteId);

        // Toggle the visibility of the full content
        fullContent.classList.toggle('hidden');

        // Change button text
        if (fullContent.classList.contains('hidden')) {
            toggleButton.textContent = 'Read More';
        } else {
            toggleButton.textContent = 'Read Less';
        }
    }

    function toggleDetails(detailsId, button) {
        const details = document.getElementById(detailsId);
        const isHidden = details.classList.contains('hidden');
        
        // Toggle visibility
        details.classList.toggle('hidden');
        
        // Update button text
        button.textContent = isHidden ? 'Hide Details' : 'Show Details';
    }
</script>
