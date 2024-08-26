<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category' => 'required',
            'description' => 'required',
            'price' => 'required',
            'user_id' => 'required',
            'quantityInStock' => 'required',
            'active' => 'required',
            'parent_id' => 'nullable|exists:products,id',
            'attributes' => 'nullable|array',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = new Product;
        $product->title = $request->title;
        $product->category = $request->category;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantityInStock = $request->quantityInStock;
        $product->user_id = $request->user_id;
        $product->active = $request->active;
        $product->parent_id = $request->parent_id;
        $product->attributes = $request->attributes;        

        $product->save();

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 200);
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
