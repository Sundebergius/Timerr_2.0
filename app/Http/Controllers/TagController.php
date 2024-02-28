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
            $client_id = $request->input('client_id');
            if ($client_id) {
                $client = Client::find($client_id);
                if ($client) {
                    $client->tags()->attach($tag->id);
                } else {
                    return response()->json(['error' => 'Client not found'], 404);
                }
            } else {
                return response()->json(['error' => 'No client ID provided'], 400);
            }
    
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Failed to save tag'], 500);
        }
    }

    public function delete(Request $request)
    {
        $tag = $request->input('tag');
        $clientId = $request->input('client_id');

        // Assuming you have a Tag model with a 'name' field
        $tagModel = Tag::where('name', $tag)->first();

        if ($tagModel) {
            // Find the client and detach the tag
            $client = Client::find($clientId);
            if ($client) {
                $client->tags()->detach($tagModel->id);
            }

            $tagModel->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Tag not found'], 404);
        }
    }

    public function getClientTags($id)
    {
        $client = Client::with('tags')->find($id);

        if ($client) {
            $tags = $client->tags()->get();
            return response()->json($tags, 200);
        } else {
            return response()->json(['error' => 'Client not found'], 404);
        }
    }
}
