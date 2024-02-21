<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Client;

class TagController extends Controller
{
    public function store(Request $request)
    {
        $tag = new Tag;
        $tag->name = $request->input('name');
        $tag->color = $request->input('color');

        if ($tag->save()) {
            // Find the client and attach the tag
            $client = Client::find($request->input('client_id'));
            if ($client) {
                $client->tags()->attach($tag->id);
            }
    
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Failed to save tag'], 500);
        }
    }

    public function delete(Request $request)
    {
        $tag = $request->input('tag');

        // Assuming you have a Tag model with a 'name' field
        $tagModel = Tag::where('name', $tag)->first();

        if ($tagModel) {
            $tagModel->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Tag not found'], 404);
        }
    }
}
