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
            //'active' => 'required',
        ]);

        $product = new Product;
        $product->title = $request->title;
        $product->category = $request->category;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        //$product->active = $request->active;

        // Set the user_id to the ID of the currently logged-in user
        $product->user_id = auth()->id();

        $product->save();

        return redirect()->route('projects.tasks.create');      

    }
}
