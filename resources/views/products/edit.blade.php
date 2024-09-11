<x-app-layout>
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <h1 class="text-4xl font-bold mb-10">Edit Product</h1>

        <form method="POST" action="{{ route('products.update', $product->id) }}" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                    Title
                </label>
                <input id="title" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="title" value="{{ $product->title }}" required autofocus />
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Description
                </label>
                <input id="description" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="description" value="{{ $product->description }}" />
            </div>

            <!-- Type (Read-only) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
                    Type
                </label>
                <input id="type" name="type" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight bg-gray-200" value="{{ ucfirst($product->type) }}" readonly />
                <input type="hidden" name="type" value="{{ $product->type }}">
            </div>

            <!-- Price -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
                    Price
                </label>
                <input id="price" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="number" step="0.01" name="price" value="{{ $product->price }}" required />
            </div>

            <!-- Quantity in Stock -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="quantityInStock">
                    Quantity in Stock
                </label>
                <input id="quantityInStock" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="number" name="quantity_in_stock" value="{{ $product->quantity_in_stock }}" required />
            </div>

            <!-- Quantity Sold -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="quantitySold">
                    Quantity Sold
                </label>
                <input id="quantitySold" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="number" name="quantity_sold" value="{{ $product->quantity_sold }}" required />
            </div>

            <!-- Status (Active) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="active">
                    Status
                </label>
                <p class="text-sm text-gray-600">Check this box if the product is active. Uncheck it if the product is not active.</p>
                <input id="active" class="block mt-1" type="checkbox" name="active" value="1" {{ $product->active ? 'checked' : '' }} />
            </div>

            <!-- Attributes -->
            <div class="mb-4">
                <label for="attributes">Attributes</label>

                <!-- Show the paragraph only if there are attributes -->
                @if (count($product->attributes) > 0)
                    <p class="text-sm text-gray-600">Add or edit key-value pairs for attributes (e.g., size, color).</p>
                @endif

                <!-- Attributes List -->
                <div id="attributes-list">
                    @forelse($product->attributes as $index => $attribute)
                        <div class="flex items-center mt-2">
                            <input type="text" name="attributes[{{ $index }}][key]" value="{{ $attribute['key'] ?? '' }}" placeholder="Key" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            <input type="text" name="attributes[{{ $index }}][value]" value="{{ $attribute['value'] ?? '' }}" placeholder="Value" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            <button type="button" class="remove-attribute bg-red-500 text-white px-2 py-1 rounded">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        </div>
                    @empty
                        <p>No attributes available. Add one below:</p>
                    @endforelse
                </div>

                <!-- Add Attribute Button -->
                <div class="mt-4">
                    <button type="button" id="add-attribute" class="bg-blue-500 text-white px-4 py-2 rounded">Add Attribute</button>
                </div>
            </div>

            <!-- Update Button -->
            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-edit mr-2"></i> Update
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let index = @json(count($product->attributes)); // Initial index

            document.getElementById('add-attribute').addEventListener('click', function() {
                let attributesList = document.getElementById('attributes-list');
                let newAttribute = document.createElement('div');
                newAttribute.className = 'flex items-center mt-2';
                newAttribute.innerHTML = `
                    <input type="text" name="attributes[${index}][key]" placeholder="Key" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <input type="text" name="attributes[${index}][value]" placeholder="Value" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <button type="button" class="remove-attribute bg-red-500 text-white px-2 py-1 rounded">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                `;
                attributesList.appendChild(newAttribute);
                index++;
            });

            document.getElementById('attributes-list').addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-attribute')) {
                    event.target.parentElement.remove();
                }
            });
        });
    </script>
</x-app-layout>
