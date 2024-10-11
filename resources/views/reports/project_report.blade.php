<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $headerDetails['title'] ?? 'Project Report' }}</title>
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

        .notes-section {
            font-size: 0.875rem;
            color: #4a5568;
            margin-top: 1rem;
        }

        .notes-section h2 {
            font-size: 1rem;
            font-weight: bold;
            color: #08577a; /* Highlight to match logo color */
            margin-bottom: 0.5rem;
            text-align: center; /* Center align the heading */
        }

        .signature-section {
            display: flex;
            justify-content: center;
            gap: 2rem; /* Space between signature items */
            text-align: center;
            margin-top: 1rem;
            page-break-inside: avoid;
        }

        .signature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-item p {
            margin-bottom: 1rem; /* Space between text and line */
            font-size: 1rem;
            color: #4a5568;
        }

        .signature-line {
            width: 100%; /* Line fills container */
            height: 1px;
            background-color: #4a5568;
            margin-top: 1rem; /* Space for actual signature below the line */
        }

        .footer {
            font-size: 0.75rem;
            color: #4a5568;
            text-align: center;
            margin-top: 2rem;
            page-break-inside: avoid;
        }

        /* New footer container to keep the entire bottom section together */
        .report-footer {
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
            @if(array_key_exists('title', $headerDetails) && $headerDetails['title'])
                <h1>{{ $headerDetails['title'] }}</h1>
            @endif
            <div class="meta-info">
                @if(array_key_exists('client_name', $headerDetails) && $headerDetails['client_name'])
                    <p><strong>Client Name:</strong> {{ $headerDetails['client_name'] }}</p>
                @endif
                @if(array_key_exists('client_email', $headerDetails) && $headerDetails['client_email'])
                    <p><strong>Client Email:</strong> {{ $headerDetails['client_email'] }}</p>
                @endif
                @if(array_key_exists('report_date', $headerDetails) && $headerDetails['report_date'])
                    <p><strong>Report Date:</strong> {{ $headerDetails['report_date'] }}</p>
                @endif
                @if(array_key_exists('project_id', $headerDetails) && $headerDetails['project_id'])
                    <p><strong>Project ID:</strong> {{ $headerDetails['project_id'] }}</p>
                @endif
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
                <h2>Products and Services Sold</h2>
                @php $productsTotal = 0; @endphp
                @foreach ($products as $product)
                    <div class="report-item">
                        <p><strong>{{ $product['is_service'] ? 'Service' : 'Product' }}:</strong> {{ $product['product']->title }}</p>
                        <p><strong>Quantity:</strong> {{ $product['total_sold'] }}</p>
                        <p><strong>Total Price:</strong> {{ number_format($product['total_price'], 2) }} DKK</p>

                        <!-- Display service attributes if it's a service -->
                        @if($product['is_service'] && !empty($product['service_attributes']))
                            <div class="service-attributes mt-2">
                                <h5><strong>Service Attributes:</strong></h5>
                                <ul class="list-disc list-inside">
                                    @foreach ($product['service_attributes'] as $attribute)
                                        <li>
                                            <strong>Attribute:</strong> {{ $attribute['attribute'] ?? 'N/A' }}
                                            <ul class="ml-6">
                                                <li><strong>Price:</strong> {{ number_format($attribute['price'] ?? 0, 2) }} DKK</li>
                                                <li><strong>Quantity:</strong> {{ $attribute['quantity'] ?? 'N/A' }}</li>
                                                <li><strong>Total for Attribute:</strong> {{ number_format(($attribute['price'] ?? 0) * ($attribute['quantity'] ?? 0), 2) }} DKK</li>
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php $productsTotal += $product['total_price']; @endphp
                    </div>
                @endforeach
                <p class="report-section-total">Total for Products and Services Sold: {{ number_format($productsTotal, 2) }} DKK</p>
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

            <!-- Other Tasks -->
            @if($otherTasks->isNotEmpty())
                <div class="report-section">
                    <h2>Other Task Summary</h2>
                    @foreach ($otherTasks as $task)
                        <div class="bg-gray-50 p-4 rounded-lg shadow-md space-y-4">
                            <div class="space-y-2">
                                <p class="text-gray-600"><strong>Description:</strong></p>
                                <p class="text-gray-800">{{ \Illuminate\Support\Str::limit($task->taskable->description, 100, '...') }}</p>
                                @if(strlen($task->taskable->description) > 100)
                                    <p class="text-gray-800">{{ $task->taskable->description }}</p>
                                @endif
                            </div>
                            @if($task->customFields->count() > 0)
                                <p class="text-gray-600 font-bold">Custom Fields:</p>
                                <ul class="list-disc pl-5 space-y-1 text-gray-800">
                                    @foreach($task->customFields as $field)
                                        <li>{{ $field->field }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if($task->checklistSections->count() > 0)
                                <p class="text-gray-600 font-bold">Checklist Sections:</p>
                                <div class="pl-5 space-y-4">
                                    @foreach($task->checklistSections as $section)
                                        <p class="font-bold text-lg text-gray-800">{{ $section->title }}</p>
                                        @if($section->checklistItems->count() > 0)
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach($section->checklistItems as $item)
                                                    <li>{{ $item->item }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="report-footer">
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
                <div class="signature-item">
                    <p>Client Signature</p>
                    <div class="signature-line"></div> <!-- Replace underscores with div line -->
                </div>
                <div class="signature-item">
                    <p>Service Provider Signature</p>
                    <div class="signature-line"></div>
                </div>
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
        </div>
    </div>
</body>
</html>
