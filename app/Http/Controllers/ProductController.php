<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Store method called'); // Log when store method is called
        \Log::info('Request data:', $request->all()); // Log the request data

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'quantity_in_stock' => 'nullable|integer|min:0',
            'active' => 'required|boolean',
            'parent_id' => 'nullable|exists:products,id',
            'attributes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray()); // Log validation errors
            return response()->json($validator->errors(), 422);
        }

        try {
            \Log::info('Validation passed'); // Log validation success

            $productData = [
                'user_id' => $request->user_id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price,
                'quantity_in_stock' => $request->quantity_in_stock,
                'active' => $request->active,
                'parent_id' => $request->parent_id,
                'attributes' => $request->attributes ? json_encode($request->attributes) : null, // Ensure proper JSON encoding
            ];

            \Log::info('Product data to be saved:', $productData); // Log data before saving

            $product = Product::create($productData);

            \Log::info('Product created successfully:', $product->toArray()); // Log success

            return response()->json(['message' => 'Product created successfully', 'product' => $product], 200);
        } catch (\Exception $e) {
            \Log::error('Error creating product:', ['exception' => $e->getMessage()]); // Log general errors
            return response()->json(['error' => 'Unable to create product'], 500);
        }
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
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'quantityInStock' => 'required',
            'quantitySold' => 'required',
        ]);
    
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantityInStock = $request->quantityInStock;
        $product->quantitySold = $request->quantitySold;
        $product->active = $request->has('active') ? 1 : 0;
    
        $product->save();
    
        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }
}
