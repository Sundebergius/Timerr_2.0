<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Store method called');
        \Log::info('Request data:', $request->all());
    
        // Common validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'quantity_in_stock' => 'nullable|integer|min:0',
            'active' => 'required|boolean',
            'parent_id' => 'nullable|exists:products,id',
            'type' => 'required|in:product,service', // Required to distinguish product and service
        ];
    
        // Additional validation for services (attributes are required only for services)
        if ($request->input('type') === 'service') {
            $rules['attributes'] = 'required|array'; // Ensure attributes are provided for services
            $rules['attributes.*.key'] = 'required|string|max:255';
            $rules['attributes.*.value'] = 'required|numeric';
        }
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json($validator->errors(), 422);
        }
    
        try {
            \Log::info('Validation passed');
    
            // Format attributes if it's a service, otherwise set as null or empty
            $formattedAttributes = [];
            if ($request->input('type') === 'service') {
                $formattedAttributes = $this->formatAttributes($request->input('attributes', []));
            }
    
            \Log::info('Formatted attributes:', $formattedAttributes);
    
            // Prepare the product data
            $productData = [
                'user_id' => $request->user_id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price ?: 0,
                'quantity_in_stock' => $request->quantity_in_stock ?: 0,
                'active' => $request->active,
                'parent_id' => $request->parent_id,
                'type' => $request->type,
                'attributes' => !empty($formattedAttributes) ? $formattedAttributes : null,
            ];
    
            \Log::info('Product data to be saved:', $productData);
    
            // Create the product
            $product = Product::create($productData);
    
            \Log::info('Product created successfully:', $product->toArray());
    
            return response()->json(['message' => 'Product created successfully', 'product' => $product], 200);
        } catch (\Exception $e) {
            \Log::error('Error creating product:', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Unable to create product'], 500);
        }
    }


    private function formatAttributes(array $attributes)
    {
        \Log::info('Original attributes:', $attributes);

        $formattedAttributes = [];

        foreach ($attributes as $attribute) {
            // Check if the attribute is marked for deletion
            if (isset($attribute['delete']) && $attribute['delete'] === '1') {
                continue; // Skip this attribute
            }

            // Ensure the attribute has a key and a value
            if (!empty($attribute['key'])) {
                $formattedAttributes[] = [
                    'key' => $attribute['key'], // Keep the key as a string
                    'value' => !empty($attribute['value']) ? (float) $attribute['value'] : null, // Ensure the value is a float, or null if empty
                ];
            }
        }

        \Log::info('Formatted attributes:', $formattedAttributes);

        return $formattedAttributes;
    }

    public function getUserProducts($userId)
    {
        try {
            // Fetch products for the given user ID
            $products = Product::where('user_id', $userId)->get();
            return response()->json(['products' => $products], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching products: '.$e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching products.'], 500);
        }
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('pageSize', 10); // Default to 10 if pageSize is not set
        if ($pageSize === 'all') {
            $products = Product::all();
        } else {
            $products = Product::paginate($pageSize);
        }

        return view('products.index', ['products' => $products]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'quantity_in_stock' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
            'parent_id' => 'nullable|exists:products,id',
            'attributes' => 'nullable|array',
            'attributes.*.key' => 'required_with:attributes|distinct|string|max:255',
            'attributes.*.value' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $productData = [
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price ?: 0,
                'quantity_in_stock' => $request->quantity_in_stock ?: 0,
                'active' => $request->has('active') ? 1 : 0,
                'parent_id' => $request->parent_id,
                'attributes' => $this->formatAttributes($request->input('attributes', [])),
            ];

            \Log::info('Product data to be updated:', $productData);

            $product->update($productData);

            \Log::info('Product updated successfully:', $product->toArray());

            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            \Log::error('Error updating product:', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Unable to update product');
        }
    }

    private function filterAttributes(array $attributes): array
    {
        return array_filter($attributes, function ($attribute) {
            return !empty($attribute['key']) || !empty($attribute['value']);
        });
    }

}
