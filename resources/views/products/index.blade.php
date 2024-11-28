<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Products & Services Overview
        </h2>
    </x-slot>

    <!-- Consolidated Alert Styling -->
    <style>
        /* Alert Styling */
        .alert {
            display: flex;
            align-items: start;
            position: relative;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: opacity 0.3s ease-in-out;
        }

        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .alert-warning { background-color: #fff3cd; border-color: #ffeeba; color: #856404; }
        .alert-error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }

        .alert button {
            background: transparent;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert svg {
            height: 1.5rem;
            width: 1.5rem;
            transition: transform 0.2s;
        }

        .alert svg:hover { transform: scale(1.1); }

        /* Confirmation Link/Button Styling */
        .delete-warning {
            display: inline-block;
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        .delete-warning button.text-red-500 {
            color: #c53030;
            font-weight: bold;
            margin-left: 5px;
            text-decoration: underline;
        }
    </style>

    <!-- Alert Messages -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                <span class="block sm:inline font-semibold">{{ session('success') }}</span>
                <button type="button" onclick="this.closest('.alert').remove()">
                    <svg class="fill-current h-6 w-6 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                        <title>Close</title>
                        <path d="M6 6L14 14M14 6L6 14"></path>
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error" role="alert">
                <span class="block sm:inline font-semibold">{{ session('error') }}</span>
                <button type="button" onclick="this.closest('.alert').remove()">
                    <svg class="fill-current h-6 w-6 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                        <title>Close</title>
                        <path d="M6 6L14 14M14 6L6 14"></path>
                    </svg>
                </button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning flex items-start" role="alert">
                <div class="flex-1">
                    <!-- Warning Message with Confirmation Link -->
                    <span class="block sm:inline font-semibold">{!! session('warning') !!}</span>
                </div>
                <button type="button" class="p-2 focus:outline-none" onclick="this.closest('.alert').remove()">
                    <svg class="fill-current h-6 w-6 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                        <title>Close</title>
                        <path d="M6 6L14 14M14 6L6 14"></path>
                    </svg>
                </button>
            </div>        
        @endif
    </div>

    <div class="py-12" id="app" data-user-id="{{ auth()->id() }}" data-team-id="{{ auth()->user()->currentTeam->id }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- <button type="button" @click="showModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i> Create New Product
                    </button> --}}

                    <div class="text-center mb-8">
                        <h1 class="text-4xl font-bold text-blue-500 mb-4">Products</h1>
                    
                        <!-- Product Counter -->
                        <div class="mb-6">
                            <p class="text-lg font-semibold text-gray-800">
                                You have created <span class="text-blue-500">{{ $productCount }}</span> out of <span class="text-blue-500">{{ $productLimit }}</span> products.
                            </p>
                    
                            @if ($productCount < $productLimit)
                                <p class="text-green-500">You can create {{ $productLimit - $productCount }} more products.</p>
                            @else
                                <p class="text-red-500">You have reached your product limit.</p>
                            @endif
                        </div>
                    
                        <!-- Button Group -->
                        <div class="flex justify-center space-x-4">
                            <!-- Create Product Button -->
                            <button
                                type="button"
                                @click="showModal = true"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out {{ $productCount >= $productLimit ? 'opacity-50 cursor-not-allowed' : '' }}"
                                @if ($productCount >= $productLimit) disabled @endif>
                                <i class="fas fa-plus mr-2"></i> Create New Product
                            </button>
                    
                            <!-- Link Parent Materials Button -->
                            <button
                                type="button"
                                @click="openLinkMaterialsModal({{ $products->first()->id ?? 'null' }})"
                                class="bg-purple-500 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                                <i class="fas fa-link mr-2"></i> Link Parent Materials
                            </button>
                        </div>
                    </div>
                    
                    <!-- Product Modal -->
                    <product-modal
                        v-if="showModal"
                        :user-id="userId"
                        :team-id="teamId"
                        @close="showModal = false"
                        @product-created="handleProductCreated">
                    </product-modal>
                    
                    <!-- Link Materials Modal -->
                    <link-materials-modal
                        v-if="showLinkMaterialsModal"
                        :product-id="selectedProductId"
                        @close="showLinkMaterialsModal = false"
                        @materials-linked="refreshPage">
                    </link-materials-modal>
                    
                    
                        
                        

                        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($products as $product)
                            <div class="relative bg-white overflow-hidden shadow-lg rounded-lg p-5 transition-shadow hover:shadow-md cursor-pointer"
                                    onclick="toggleCardExpansion({{ $product->id }})">
                       
                                    
                                    <!-- Badge for Type -->
                                    <span class="absolute top-0 right-0 mt-4 mr-4 px-2 py-1 text-sm font-semibold rounded-full
                                        {{ 
                                            $product->type === 'product' ? 'bg-blue-200 text-blue-800' : 
                                            ($product->type === 'service' ? 'bg-green-200 text-green-800' : 
                                            'bg-yellow-200 text-yellow-800') 
                                        }}">
                                        {{ ucfirst($product->type) }}
                                    </span>
                        
                                    <!-- Title and Description -->
                                    <div class="mb-4">
                                        <h3 class="text-xl font-bold text-gray-800">{{ $product->title }}</h3>
                                        <p class="text-gray-600">{{ $product->description ?? 'No description available.' }}</p>
                                    </div>

                                    <!-- Materials Linked to Product or Service -->
                                    @if($product->type === 'product' || $product->type === 'service')
                                    <div class="mb-4 text-sm">
                                        <p class="font-semibold text-gray-700">Materials:</p>
                                        <ul class="list-disc ml-4 mt-2">
                                            @foreach ($product->materials as $material)
                                            <li class="mt-2">
                                                <span class="font-bold">{{ $material->title }}</span>
                                                <span class="text-gray-500">({{ $material->quantity_in_stock }} {{ $material->unit_type }})</span>
                                                <span class="text-gray-400 ml-2">Usage: {{ $material->usage_per_unit }} per unit</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    <!-- Parent Material and Children -->
                                    @if($product->is_parent_material)
                                    <div class="mt-4">
                                        <p class="font-semibold text-gray-700">Parent Material:</p>
                                        <ul class="mt-2 space-y-2">
                                            @foreach ($product->children as $child)
                                            <li class="flex justify-between items-center bg-gray-50 p-2 rounded-lg">
                                                <div>
                                                    <span class="font-bold">{{ $child->title }}</span>
                                                    <span class="text-gray-500 ml-2">({{ $child->quantity_in_stock }} {{ $child->unit_type }})</span>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Cost: ${{ $child->cost_per_unit }}, Price: ${{ $child->price_per_unit }}
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                        
                                    <!-- Product-Specific Details with Stock Progress Bar -->
                                    @if($product->type === 'product')
                                        <div class="mb-2 text-sm">
                                            <!-- Price Section -->
                                            @php
                                                $materialPrice = ($product->price == 0 || $product->price == null) && $product->materials && $product->materials->isNotEmpty()
                                                    ? $product->materials->sum(function($material) {
                                                        return $material->price_per_unit * $material->usage_per_unit;
                                                    })
                                                    : $product->price;
                                            @endphp
                                            <p>Price: <span class="font-semibold">${{ $materialPrice }}</span></p>

                                            <!-- Stock Section -->
                                            @php
                                                $productStock = $product->quantity_in_stock > 0 
                                                    ? $product->quantity_in_stock 
                                                    : ($product->materials && $product->materials->isNotEmpty()
                                                        ? $product->materials->min(function($material) {
                                                            return intval($material->quantity_in_stock / max($material->usage_per_unit, 1));
                                                        })
                                                        : 0);
                                                
                                                // Calculate quantity sold from TaskProducts if available
                                                $quantitySold = \App\Models\TaskProduct::where('product_id', $product->id)->sum('quantity') ?? 0;
                                            @endphp
                                            <p>Quantity in Stock: <span class="font-semibold">{{ $productStock }}</span></p>
                                            <p>Quantity Sold: <span class="font-semibold">{{ $quantitySold }}</span></p>
                                        </div>

                                        <!-- Stock Progress Bar -->
                                        @if($product->manage_inventory)
                                            <div class="w-full bg-gray-300 rounded-full h-2.5 mb-4">
                                                <div class="bg-blue-500 h-2.5 rounded-full"
                                                    style="width: {{ ($quantitySold / max(1, $productStock)) * 100 }}%">
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    <!-- Service-Specific Attributes -->
                                    @if($product->type === 'service')
                                        <div class="mb-2 text-sm text-gray-700">
                                            <!-- Base Price -->
                                            @if($product->price > 0)
                                                <p><strong>Base Price:</strong> ${{ number_format($product->price, 2) }}</p>
                                            @endif

                                            <p><strong>Service Options:</strong></p>
                                            <ul class="list-disc ml-4">
                                                @php
                                                    // Decode service attributes if stored as a JSON string
                                                    $attributes = is_string($product->attributes)
                                                        ? json_decode($product->attributes, true)
                                                        : $product->attributes;

                                                    // Fetch task products and initialize totals
                                                    $taskProducts = \App\Models\TaskProduct::where('product_id', $product->id)->get();
                                                    $totalQuantitySold = 0;
                                                    $totalRevenue = 0; // Initialize at zero to add base price once per taskProduct

                                                    // Loop over each task product
                                                    foreach ($taskProducts as $taskProduct) {
                                                        $taskAttributes = json_decode($taskProduct->attributes, true) ?? [];
                                                        $productTotal = $product->price * $taskProduct->quantity; // Base price calculation for each sold unit

                                                        // Add revenue from each attribute in the task product
                                                        foreach ($taskAttributes as $taskAttribute) {
                                                            $attributeRevenue = ($taskAttribute['price'] ?? 0) * ($taskAttribute['quantity'] ?? 0);
                                                            $productTotal += $attributeRevenue;
                                                        }

                                                        // Accumulate totals
                                                        $totalRevenue += $productTotal;
                                                    }
                                                @endphp

                                                <!-- Display each service option with price and quantity sold -->
                                                @foreach($attributes as $attribute)
                                                    @php
                                                        // Calculate total quantity sold for each attribute across all taskProducts
                                                        $attributeKey = $attribute['key'] ?? '';
                                                        $attributeQuantitySold = $taskProducts->reduce(function ($carry, $taskProduct) use ($attributeKey) {
                                                            $taskAttributes = json_decode($taskProduct->attributes, true) ?? [];
                                                            foreach ($taskAttributes as $taskAttribute) {
                                                                if ($taskAttribute['attribute'] == $attributeKey) {
                                                                    $carry += $taskAttribute['quantity'];
                                                                }
                                                            }
                                                            return $carry;
                                                        }, 0);

                                                        $totalQuantitySold += $attributeQuantitySold;
                                                    @endphp
                                                    <li>
                                                        {{ $attributeKey }}: {{ number_format($attribute['value'] ?? 0, 2) }}
                                                        <span class="text-gray-500">(Sold: {{ $attributeQuantitySold }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <!-- Display total quantity sold and revenue for the service -->
                                            <p class="mt-2 font-semibold">Total Quantity Sold: {{ $totalQuantitySold }}</p>
                                            <p class="font-semibold">Total Revenue: {{ number_format($totalRevenue, 2) }}</p>
                                        </div>
                                    @endif

                                    <!-- Status -->
                                    <p class="mt-4 text-sm">
                                        Status: 
                                        <span class="{{ $product->active ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $product->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                        
                                    <!-- Expandable Analytics Section -->
                                    <div id="analytics-{{ $product->id }}" class="hidden mt-4">
                                        <!-- Chart Container -->
                                        <canvas id="chart-{{ $product->id }}" class="mb-4"></canvas>
                                        <p>Total Revenue: ${{ $product->price * $product->quantity_sold }}</p> <!-- Display total revenue -->
                                    </div>
                        
                                    <!-- Actions -->
                                    <div class="mt-5 flex justify-between">
                                        <!-- Edit button with stopPropagation -->
                                        <a href="{{ route('products.edit', $product->id) }}" 
                                        onclick="event.stopPropagation()"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700">
                                            Edit
                                        </a>

                                        <!-- Delete form with stopPropagation on button -->
                                        <form method="POST" action="{{ route('products.destroy', $product->id) }}" onsubmit="return confirmDelete();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="event.stopPropagation()"
                                                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        

                    @if ($products->total() > 10)
                        <!-- Pagination links and Page size dropdown -->
                        <div class="mt-6 flex items-center justify-between">
                            {{ $products->links() }}
                            <form method="GET" action="{{ route('products.index') }}" style="flex-grow: 1; text-align: right;">
                                <div class="inline-block relative w-32">
                                    <select name="pageSize" onchange="this.form.submit()" 
                                            class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="10" {{ request('pageSize') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('pageSize') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('pageSize') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('pageSize') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="all" {{ request('pageSize') == 'all' ? 'selected' : '' }}>All</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.alert-success').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        document.querySelectorAll('.alert-warning').forEach(alert => {
            alert.querySelector('button').addEventListener('click', () => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        });
    });

    // Product and Service data array from Laravel
    const products = {!! json_encode($products->items()) !!};
    
    // Track created charts by product ID
    const charts = {};

    // Function to toggle card expansion and render chart
    function toggleCardExpansion(productId) {
        const analyticsSection = document.getElementById(`analytics-${productId}`);
        if (analyticsSection.classList.contains('hidden')) {
            analyticsSection.classList.remove('hidden');
            renderChart(productId); // Render chart on first expansion
        } else {
            analyticsSection.classList.add('hidden');
            // Destroy chart if it exists to free up canvas
            if (charts[productId]) {
                charts[productId].destroy();
                delete charts[productId]; // Remove from tracking
            }
        }
    }

    // Render chart based on product/service type
    function renderChart(productId) {
        const ctx = document.getElementById(`chart-${productId}`).getContext('2d');
        const productData = products.find(product => product.id === productId);

        if (!productData) return; // Check if the productData exists to prevent errors

        // Destroy existing chart instance on this canvas before creating a new one
        if (charts[productId]) {
            charts[productId].destroy();
        }

        const isProduct = productData.type === 'product';
        const chartData = {
            labels: isProduct ? ['Sold', 'In Stock'] : ['Sold'],
            datasets: [{
                label: 'Quantity',
                data: isProduct ? [productData.quantity_sold, productData.quantity_in_stock] : [productData.quantity_sold],
                backgroundColor: ['#4caf50', '#f44336']
            }]
        };

        charts[productId] = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: `${productData.title} Analytics`
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (isProduct && context.dataIndex === 0) {
                                    return `Total Revenue: $${productData.price * productData.quantity_sold}`;
                                }
                                return `${context.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    }
                }
            }
        });
    }

    function confirmDelete() {
        return confirm('Are you sure you want to delete this product?');
    }
</script>
</x-app-layout>
