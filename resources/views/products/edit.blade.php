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
                    
                        <div>
                            <label for="title">Title</label>
                            <input id="title" class="block mt-1 w-full" type="text" name="title" value="{{ $product->title }}" required autofocus />
                        </div>
                    
                        <div class="mt-4">
                            <label for="description">Description</label>
                            <input id="description" class="block mt-1 w-full" type="text" name="description" value="{{ $product->description }}" required />
                        </div>
                    
                        <div class="mt-4">
                            <label for="price">Price</label>
                            <input id="price" class="block mt-1 w-full" type="number" name="price" value="{{ $product->price }}" required />
                        </div>
                    
                        <div class="mt-4">
                            <label for="quantityInStock">Quantity in Stock</label>
                            <input id="quantityInStock" class="block mt-1 w-full" type="number" name="quantityInStock" value="{{ $product->quantityInStock }}" required />
                        </div>
                    
                        <div class="mt-4">
                            <label for="quantitySold">Quantity Sold</label>
                            <input id="quantitySold" class="block mt-1 w-full" type="number" name="quantitySold" value="{{ $product->quantitySold }}" required />
                        </div>
                    
                        <div class="mt-4">
                            <label for="active">Status</label>
                            <p class="text-sm text-gray-600">Check this box if the product is active. Uncheck it if the product is not active.</p>
                            <input id="active" class="block mt-1" type="checkbox" name="active" @if($product->active) checked @endif />
                        </div>
                    
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
</x-app-layout>