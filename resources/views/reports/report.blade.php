<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 0.875rem;
        }

        .report-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1.5rem;
            background-color: #f7fafc;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
        }

        .report-header {
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .report-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d3748;
        }

        .report-header .meta-info {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #4a5568;
        }

        .report-section {
            margin-bottom: 2rem;
        }

        .report-section h2 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .report-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
            padding: 1rem;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
        }

        .report-item p {
            margin: 0;
            font-size: 0.875rem;
            color: #4a5568;
        }

        .report-total {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
            padding: 1rem;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
        }

        .report-total p {
            margin: 0;
            font-size: 1rem;
            color: #2d3748;
        }

        .report-section-total {
            font-weight: bold;
            text-align: right;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="report-container">
        <div class="report-header">
            <h1>Project Report: {{ $project->title }}</h1>
            <div class="meta-info">
                <div>
                    @if($project->client)
                        <p><strong>Client Name:</strong> {{ $project->client->name }}</p>
                        <p><strong>Client Email:</strong> {{ $project->client->email }}</p>
                    @else
                        <p><strong>Client:</strong> Information not available</p>
                    @endif
                </div>
                <div>
                    <p><strong>Report Date:</strong> {{ now()->toFormattedDateString() }}</p>
                    <p><strong>Project ID:</strong> #{{ $project->id }}</p>
                </div>
            </div>
        </div>

        <!-- Project-Based Tasks Section -->
        @if($projectTasks->isNotEmpty())
        <div class="report-section">
            <h2>Project-Based Tasks</h2>
            @php $projectTotal = 0; @endphp
            @foreach ($projectTasks as $task)
                <div class="report-item">
                    <p><strong>Task:</strong> {{ $task->title }}</p>
                    <p><strong>Price:</strong> {{ number_format($task->taskable->price, 2) }} DKK</p>
                    @php $projectTotal += $task->taskable->price; @endphp
                </div>
            @endforeach
            <p class="report-section-total">Total for Project-Based Tasks: {{ number_format($projectTotal, 2) }} DKK</p>
        </div>
        @endif

        <!-- Hourly Tasks Section -->
        @if($hourlyTasks->isNotEmpty())
        <div class="report-section">
            <h2>Hourly Tasks</h2>
            @php $hourlyTotal = 0; @endphp
            @foreach ($hourlyTasks as $task)
                @php 
                    $hours = floor($task->taskable->registrationHourly->sum('minutes_worked') / 60);
                    $minutes = $task->taskable->registrationHourly->sum('minutes_worked') % 60;
                    $hoursTotal = ($hours + $minutes / 60) * $task->taskable->rate_per_hour;
                    $hourlyTotal += $hoursTotal;
                @endphp
                <div class="report-item">
                    <p><strong>Task:</strong> {{ $task->title }}</p>
                    <p><strong>Time:</strong> {{ sprintf('%02d:%02d', $hours, $minutes) }}</p>
                    <p><strong>Total:</strong> {{ number_format($hoursTotal, 2) }} DKK</p>
                </div>
            @endforeach
            <p class="report-section-total">Total for Hourly Tasks: {{ number_format($hourlyTotal, 2) }} DKK</p>
        </div>
        @endif

        <!-- Products Sold Section -->
        @if($products->isNotEmpty())
        <div class="report-section">
            <h2>Products Sold</h2>
            @php $productsTotal = 0; @endphp
            @foreach ($products as $product)
                <div class="report-item">
                    <p><strong>Product:</strong> {{ $product['product']->title }}</p>
                    <p><strong>Quantity:</strong> {{ $product['total_sold'] }}</p>
                    <p><strong>Total Price:</strong> {{ number_format($product['total_price'], 2) }} DKK</p>
                    @php $productsTotal += $product['total_price']; @endphp
                </div>
            @endforeach
            <p class="report-section-total">Total for Products Sold: {{ number_format($productsTotal, 2) }} DKK</p>
        </div>
        @endif

        <!-- Distance Driven Section -->
        @if($distanceTasks->isNotEmpty())
        <div class="report-section">
            <h2>Distance Driven</h2>
            @php $distanceTotal = 0; @endphp
            @foreach ($distanceTasks as $task)
                @php 
                    $distance = $task->taskable->registrationDistances->sum('distance');
                    $distancePrice = $distance * $task->taskable->price_per_km;
                    $distanceTotal += $distancePrice;
                @endphp
                <div class="report-item">
                    <p><strong>Distance:</strong> {{ $distance }} km</p>
                    <p><strong>Total:</strong> {{ number_format($distancePrice, 2) }} DKK</p>
                </div>
            @endforeach
            <p class="report-section-total">Total for Distance Driven: {{ number_format($distanceTotal, 2) }} DKK</p>
        </div>
        @endif

        <!-- Overall Total Section -->
        <div class="report-total">
            <p><strong>Total for all sections:</strong></p>
            <p class="text-xl font-bold">
                {{ number_format($projectTotal + $hourlyTotal + $productsTotal + $distanceTotal, 2) }} DKK
            </p>
        </div>
    </div>
</body>
</html>
