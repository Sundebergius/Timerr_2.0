<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'description' => 'required',
            'price' => 'required',
            'quantity' => 'required',
            'user_id' => 'required',
            //'active' => 'required',
        ]);

        $product = new Product;
        $product->title = $request->title;
        $product->category = $request->category;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->user_id = $request->user_id;
        //$product->active = $request->active;        

        $product->save();

        return response()->json(['message' => 'Product created successfully'], 200);
    }

    public function getUserProducts($userId)
    {
        $products = Product::where('user_id', $userId)->get();
        return response()->json($products);
    }
}
