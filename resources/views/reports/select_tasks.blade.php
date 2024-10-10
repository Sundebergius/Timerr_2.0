<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Tasks for Project Report: ') . $project->title }}
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
                    <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">
                        Customize and Select Tasks for Project Report: {{ $project->title }}
                    </h1>

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
                                    <label class="block text-sm font-medium text-gray-700">Report Title</label>
                                    <input type="text" name="report_title" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        value="Project Report: {{ $project->title }}">
                                </div>

                                <!-- Client Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client Name</label>
                                    <input type="text" name="client_name" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                                        value="{{ $project->client->name ?? 'Test Client' }}">
                                </div>

                                <!-- Client Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client Email</label>
                                    <input type="email" name="client_email" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        value="{{ $project->client->email ?? 'Test@client.dk' }}">
                                </div>

                                <!-- Report Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Report Date</label>
                                    <input type="date" name="report_date" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        value="{{ now()->toDateString() }}">
                                </div>

                                <!-- Project ID -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Project ID</label>
                                    <input type="text" name="project_id" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        value="#{{ $project->id }}">
                                </div>
                            </div>
                        


                            <!-- Paid Features Section (Limited for Free Users) -->
                            <div class="mb-8">
                                <!-- Conditional Heading for Paid Features -->
                                <h3 class="text-lg font-semibold text-blue-600 mt-8 mb-4 border-b-2 border-blue-500 pb-2">
                                    Additional Customization Options
                                    <span class="text-sm text-gray-500">
                                        @if($subscriptionPlan === 'free')
                                            (Upgrade for more options)
                                        @else
                                            (Available on Paid Plans)
                                        @endif
                                    </span>
                                </h3>

                                <div id="paidFeaturesSection" class="mt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                        <!-- Upload Logo (Disabled for Free Users) -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Upload Logo</label>
                                            <input type="file" name="report_logo" accept="image/*" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                                {{ $subscriptionPlan === 'free' ? 'disabled' : '' }}
                                                title="{{ $subscriptionPlan === 'free' ? 'Available on paid plans only' : '' }}">
                                        </div>

                                        <!-- VAT Toggle and Field -->
                                        <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                            <input type="checkbox" id="vatToggle" class="h-5 w-5 text-blue-600 border-gray-300 rounded" 
                                                onchange="toggleVAT()" 
                                                {{ $subscriptionPlan === 'free' ? 'disabled' : '' }}
                                                title="{{ $subscriptionPlan === 'free' ? 'Available on paid plans only' : '' }}">
                                            <span class="text-gray-700 font-medium">Enable VAT</span>
                                        </div>

                                        <div id="vatField" class="hidden">
                                            <label class="block text-sm font-medium text-gray-700">VAT (%)</label>
                                            <input type="number" name="vat" id="vatInput" step="0.01" placeholder="Enter VAT percentage" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                                readonly title="Enable VAT to edit this field">
                                        </div>

                                        <!-- Discount Field (Disabled for Free Users) -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Discount (%)</label>
                                            <input type="number" name="discount" step="0.01" placeholder="Enter discount percentage"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                                {{ $subscriptionPlan === 'free' ? 'readonly' : '' }}
                                                title="{{ $subscriptionPlan === 'free' ? 'Available on paid plans only' : '' }}">
                                        </div>

                                        <!-- Total with VAT and Discount (Display Only) -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Total with VAT and Discount</label>
                                            <input type="text" id="total_with_vat_discount" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                                        </div>
                                    </div>

                                    <!-- Watermark Section (Always Checked and Disabled for Free Users) -->
                                    <div class="mb-8">
                                        <label class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                            <input type="checkbox" name="add_watermark" value="1" 
                                                class="h-5 w-5 text-blue-600 border-gray-300 rounded mt-1" 
                                                {{ $subscriptionPlan === 'free' ? 'checked disabled' : '' }}>
                                            <span>
                                                <span class="text-gray-700 font-medium">Add "Created by Timerr" Watermark</span>
                                                <p class="text-gray-500 text-sm mt-1">
                                                    {{ $subscriptionPlan === 'free' ? 'Watermark is mandatory on free plans. Upgrade to remove it.' : 'Optional watermark for paid plans.' }}
                                                </p>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    

                        <!-- Summary Preview -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-gray-700">Selected Tasks Summary</h2>
                            <ul id="summary" class="text-gray-600 bg-gray-100 p-4 rounded-md border border-gray-300" style="min-height: 150px;"></ul>
                            <div class="mt-4 bg-gray-50 p-4 rounded-md text-right border border-gray-300">
                                <p class="text-2xl text-left font-bold text-gray-800">Total: <span id="summaryTotal" class="text-gray-900">0.00 DKK</span></p>
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

                            <!-- Include Notes Section -->
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold text-gray-700 mb-4">Additional Information</h2>
                                <div class="grid grid-cols-1 gap-4">

                                    <!-- Include Notes Checkbox -->
                                    <label class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                        <input type="checkbox" name="include_notes" value="1" class="h-5 w-5 text-blue-600 border-gray-300 rounded mt-1">
                                        <span>
                                            <span class="text-gray-700 font-medium">Include Notes Section</span>
                                            <p class="text-gray-500 text-sm mt-1">Add an additional notes section in the report for customized details.</p>
                                            <textarea name="notes_content" class="mt-2 w-full border-gray-300 rounded-md shadow-sm" placeholder="Enter additional notes here..."></textarea>
                                        </span>
                                    </label>

                                    <!-- Include Signature Checkbox -->
                                    <label class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition mt-2">
                                        <input type="checkbox" name="include_signature" value="1" class="h-5 w-5 text-blue-600 border-gray-300 rounded mt-1">
                                        <span>
                                            <span class="text-gray-700 font-medium">Include Signature Section</span>
                                            <p class="text-gray-500 text-sm mt-1">Add a client and service provider signature section to the report for formal approvals.</p>
                                        </span>
                                    </label>
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
                // Extract task price from label with a more flexible regex pattern
                let priceMatch = label.match(/([\d,]+\.\d{2})\s*DKK/);
                let price = priceMatch ? parseFloat(priceMatch[1].replace(',', '')) : 0;
    
                // Log price for debugging purposes
                console.log(`Parsed Price: ${price} from Label: ${label}`);
    
                // Add price to subtotal
                subtotal += price;
    
                // Add task label to summary list with enhanced formatting
                summary.innerHTML += `<li class="mb-2 border-b border-gray-200 pb-1">${label}</li>`;
            });
    
            // Calculate VAT and discount if available (only for paid users)
            let vatInput = document.querySelector('input[name="vat"]');
            let discountInput = document.querySelector('input[name="discount"]');
            
            let vat = vatInput ? parseFloat(vatInput.value) : 0;
            let discount = discountInput ? parseFloat(discountInput.value) : 0;
    
            // Calculate total with VAT and Discount
            let totalWithVAT = subtotal * (1 + vat / 100);
            let totalWithVATAndDiscount = totalWithVAT * (1 - discount / 100);
    
            // Update the total field if it exists
            let totalField = document.getElementById('total_with_vat_discount');
            if (totalField) {
                totalField.value = totalWithVATAndDiscount.toFixed(2);
            }
    
            // Update subtotal display in the summary section
            let subtotalDisplay = document.getElementById('summaryTotal');
            if (subtotalDisplay) {
                subtotalDisplay.innerText = subtotal.toFixed(2) + ' DKK';
            }
        }
    
        // Attach event listeners for task selection, VAT, and Discount changes
        document.querySelectorAll('input[name="selected_tasks[]"]').forEach(task => {
            task.addEventListener('change', updateSummary);
        });
    
        let vatInput = document.querySelector('input[name="vat"]');
        let discountInput = document.querySelector('input[name="discount"]');
        
        if (vatInput) vatInput.addEventListener('input', updateSummary);
        if (discountInput) discountInput.addEventListener('input', updateSummary);
    
        // Initial call to populate summary and totals
        updateSummary();
    
        function toggleVAT() {
            const vatField = document.getElementById('vatField');
            const vatInput = document.getElementById('vatInput');
            if (document.getElementById('vatToggle').checked) {
                vatField.classList.remove('hidden');
                vatInput.removeAttribute('readonly');
                vatInput.placeholder = "Enter VAT percentage";
                updateSummary(); // Recalculate totals with VAT
            } else {
                vatField.classList.add('hidden');
                vatInput.setAttribute('readonly', 'true');
                vatInput.value = '';
                updateSummary(); // Recalculate totals without VAT
            }
        }

        // Toggle Section Visibility Function
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const toggleIcon = document.getElementById(sectionId + 'ToggleIcon');
            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'grid'; // or 'block' depending on layout
                toggleIcon.innerHTML = '&minus;';
            } else {
                section.style.display = 'none';
                toggleIcon.innerHTML = '&plus;';
            }
        }
    
        // Initialize headerSection as open by default
        document.getElementById('headerSection').style.display = 'grid';
    </script>    
</x-app-layout>
