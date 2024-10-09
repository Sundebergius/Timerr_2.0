<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $headerDetails['title'] }}</title>
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
        .report-header, .report-section, .report-total {
            margin-bottom: 1.5rem;
            page-break-inside: avoid; /* Avoid splitting sections */
        }
        .report-section, .report-item {
            page-break-inside: avoid; /* Prevent splitting within sections */
        }
        .logo-container img {
            max-width: 150px;
            height: auto;
        }
        .report-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #08577a; /* Logo color */
        }
        .report-section h2 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #08577a;
            page-break-after: avoid;
        }
        .report-section h2 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #08577a; /* Logo color */
        }
        .report-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
            padding: 1rem;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
            page-break-inside: avoid;
        }
        .report-item:nth-child(even) {
            background-color: #f7fafc;
        }
        .report-item p {
            margin: 0;
            font-size: 0.875rem;
            color: #4a5568;
        }
        .report-total {
            display: grid;
            grid-template-columns: 3fr 1fr;
            padding: 1rem;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
            color: #08577a; /* Logo color for the total section */
            page-break-before: always; /* Force the total section to a new page if it doesn't fit */
        }
        .report-total, .vat-discount-section {
            display: grid;
            grid-template-columns: 3fr 1fr;
            padding: 1rem;
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 0.5rem;
            page-break-inside: avoid;
        }
        .report-total p, .vat-discount-section p {
            margin: 0;
            font-size: 1rem;
            color: #08577a;
            font-weight: bold;
        }
        .report-total p {
            margin: 0;
            font-size: 1rem;
            color: #08577a; /* Highlight in logo color */
            font-weight: bold;
        }
        .signature-section, .notes-section {
            font-size: 0.875rem;
            color: #4a5568;
        }
        .footer {
            font-size: 0.75rem;
            color: #4a5568;
            text-align: center;
            margin-top: 2rem;
            page-break-inside: avoid;
        }
    </style>        
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="report-container">
        <!-- Logo -->
        @if($logoData)
        <div class="logo-container text-center mb-4">
            <img src="data:image/png;base64,{{ $logoData }}" alt="Company Logo">
        </div>
        @endif

        <!-- Header -->
        <div class="report-header">
            <h1>{{ $headerDetails['title'] }}</h1>
            <div class="meta-info">
                <div>
                    <p><strong>Client Name:</strong> {{ $headerDetails['client_name'] }}</p>
                    <p><strong>Client Email:</strong> {{ $headerDetails['client_email'] }}</p>
                </div>
                <div>
                    <p><strong>Report Date:</strong> {{ $headerDetails['report_date'] }}</p>
                    <p><strong>Project ID:</strong> {{ $headerDetails['project_id'] }}</p>
                </div>
            </div>
        </div>

        <!-- Project-Based Tasks -->
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

        <!-- Hourly Tasks -->
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

        <!-- Products Sold -->
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

        <!-- Distance Driven -->
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

        <!-- Subtotal Section (Always Visible) -->
        <div class="vat-discount-section">
            <p><strong>Subtotal:</strong></p>
            <p>{{ number_format($subtotal, 2) }} DKK</p>
        </div>

        <!-- VAT Section (Only for Paid Users) -->
        @if($vat > 0 && $userPlan !== 'free')
        <div class="vat-discount-section">
            <p><strong>VAT ({{ $vat }}%):</strong></p>
            <p>{{ number_format($subtotal * ($vat / 100), 2) }} DKK</p>
        </div>
        @endif

        <!-- Discount Section (Only for Paid Users) -->
        @if($discount > 0 && $userPlan !== 'free')
        <div class="vat-discount-section">
            <p><strong>Discount ({{ $discount }}%):</strong></p>
            <p>-{{ number_format($subtotal * ($discount / 100), 2) }} DKK</p>
        </div>
        @endif

        <!-- Total Calculation Summary (Adjusts Label Based on VAT and Discount Presence) -->
        @if($userPlan !== 'free' && ($vat > 0 || $discount > 0))
        <div class="report-total">
            <p>
                <strong>
                    Total 
                    @if($vat > 0 && $discount > 0)
                        after VAT and Discount:
                    @elseif($vat > 0)
                        after VAT:
                    @elseif($discount > 0)
                        after Discount:
                    @endif
                </strong>
            </p>
            <p class="text-xl font-bold">{{ number_format($totalWithVatAndDiscount, 2) }} DKK</p>
        </div>
        @endif

        <!-- Optional Watermark -->
        @if($includeWatermark)
        <div class="footer">
            <div class="flex items-center justify-center space-x-2">
                <img src="{{ asset('Timerr_icon.svg') }}" alt="Timerr Logo" style="width: 20px; height: 20px;">
                <p>Created by Timerr</p>
            </div>
        </div>
        @endif

        <!-- Optional Notes Section -->
        @if($includeNotes)
        <div class="notes-section">
            <h2>Additional Notes</h2>
            <p>{{ $notesContent ?? 'No additional notes provided.' }}</p>
        </div>
        @endif

        <!-- Optional Signature Section -->
        @if($includeSignature)
        <div class="signature-section">
            <p>___________________________</p>
            <p>Client Signature</p>
            <p>___________________________</p>
            <p>Service Provider Signature</p>
        </div>
        @endif
    </div>
</body>
</html>
