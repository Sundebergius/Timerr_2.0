<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-4">
        <div class="text-center mb-4">
            <h1 class="text-xl font-semibold">Invoice for Project {{ $project->Title }}</h1>
            {{-- <p class="text-sm">Toldstrupsgade 14, 2. 3, 9000 Aalborg</p>
            <p class="text-sm">CVR: DK43156209</p> --}}
        </div>

        <div class="flex justify-between items-center mb-4">
            <div>
                <p class="text-sm">Client Name: {{ $project->client->name }}</p>
                <p class="text-sm">Client Email: {{ $project->client->email }}</p>
            </div>
            <div>
                <p class="text-sm">Invoice Date: {{ now()->toFormattedDateString() }}</p>
                <p class="text-sm">Invoice ID: #{{ $project->id }}</p>
            </div>
        </div>

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Project-Based Tasks</h2>
            @php $total = 0 @endphp
            @foreach ($projectTasks as $task)
                <div class="flex justify-between items-center bg-white p-2 mb-2">
                    <p class="text-sm">Title: {{ $task->title }}</p>
                    <p class="text-sm">Price: {{ $task->taskable->price }}</p>
                </div>
                @php $total += $task->taskable->price @endphp
            @endforeach
        </div>

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Hourly Tasks</h2>
            @foreach ($hourlyTasks as $task)
                @php 
                    $hours = $task->taskable->registrationHourly->sum('minutes_worked') / 60;
                    $total += $hours * $task->taskable->rate_per_hour;
                @endphp
                <div class="flex justify-between items-center bg-white p-2 mb-2">
                    <p class="text-sm">Title: {{ $task->title }}</p>
                    <p class="text-sm">Hours: {{ number_format($hours, 2) }}</p>
                    <p class="text-sm">Rate: {{ $task->taskable->rate_per_hour }}</p>
                </div>
            @endforeach
        </div>

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Products Sold</h2>
            @foreach ($products as $product)
                <div class="flex justify-between items-center bg-white p-2 mb-2">
                    <p class="text-sm">Title: {{ $product->title }}</p>
                    <p class="text-sm">Qty: {{ $product->quantitySold }}</p>
                    <p class="text-sm">Price: {{ $product->price }}</p>
                </div>
                @php $total += $product->price * $product->quantitySold @endphp
            @endforeach
        </div>

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Distance Driven</h2>
            @foreach ($distanceTasks as $task)
                @php 
                    $distance = $task->taskable->registrationDistances->sum('distance');
                    $total += $distance * $task->taskable->price_per_km;
                @endphp
                <div class="flex justify-between items-center bg-white p-2 mb-2">
                    <p class="text-sm">Distance: {{ $distance }} km</p>
                    <p class="text-sm">Rate: {{ $task->taskable->price_per_km }}</p>
                </div>
            @endforeach
        </div>

        <div class="text-right mb-4">
            <p class="text-lg font-semibold">Subtotal: {{ number_format($total, 2) }} DKK</p>
            <p class="text-lg font-semibold">VAT (25%): {{ number_format($total * 0.25, 2) }} DKK</p>
            <p class="text-lg font-semibold">Total incl. VAT: {{ number_format($total * 1.25, 2) }} DKK</p>
        </div>

        <div class="text-center">
            <p class="text-sm">Demo Bank | 0000-0000000000</p>
        </div>
    </div>
</body>
</html>