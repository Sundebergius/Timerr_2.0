<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 0.875rem; /* Smaller text size */
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1.5rem;
            background-color: #f7fafc;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
        }

        .invoice-header {
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .invoice-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d3748;
        }

        .invoice-header .meta-info {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #4a5568;
        }

        .invoice-section {
            margin-bottom: 2rem;
        }

        .invoice-section h2 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .invoice-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 1rem;
            padding: 1rem;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
        }

        .invoice-item p {
            margin: 0;
            font-size: 0.875rem;
            color: #4a5568;
        }

        .invoice-total {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
            padding: 1rem;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
        }

        .invoice-total p {
            margin: 0;
            font-size: 1rem;
            color: #2d3748;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>Invoice for Project {{ $project->title }}</h1>
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
                    <p><strong>Invoice Date:</strong> {{ now()->toFormattedDateString() }}</p>
                    <p><strong>Invoice ID:</strong> #{{ $project->id }}</p>
                </div>
            </div>
        </div>

        <div class="invoice-section">
            @if($projectTasks->isNotEmpty())
            <h2>Project-Based Tasks</h2>
            @foreach ($projectTasks as $task)
                <div class="invoice-item">
                    <p><strong>Title:</strong> {{ $task->title }}</p>
                    <p><strong>Price:</strong> {{ number_format($task->taskable->price, 2) }} DKK</p>
                </div>
            @endforeach
            @endif
        </div>

        <div class="invoice-section">
            @if($hourlyTasks->isNotEmpty())
            <h2>Hourly Tasks</h2>
            @foreach ($hourlyTasks as $task)
                @php 
                    $hours = floor($task->taskable->registrationHourly->sum('minutes_worked') / 60);
                    $minutes = $task->taskable->registrationHourly->sum('minutes_worked') % 60;
                @endphp
                <div class="invoice-item">
                    <p><strong>Title:</strong> {{ $task->title }}</p>
                    <p><strong>Time:</strong> {{ sprintf('%02d:%02d', $hours, $minutes) }}</p>
                    <p><strong>Rate:</strong> {{ number_format($task->taskable->rate_per_hour, 2) }} DKK</p>
                </div>
            @endforeach
            @endif
        </div>

        <div class="invoice-section">
            @if($products->isNotEmpty())
            <h2>Products Sold</h2>
            @foreach ($products as $product)
                <div class="invoice-item">
                    <p><strong>Title:</strong> {{ $product['product']->title }}</p>
                    <p><strong>Qty:</strong> {{ $product['total_sold'] }}</p>
                    <p><strong>Price:</strong> {{ number_format($product['product']->price, 2) }} DKK</p>
                    <p><strong>Total Price:</strong> {{ number_format($product['total_price'], 2) }} DKK</p>
                </div>
            @endforeach
            @endif
        </div>

        <div class="invoice-section">
            @if($distanceTasks->isNotEmpty())
            <h2>Distance Driven</h2>
            @foreach ($distanceTasks as $task)
                @php 
                    $distance = $task->taskable->registrationDistances->sum('distance');
                @endphp
                <div class="invoice-item">
                    <p><strong>Distance:</strong> {{ $distance }} km</p>
                    <p><strong>Rate:</strong> {{ number_format($task->taskable->price_per_km, 2) }} DKK</p>
                </div>
            @endforeach
            @endif
        </div>

        <div class="invoice-total">
            <div>
                <p><strong>Subtotal:</strong></p>
                <p><strong>Discount:</strong></p>
                <p><strong>VAT (25%):</strong></p>
                <p><strong>Total incl. VAT:</strong></p>
            </div>
            <div>
                <p>{{ number_format($subtotal, 2) }} DKK</p>
                <p>{{ number_format($discount, 2) }} DKK</p>
                <p>{{ number_format($vat, 2) }} DKK</p>
                <p class="text-xl font-bold">{{ number_format($totalWithVat, 2) }} DKK</p>
            </div>
        </div>
    </div>
</body>
</html>
