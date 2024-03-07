<x-app-layout>
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">{{ $project->title }}</h1>

        @foreach($project->tasks as $task)
        <div class="mb-4 p-4 bg-white rounded shadow">
            <h2 class="text-xl font-bold mb-2">{{ $task->title }}</h2>
            <p class="mb-2"><strong>Customer:</strong> {{ $task->customer }}</p>
            <p class="mb-2"><strong>Type:</strong> {{ $task->type }}</p>
            <p class="mb-2"><strong>Price:</strong> {{ $task->price }}</p>
            <p class="mb-2"><strong>Date:</strong> {{ $task->date }}</p>
            <p class="mb-2"><strong>Location:</strong> {{ $task->location }}</p>

            @if($task->type == 'hourly')
            <p class="mb-2"><strong>Hours Worked:</strong> {{ $task->hours_worked }}</p>
            @endif

            @if($task->type == 'sale_of_products')
            <p class="mb-2"><strong>Product Sold:</strong> {{ $task->product_sold }}</p>
            <p class="mb-2"><strong>Total Price:</strong> {{ $task->total_price }}</p>
            @endif

            @foreach($task->registrations as $registration)
            <div class="mt-4 p-4 bg-gray-100 rounded shadow">
                <p><strong>Time Worked:</strong> {{ $registration->time_worked }}</p>
                <p><strong>Date Worked:</strong> {{ $registration->date_worked }}</p>
                <p><strong>Comment:</strong> {{ $registration->comment }}</p>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</x-app-layout>