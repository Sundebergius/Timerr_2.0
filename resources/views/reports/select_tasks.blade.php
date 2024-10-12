<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- {{ __('Project Report Customization for: ') . $project->title }} --}}
            {{ __('Project Report Customization') }}
        </h2>
    </x-slot>

    <style>
    /* Smooth transition effect */
    #summary {
        max-height: 300px; /* Adjust as necessary */
        overflow-y: auto; /* Scroll if content exceeds max-height */
        transition: max-height 0.3s ease, padding 0.3s ease; /* Smooth transition on height changes */
    }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-semibold text-gray-700 mb-4 text-center">
                        Customize Your Project Report
                    </h1>
                    <p class="text-center text-sm text-gray-500 mb-6"> <!-- Added padding with 'mb-6' -->
                        Project: <strong class="text-gray-700">{{ $project->title }}</strong>
                    </p>

                    <form method="POST" action="{{ route('projects.generateReport', $project->id) }}" enctype="multipart/form-data">
                        @csrf

                    <!-- Display message about edit restrictions for Free Users -->
                    @if($subscriptionPlan === 'free')
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded" role="alert">
                            <p class="font-bold">Upgrade to a Paid Plan</p>
                            <p>Unlock additional editing capabilities by upgrading to a paid plan.</p>
                        </div>
                    @endif

                    <!-- Editable Fields for All Users -->
                    <div class="mb-8">
                        <button type="button" onclick="toggleSection('headerOfferSection')" class="w-full text-left flex justify-between items-center bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-md font-semibold text-white focus:outline-none hover:from-blue-600 hover:to-blue-700 transition-colors duration-200 ease-in-out">
                            Report Details & Customization
                            <span id="headerOfferToggleIcon" class="text-xl">&minus;</span>
                        </button>

                        <div id="headerOfferSection" class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <!-- Report Title -->
                                <div>
                                    <label for="report_title" class="block text-sm font-medium text-gray-700">Report Title</label>
                                    <input id="report_title" type="text" name="report_title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="Project Report: {{ $project->title }}">
                                </div>
                                <!-- Client Name -->
                                <div>
                                    <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name</label>
                                    <input id="client_name" type="text" name="client_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ $project->client->name ?? 'Test Client' }}">
                                </div>
                                <!-- Client Email -->
                                <div>
                                    <label for="client_email" class="block text-sm font-medium text-gray-700">Client Email</label>
                                    <input id="client_email" type="email" name="client_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ $project->client->email ?? 'Test@client.dk' }}">
                                </div>
                                <!-- Report Date -->
                                <div>
                                    <label for="report_date" class="block text-sm font-medium text-gray-700">Report Date</label>
                                    <input id="report_date" type="date" name="report_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ now()->toDateString() }}">
                                </div>
                                <!-- Project ID -->
                                <div>
                                    <label for="project_id" class="block text-sm font-medium text-gray-700">Project ID</label>
                                    <input id="project_id" type="text" name="project_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="#{{ $project->id }}">
                                </div>
                                <!-- Upload Logo -->
                                <div>
                                    <label for="report_logo" class="block text-sm font-medium text-gray-700">Upload Logo</label>
                                    <input id="report_logo" type="file" name="report_logo" accept="image/*" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                           {{ $subscriptionPlan === 'free' ? 'disabled' : '' }} 
                                           title="{{ $subscriptionPlan === 'free' ? 'Available on paid plans only' : '' }}">
                                    @if($subscriptionPlan === 'free')
                                        <p class="text-xs text-gray-500 mt-1">Available on paid plans only.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Customization Options -->
                            <div class="mt-8">
                                <h3 class="text-lg font-semibold text-blue-600 mb-4 border-b-2 border-blue-500 pb-2">
                                    Customization Options
                                </h3>

                                <!-- Grid for additional customization fields -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <!-- Notes Section -->
                                    <div class="col-span-2">
                                        <label for="notes_content" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                                        <textarea id="notes_content" name="notes_content" class="mt-2 w-full border-gray-300 rounded-md shadow-sm" placeholder="Enter additional notes here..."></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Add extra information or instructions here.</p>
                                    </div>

                                    <!-- Signature Section -->
                                    <div class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition col-span-2">
                                        <input type="checkbox" name="include_signature" value="1" class="h-5 w-5 text-blue-600 border-gray-300 rounded mt-1">
                                        <span>
                                            <span class="text-gray-700 font-medium">Include Signature Section</span>
                                            <p class="text-gray-500 text-sm mt-1">Add a client and service provider signature section to the report for formal approvals.</p>
                                        </span>
                                    </div>

                                    <!-- Watermark -->
                                    <div class="flex flex-col md:flex-row items-start md:space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                        <input type="checkbox" name="add_watermark" value="1" class="h-5 w-5 text-blue-600 border-gray-300 rounded mt-1 md:mt-0" 
                                            {{ $subscriptionPlan === 'free' ? 'checked disabled' : '' }}>
                                        <span class="mt-2 md:mt-0">
                                            <span class="text-gray-700 font-medium">Add "Created by Timerr" Watermark</span>
                                            <p class="text-gray-500 text-sm mt-1">
                                                {{ $subscriptionPlan === 'free' ? 'Watermark is mandatory on free plans. Upgrade to remove it.' : 'Optional watermark for paid plans.' }}
                                            </p>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Summary Preview -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-blue-600 mb-4 border-b-2 border-blue-500 pb-2">
                                Selected Tasks Overview
                            </h2>
                            <p class="text-sm text-gray-500 mb-4">
                                Below is a summary of the tasks you’ve selected. Adjust your selection to update the total cost at the bottom.
                            </p>

                            <!-- Summary of Selected Tasks -->
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-300">
                                <ul id="summary" class="text-gray-600 space-y-2">
                                    <!-- Task items will be added here by JavaScript -->
                                </ul>
                                <p class="text-xs text-gray-500 mt-4 text-right">
                                    * Summary updates automatically as you select tasks.
                                </p>
                            </div>
                        </div>

                            <!-- Project-Based Tasks -->
                            @if($projectTasks->isNotEmpty())
                                <h3 class="text-lg font-semibold text-blue-600 mb-4">Project-Based Tasks</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    @foreach($projectTasks as $task)
                                        <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                            <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded" onchange="updateSummary()">
                                            <span class="text-gray-700">{{ $task->title }} ({{ number_format($task->taskable->price, 2) }} DKK)</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Hourly Tasks -->
                            @if($hourlyTasks->isNotEmpty())
                                <h3 class="text-lg font-semibold text-blue-600 mb-4">Hourly Tasks</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    @foreach($hourlyTasks as $task)
                                        @php
                                            $totalMinutesWorked = $task->taskable->registrationHourly->sum('minutes_worked');
                                            $hoursWorked = floor($totalMinutesWorked / 60);
                                            $minutesWorked = $totalMinutesWorked % 60;
                                            $earningsPerMinute = $task->taskable->rate_per_hour / 60;
                                            $hourlyEarnings = ceil($totalMinutesWorked * $earningsPerMinute); // Round up each task's earnings individually
                                        @endphp
                                        <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                            <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded" onchange="updateSummary()">
                                            <span class="text-gray-700">
                                                {{ $task->title }} (Worked: {{ $hoursWorked }}h {{ $minutesWorked }}m, Earnings: {{ number_format($hourlyEarnings, 2) }} DKK)
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Distance-Based Tasks -->
                            @if($distanceTasks->isNotEmpty())
                                <h3 class="text-lg font-semibold text-blue-600 mb-4">Distance-Based Tasks</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    @foreach($distanceTasks as $task)
                                        @php
                                            $totalDistance = $task->taskable->registrationDistances->sum('distance');
                                            $distanceCost = $totalDistance * $task->taskable->price_per_km;
                                        @endphp
                                        <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                            <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded" onchange="updateSummary()">
                                            <span class="text-gray-700">
                                                {{ $task->title }} (Distance: {{ $totalDistance }} km, Cost: {{ number_format($distanceCost, 2) }} DKK)
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Product-Based Tasks -->
                            @if($productTasks->isNotEmpty())
                                <h3 class="text-lg font-semibold text-blue-600 mb-4">Product-Based Tasks</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    @foreach($productTasks as $task)
                                        @php
                                            $taskTotal = 0;
                                        @endphp
                                        @foreach($task->taskProduct as $taskProduct)
                                            @php
                                                $productPrice = $taskProduct->product->price ?? 0;
                                                $quantitySold = $taskProduct->quantity ?? 0;
                                                $productTotal = $productPrice * $quantitySold;
                                                
                                                $isService = $taskProduct->product->type === 'service';
                                                $serviceAttributes = json_decode($taskProduct->attributes, true) ?? [];
                                                
                                                // Calculate total for each attribute if it's a service
                                                foreach ($serviceAttributes as $attribute) {
                                                    $attributeTotal = ($attribute['price'] ?? 0) * ($attribute['quantity'] ?? 0);
                                                    $productTotal += $attributeTotal;
                                                }
                                                
                                                $taskTotal += $productTotal;
                                            @endphp
                                            <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                                <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded" onchange="updateSummary()">
                                                <span class="text-gray-700">
                                                    {{ $task->title }} - {{ $taskProduct->product->title }} 
                                                    @if(!$isService)
                                                        (Sold: {{ $quantitySold }}, Total: {{ number_format($productTotal, 2) }} DKK)
                                                    @else
                                                        (Service Total: {{ number_format($productTotal, 2) }} DKK)
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    @endforeach
                                </div>
                            @endif

                            <!-- Other Tasks -->
                            @if($otherTasks->isNotEmpty())
                                <h3 class="text-lg font-semibold text-blue-600 mb-4">Other Tasks</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    @foreach($otherTasks as $task)
                                        <label class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                            <input type="checkbox" name="selected_tasks[]" value="{{ $task->id }}" checked class="h-5 w-5 text-blue-600 border-gray-300 rounded" onchange="updateSummary()">
                                            <span class="text-gray-700">{{ $task->title }} (Custom Task)</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Paid Features: VAT and Discount Section -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-blue-600 border-b-2 border-blue-500 pb-2">Additional Options (Paid Plans Only)</h3>
                                <p class="text-sm text-gray-500 mt-2 mb-4">Unlock VAT and Discount options by upgrading to a paid plan.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-md border border-gray-300">
                                    <!-- VAT Toggle and Field -->
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" id="vatToggle" class="h-5 w-5 text-blue-600 border-gray-300 rounded"
                                            onchange="toggleVAT()"
                                            {{ $subscriptionPlan === 'free' ? 'disabled' : '' }}
                                            title="{{ $subscriptionPlan === 'free' ? 'Available on paid plans only' : '' }}">
                                        <span class="text-gray-700 font-medium">Enable VAT</span>
                                    </div>
                                    
                                    <!-- VAT Percentage Input -->
                                    <div id="vatField" class="hidden">
                                        <label for="vatInput" class="block text-sm font-medium text-gray-700">VAT (%)</label>
                                        <input type="number" name="vat" id="vatInput" step="0.01" min="0" max="100"
                                               oninput="validatePercentage(this)" placeholder="Enter VAT percentage"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                               readonly title="Enable VAT to edit this field">
                                    </div>
                                    
                                    <!-- Discount Percentage Input -->
                                    <div>
                                        <label for="discountInput" class="block text-sm font-medium text-gray-700">Discount (%)</label>
                                        <input type="number" name="discount" id="discountInput" step="0.01" min="0" max="100"
                                            oninput="validatePercentage(this)" placeholder="Enter discount percentage"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            {{ $subscriptionPlan === 'free' ? 'readonly' : '' }}
                                            title="{{ $subscriptionPlan === 'free' ? 'Available on paid plans only' : '' }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Final Total Summary Section -->
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold text-gray-700">Final Total Summary</h2>
                                <div class="bg-gray-50 p-6 rounded-md border border-gray-300 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Subtotal:</span>
                                        <span id="summarySubtotal" class="font-bold text-gray-800">0.00 DKK</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">VAT:</span>
                                        <span id="summaryVAT" class="font-bold text-gray-800">0.00 DKK</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Discount:</span>
                                        <span id="summaryDiscount" class="font-bold text-gray-800">0.00 DKK</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-semibold text-gray-800">Total:</span>
                                        <span id="summaryTotal" class="text-2xl font-bold text-blue-600">0.00 DKK</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mt-6">
                                <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow hover:bg-blue-700 transition">
                                    Generate Report
                                </button>
                            </div>
                            
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update Summary Function
        function updateSummary() {
            let selectedTasks = document.querySelectorAll('input[name="selected_tasks[]"]:checked');
            let summary = document.getElementById('summary');
            summary.innerHTML = '';
            
            // Initialize subtotal
            let subtotal = 0;
    
            selectedTasks.forEach(task => {
                let label = task.parentElement.innerText;
                let priceMatch = label.match(/([\d,]+\.\d{2})\s*DKK/);
                let price = priceMatch ? parseFloat(priceMatch[1].replace(',', '')) : 0;
    
                // Add price to subtotal
                subtotal += price;
    
                // Add task label to summary list with enhanced formatting
                summary.innerHTML += `<li class="mb-2 border-b border-gray-200 pb-1">${label}</li>`;
            });
    
            // Calculate VAT and discount with default values if fields are empty or invalid
            let vatInput = document.querySelector('input[name="vat"]');
            let discountInput = document.querySelector('input[name="discount"]');
            
            let vat = vatInput && vatInput.value ? Math.min(Math.max(parseFloat(vatInput.value), 0), 100) : 0;
            let discount = discountInput && discountInput.value ? Math.min(Math.max(parseFloat(discountInput.value), 0), 100) : 0;
    
            // Calculate total with VAT and Discount
            let totalWithVAT = subtotal * (1 + vat / 100);
            let totalWithVATAndDiscount = totalWithVAT * (1 - discount / 100);
    
            // Update the summary section totals dynamically
            document.getElementById('summarySubtotal').innerText = subtotal.toFixed(2) + ' DKK';
            document.getElementById('summaryVAT').innerText = (subtotal * (vat / 100)).toFixed(2) + ' DKK';
            document.getElementById('summaryDiscount').innerText = (totalWithVAT * (discount / 100)).toFixed(2) + ' DKK';
            document.getElementById('summaryTotal').innerText = totalWithVATAndDiscount.toFixed(2) + ' DKK';
        }

        function validatePercentage(input) {
            if (input.value < 0) input.value = 0;
            if (input.value > 100) input.value = 100;
        }
    
        // Function to toggle VAT field and set default VAT value to 25 if enabled
        function toggleVAT() {
            const vatField = document.getElementById('vatField');
            const vatInput = document.getElementById('vatInput');
            const vatToggle = document.getElementById('vatToggle');
            
            vatField.classList.toggle('hidden', !vatToggle.checked);
            vatInput.readOnly = !vatToggle.checked;
    
            if (vatToggle.checked) {
                vatInput.value = vatInput.value || 25; // Set to 25 if not already set
            } else {
                vatInput.value = ''; // Clear if disabled
            }
            
            updateSummary(); // Recalculate totals with or without VAT
        }
    
        // Attach event listeners for task selection, VAT, and Discount changes
        document.querySelectorAll('input[name="selected_tasks[]"]').forEach(task => {
            task.addEventListener('change', updateSummary);
        });
    
        let vatInput = document.querySelector('input[name="vat"]');
        let discountInput = document.querySelector('input[name="discount"]');
        
        if (vatInput) vatInput.addEventListener('input', () => {
            vatInput.value = Math.min(Math.max(vatInput.value, 0), 100) || 0; // Ensure VAT is between 0-100
            updateSummary();
        });
        if (discountInput) discountInput.addEventListener('input', () => {
            discountInput.value = Math.min(Math.max(discountInput.value, 0), 100) || 0; // Ensure Discount is between 0-100
            updateSummary();
        });
    
        // Initial call to populate summary and totals
        updateSummary();
    
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const toggleIcon = document.getElementById(sectionId + 'ToggleIcon');
    
            // Check if the section exists
            if (section) {
                // Toggle the display of the section
                if (section.style.display === 'none' || section.style.display === '') {
                    section.style.display = 'grid'; // Show the section
                    if (toggleIcon) {
                        toggleIcon.innerHTML = '&minus;'; // Update icon if toggleIcon exists
                    }
                } else {
                    section.style.display = 'none'; // Hide the section
                    if (toggleIcon) {
                        toggleIcon.innerHTML = '&plus;'; // Update icon if toggleIcon exists
                    }
                }
            } else {
                console.warn(`Element with ID ${sectionId} not found.`);
            }
        }
    
        // Initialize headerOfferSection as open by default if it exists
        const headerOfferSection = document.getElementById('headerOfferSection');
        if (headerOfferSection) {
            headerOfferSection.style.display = 'grid';
        } else {
            console.warn("Element with ID 'headerOfferSection' not found.");
        }
    </script>    
</x-app-layout>
