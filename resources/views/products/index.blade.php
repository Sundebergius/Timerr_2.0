<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Products
        </h2>
    </x-slot>

    <div class="py-12" id="app" data-user-id="{{ auth()->id() }}" data-team-id="{{ auth()->user()->currentTeam->id }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- <button type="button" @click="showModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i> Create New Product
                    </button> --}}

                    <!-- Product Counter -->
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

                        <!-- Create Product Button -->
                        <button type="button" @click="showModal = true"
                            class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out {{ $productCount >= $productLimit ? 'opacity-50 cursor-not-allowed' : '' }}"
                            @if ($productCount >= $productLimit) disabled @endif>
                            <i class="fas fa-plus mr-2"></i> Create New Product
                        </button>

                        <!-- CTA for Upgrade when Product Limit is Reached -->
                        @if ($productCount >= $productLimit)
                            <div class="mt-6 bg-yellow-100 p-4 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold text-yellow-800">Need more products?</h3>
                                <p class="text-yellow-600">Upgrade to the Freelancer plan to create up to 15 products and unlock advanced features.</p>
                                <a href="{{ route('stripe.checkout', ['plan' => 'freelancer']) }}" class="mt-4 inline-block bg-yellow-500 text-white py-2 px-6 rounded-lg shadow hover:bg-yellow-600">Upgrade Now</a>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Product Modal -->
                    <product-modal v-if="showModal" :user-id="userId" :team-id="teamId" @close="showModal = false"
                        @product-created="handleProductCreated"></product-modal>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <!-- Title with larger and darker font -->
                                                <dt class="text-lg font-bold text-gray-900">
                                                    {{ $product->title }} ({{ ucfirst($product->type) }})
                                                </dt>
                                                {{-- <dd>
                                                    <div class="text-lg font-medium text-gray-900">
                                                        {{ $product->description ?? 'No description provided' }}
                                                    </div>
                                                </dd> --}}

                                                <!-- Conditional display for product vs service -->
                                                @if($product->type == 'product')
                                                    <dd class="mt-2 text-sm text-gray-500">
                                                        Price: ${{ $product->price }}
                                                    </dd>
                                                    <dd class="mt-2 text-sm text-gray-500">
                                                        Quantity in Stock: {{ $product->quantity_in_stock }}
                                                    </dd>
                                                    <dd class="mt-2 text-sm text-gray-500">
                                                        Quantity Sold: {{ $product->quantity_sold }}
                                                    </dd>
                                                    @elseif($product->type == 'service')
                                                    <dd class="mt-2 text-sm text-gray-500">
                                                        Attributes:
                                                        <ul>
                                                            @if($product->attributes)
                                                                @php
                                                                    $attributes = is_string($product->attributes)
                                                                        ? json_decode($product->attributes, true) // Decode as associative array
                                                                        : $product->attributes;
                                                                @endphp
                                                                @foreach($attributes as $attribute)
                                                                    <li>{{ $attribute['key'] ?? 'N/A' }}: {{ $attribute['value'] ?? 'N/A' }}</li>
                                                                @endforeach
                                                            @else
                                                                <li>No attributes available</li>
                                                            @endif
                                                        </ul>
                                                    </dd>
                                                @endif

                                                <dd class="mt-2 text-sm">
                                                    Status: 
                                                    @if($product->active)
                                                        <span class="badge bg-success-200 text-dark">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-200 text-dark">Inactive</span>
                                                    @endif
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5 flex justify-end space-x-4">
                                    <!-- Edit button -->
                                    <a href="{{ route('products.edit', $product->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                                        <i class="fas fa-edit mr-2"></i> Edit
                                    </a>
                    
                                    <!-- Delete button -->
                                    <form method="POST" action="{{ route('products.destroy', $product->id) }}" onsubmit="return confirmDelete();">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                                            <i class="fas fa-trash mr-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Page size dropdown -->
                    <form method="GET" action="{{ route('products.index') }}" style="flex-grow: 1; text-align: right;">
                        <div class="inline-block relative w-32">
                            <select name="pageSize" onchange="this.form.submit()" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                                <option value="10" {{ request('pageSize') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('pageSize') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('pageSize') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('pageSize') == 100 ? 'selected' : '' }}>100</option>
                                <option value="all" {{ request('pageSize') == 'all' ? 'selected' : '' }}>All</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this product?');
        }
    </script>
</x-app-layout>
