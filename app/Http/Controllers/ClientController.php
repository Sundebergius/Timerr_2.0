<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $searches = $request->get('search');
        $pageSize = $request->get('pageSize', 10);
        $sortField = $request->get('sortField', 'name');
        $sortDirection = $request->get('sortDirection', 'asc');
    
        $query = Client::query();

        if ($searches) {
            foreach ($searches as $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%")
                        ->orWhereHas('tags', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        });
                });
            }
        }
    
        $clients = $query->orderBy($sortField, $sortDirection)
            ->paginate($pageSize == 'all' ? Client::count() : $pageSize);
    
        return view('clients.index', ['clients' => $clients]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = auth()->id(); // set user_id to the ID of the currently authenticated user
        $data['country'] = 'DK'; // set default country to 'DK'
        $data['status'] = Client::STATUS_LEAD; // set default status to 'lead'
    
        $client = Client::create($data);

        // Handle the tags
        $tags = $request->input('tags');
        $tag_colors = $request->input('tag_colors');
        foreach ($tags as $index => $tagName) {
            $tagColor = $tag_colors[$index] ?? null;
            $tag = Tag::firstOrCreate(['name' => $tagName, 'color' => $tagColor]);
            $client->tags()->attach($tag->id);
        }
    
        return redirect()->route('clients.index');
    }

    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        //dd($request->all());
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'status' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'categories' => 'nullable',
            'notes' => 'nullable',
        ]);

        //dd($client->tags);
        $client->update($validatedData);

        // Handle the tags
        $newTags = $request->input('tags');
        $newTagColors = $request->input('tag_colors');
        $client->load('tags'); // reload the tags relationship
        //dd($client->tags);
        $currentTagIds = $client->tags->pluck('id')->toArray();

        foreach ($newTags as $index => $tagName) {
            $tagColor = $newTagColors[$index] ?? null;
            $tag = Tag::firstOrCreate(['name' => $tagName, 'color' => $tagColor]);
            if (!in_array($tag->id, $currentTagIds)) {
                $client->tags()->attach($tag->id); // attach new tags
            } else {
                $key = array_search($tag->id, $currentTagIds);
                unset($currentTagIds[$key]); // remove tag from the list of current tags
            }
        }
        // Any tags that are still in $currentTagIds have been removed in the request
        foreach ($currentTagIds as $tagId) {
            $client->tags()->detach($tagId);
        }

        return redirect()->route('clients.index')->with('success', 'Client updated successfully');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index');
    }

    public function create()
    {
        return view('clients.create');
    }

    public function edit(Client $client)
    {
        $statuses = [
            'lead' => Client::STATUS_LEAD,
            'contacted' => Client::STATUS_CONTACTED,
            'interested' => Client::STATUS_INTERESTED,
            'negotiation' => Client::STATUS_NEGOTIATION,
            'deal_made' => Client::STATUS_DEAL_MADE,
        ];

        return view('clients.edit', compact('client', 'statuses'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $csvData = file_get_contents($file);
        $rows = array_map("str_getcsv", explode("\n", $csvData));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $row = array_combine($header, $row);

            Client::create([
                'name' => $row['name'],
                'contact_details' => $row['contact_details'],
                'status' => $row['status'],
                'type' => $row['type'],
                'company_name' => $row['company_name'],
                'company_size' => $row['company_size'],
            ]);
        }

        return redirect()->route('clients.index');
    }
    
    public function updateStatus(Request $request, $id)
    {
        $client = Client::find($id);
        $client->status = $request->status;
        $client->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }
}
