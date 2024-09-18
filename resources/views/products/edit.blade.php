<x-app-layout>
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <h1 class="text-4xl font-bold mb-10">Edit {{ ucfirst($product->type) }}</h1>

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

            <!-- Description (only for type 'product') -->
            @if($product->type === 'product')
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Description
                    </label>
                    <input id="description" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="description" value="{{ $product->description }}" />
                </div>
            @endif

            <!-- Type (Read-only) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
                    Type
                </label>
                <input id="type" name="type" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight bg-gray-200" value="{{ ucfirst($product->type) }}" readonly />
                <input type="hidden" name="type" value="{{ $product->type }}">
            </div>

            <!-- Price (only for type 'product') -->
            @if($product->type === 'product')
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
            @endif

            <!-- Status (Active) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="active">
                    Status
                </label>
                <p class="text-sm text-gray-600">Check this box if the product is active. Uncheck it if the product is not active.</p>
                <input id="active" class="block mt-1" type="checkbox" name="active" value="1" {{ $product->active ? 'checked' : '' }} />
            </div>

            <!-- Attributes (only for type 'service') -->
            @if($product->type === 'service')
            <div class="mb-4">
                <label for="attributes">Attributes</label>

                <!-- Show the paragraph only if there are attributes -->
                @if (count($product->attributes ?? []) > 0)
                    <p class="text-sm text-gray-600">Add or edit key-value pairs for attributes (e.g., size, color).</p>
                @endif

                <div id="attributes-list">
                    @forelse($product->attributes ?? [] as $index => $attribute)
                        <div class="flex items-center mt-2 attribute-item" data-index="{{ $index }}">
                            <input type="text" name="attributes[{{ $index }}][key]" value="{{ $attribute['key'] ?? '' }}" placeholder="Key" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            <input type="text" name="attributes[{{ $index }}][value]" value="{{ $attribute['value'] ?? '' }}" placeholder="Value" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                            <button type="button" class="remove-attribute bg-red-500 text-white px-2 py-1 rounded">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                            <button type="button" class="undo-remove-attribute bg-green-500 text-white px-2 py-1 rounded hidden">
                                <i class="fas fa-undo"></i> Undo
                            </button>
                            <input type="hidden" name="attributes[{{ $index }}][delete]" value="0" class="delete-flag">
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
            @endif

            <!-- Update Button -->
            <div class="flex items-center justify-end mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-edit mr-2"></i> Update
            </button>
            </div>
        </form>
    </div>

    <style>
        .attribute-item {
            transition: background-color 0.3s, color 0.3s;
        }
    
        .attribute-item.deleting {
            background-color: #fdd;
            border: 1px solid #f99;
            color: #999;
            text-decoration: line-through;
        }
    
        /* Ensure remove-attribute button is hidden when deleting */
        .attribute-item.deleting .remove-attribute {
            display: none !important; /* Force hiding the remove button */
        }
    
        /* The undo button is visible only when deleting */
        .attribute-item.deleting .undo-remove-attribute {
            display: inline-block !important;
        }
    
        /* Initially, hide the undo button */
        .undo-remove-attribute {
            display: none;
        }
    </style>
    

<script>
document.addEventListener('DOMContentLoaded', function() {
    let attributesList = document.getElementById('attributes-list');
    
    if (attributesList) {
        let index = @json(count($product->attributes ?? [])); 

        // Add new attribute logic
        let addAttributeButton = document.getElementById('add-attribute');
        if (addAttributeButton) {
            addAttributeButton.addEventListener('click', function() {
                let newAttribute = document.createElement('div');
                newAttribute.className = 'flex items-center mt-2 attribute-item';
                newAttribute.setAttribute('data-index', index);
                newAttribute.innerHTML = `
                    <input type="text" name="attributes[${index}][key]" placeholder="Key" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <input type="text" name="attributes[${index}][value]" placeholder="Value" class="block w-1/3 mr-2 shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <button type="button" class="remove-attribute bg-red-500 text-white px-2 py-1 rounded">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                    <button type="button" class="undo-remove-attribute bg-green-500 text-white px-2 py-1 rounded hidden">
                        <i class="fas fa-undo"></i> Undo
                    </button>
                    <input type="hidden" name="attributes[${index}][delete]" value="0" class="delete-flag">
                `;
                attributesList.appendChild(newAttribute);
                index++;
            });
        }

        // Add event listener to the parent `attributesList` for remove and undo buttons
        attributesList.addEventListener('click', function(event) {
            let attributeItem = event.target.closest('.attribute-item');
            if (!attributeItem) return;

            let deleteFlag = attributeItem.querySelector('.delete-flag');
            let removeButton = attributeItem.querySelector('.remove-attribute');
            let undoButton = attributeItem.querySelector('.undo-remove-attribute');

            // Handling Remove Button Click
            if (event.target.closest('.remove-attribute')) {
                console.log('Remove clicked:', attributeItem); // Debugging: log item being removed

                // Mark for deletion and add visual class
                deleteFlag.value = '1';
                attributeItem.classList.add('deleting');

                // Toggle buttons
                removeButton.classList.add('hidden');
                undoButton.classList.remove('hidden');
            }

            // Handling Undo Button Click
            else if (event.target.closest('.undo-remove-attribute')) {
                console.log('Undo clicked:', attributeItem); // Debugging: log item being undone

                // Unmark for deletion and remove visual class
                deleteFlag.value = '0';
                attributeItem.classList.remove('deleting');

                // Toggle buttons back
                removeButton.classList.remove('hidden');
                undoButton.classList.add('hidden');
            }
        });
    }
});

</script>

</x-app-layout>
