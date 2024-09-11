<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Product
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('products.update', $product->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <label for="title">Title</label>
                            <input id="title" class="block mt-1 w-full" type="text" name="title" value="{{ $product->title }}" required autofocus />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <label for="description">Description</label>
                            <input id="description" class="block mt-1 w-full" type="text" name="description" value="{{ $product->description }}" />
                        </div>

                        <!-- Type -->
                        <div class="mt-4">
                            <label for="type">Type</label>
                            <select id="type" name="type" class="block mt-1 w-full">
                                <option value="product" {{ $product->type == 'product' ? 'selected' : '' }}>Product</option>
                                <option value="service" {{ $product->type == 'service' ? 'selected' : '' }}>Service</option>
                            </select>
                        </div>

                        <!-- Price -->
                        <div class="mt-4">
                            <label for="price">Price</label>
                            <input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" value="{{ $product->price }}" required />
                        </div>

                        <!-- Quantity in Stock -->
                        <div class="mt-4">
                            <label for="quantityInStock">Quantity in Stock</label>
                            <input id="quantityInStock" class="block mt-1 w-full" type="number" name="quantity_in_stock" value="{{ $product->quantity_in_stock }}" required />
                        </div>

                        <!-- Quantity Sold -->
                        <div class="mt-4">
                            <label for="quantitySold">Quantity Sold</label>
                            <input id="quantitySold" class="block mt-1 w-full" type="number" name="quantity_sold" value="{{ $product->quantity_sold }}" required />
                        </div>

                        <!-- Status (Active) -->
                        <div class="mt-4">
                            <label for="active">Status</label>
                            <p class="text-sm text-gray-600">Check this box if the product is active. Uncheck it if the product is not active.</p>
                            <input id="active" class="block mt-1" type="checkbox" name="active" value="1" {{ $product->active ? 'checked' : '' }} />
                        </div>

                        <!-- Attributes -->
                        <div class="mt-4">
                            <label for="attributes">Attributes</label>
                            <p class="text-sm text-gray-600">Add or edit key-value pairs for attributes (e.g., size, color).</p>

                            <!-- Attributes List -->
                            <div id="attributes-list">
                                @foreach($product->attributes as $key => $value)
                                    <div class="flex items-center mt-2">
                                        <input type="text" name="attributes[{{ $key }}][key]" value="{{ $key }}" placeholder="Key" class="block w-1/3 mr-2" />
                                        <input type="text" name="attributes[{{ $key }}][value]" value="{{ $value }}" placeholder="Value" class="block w-1/3 mr-2" />
                                        <button type="button" class="remove-attribute bg-red-500 text-white px-2 py-1">Remove</button>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Add Attribute Button -->
                            <div class="mt-4">
                                <button type="button" id="add-attribute" class="bg-blue-500 text-white px-4 py-2">Add Attribute</button>
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
            </div>
        </div>
    </div>

    <script>
        document.getElementById('add-attribute').addEventListener('click', function() {
            const attributesList = document.getElementById('attributes-list');
            const newAttributeRow = document.createElement('div');
            newAttributeRow.classList.add('flex', 'items-center', 'mt-2');
            newAttributeRow.innerHTML = `
                <input type="text" name="attributes[]" placeholder="Key" class="block w-1/3 mr-2" />
                <input type="text" name="attributes[]" placeholder="Value" class="block w-1/3 mr-2" />
                <button type="button" class="remove-attribute bg-red-500 text-white px-2 py-1">Remove</button>
            `;
            attributesList.appendChild(newAttributeRow);
        });

        document.getElementById('attributes-list').addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-attribute')) {
                event.target.parentElement.remove();
            }
        });
    </script>
</x-app-layout>
