<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Webhook;

use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());

        $colorClasses = [
            'rose' => 'bg-rose-100 text-rose-500 hover:bg-rose-200',
            'sky' => 'bg-sky-100 text-sky-500 hover:bg-sky-200',
            'emerald' => 'bg-emerald-100 text-emerald-500 hover:bg-emerald-200',
            'gray' => 'bg-gray-100 text-gray-500 hover:bg-gray-200',
        ];

        $user = auth()->user();
    
        // Fetch the current user and calculate client count and limit
        $clientCount = $user->clients()->count();
        $clientLimit = 5; // Example: This can be fetched dynamically based on the user's plan if needed


        // Ensure $searches is always an array
        $searches = (array) $request->input('search', []);
        $pageSize = $request->get('pageSize', 10);

        // If the selected value is "All", get all clients
        if ($pageSize == 'all') {
            $clients = Client::get();
        } else {
            // Otherwise, paginate the clients
            $clients = Client::paginate($pageSize);
        }

        $sortField = $request->get('sortField', 'name');
        $sortDirection = $request->get('sortDirection', 'asc');

        $query = Client::with('tags')->where('user_id', auth()->id());

        $status = null;
        if (!empty($searches)) {
            $query->where(function ($query) use ($searches, &$status) {
                foreach ($searches as $search) {
                    if (strpos($search, 'status:') === 0) {
                        // Extract status value
                        $status = substr($search, 7);
                    } else {
                        $query->orWhere('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%")
                            ->orWhereHas('tags', function ($query) use ($search) {
                                $query->where('name', 'LIKE', "%{$search}%");
                            });
                    }
                }
            });

            // Apply the status filter if a status is specified
            if ($status) {
                $query->where('status', $status);
            }
        }

        $clients = $query->orderBy($sortField, $sortDirection)
                        ->paginate($pageSize == 'all' ? Client::count() : $pageSize);

        return view('clients.index', ['clients' => $clients, 'clientCount' => $clientCount, 'clientLimit' => $clientLimit, 'colorClasses' => $colorClasses]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'cvr' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:lead,contacted,interested,negotiation,deal_made', // Validate status
            // 'tags' => 'array',
            // 'tags.*' => 'string',
            // 'tag_colors' => 'array',
            // 'tag_colors.*' => 'string',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id(); // set user_id to the ID of the currently authenticated user

        // Set default country and status if not provided
        $data['country'] = $data['country'] ?? 'DK';
        $data['status'] = $data['status'] ?? Client::STATUS_LEAD;

        $client = Client::create($data);

        // Handle the tags
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            $tag_colors = $request->input('tag_colors');
            foreach ($tags as $index => $tagName) {
                $tagColor = $tag_colors[$index] ?? null;
                $tag = Tag::firstOrCreate(['name' => $tagName, 'color' => $tagColor]);
                $client->tags()->attach($tag->id);
            }
        }

        // Trigger the "client created" webhook event
        $this->triggerWebhookEvent('client_created', [
            'client_id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
        ]);
    
        return redirect()->route('clients.index');
    }

    protected function triggerWebhookEvent($event, $payload)
    {
        // Fetch all webhooks related to this event
        $webhooks = Webhook::where('event', $event)->get();

        foreach ($webhooks as $webhook) {
            $this->sendWebhookNotification($webhook, $payload);
        }
    }

    // protected function sendWebhookNotification(Webhook $webhook, array $payload)
    // {
    //     // Send an HTTP POST request to the webhook URL with the payload data
    //     $response = Http::post($webhook->url, $payload);

    //     if (!$response->successful()) {
    //         // Log the failure to send the webhook
    //         \Log::error('Failed to send webhook notification', [
    //             'webhook_url' => $webhook->url,
    //             'response_status' => $response->status(),
    //             'response_body' => $response->body(),
    //         ]);
    //     }
    // }

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
            'address' => 'nullable',
            'city' => 'nullable',
            'zip_code' => 'nullable',
            'categories' => 'nullable',
            'notes' => 'nullable',
        ]);

        $client->update($validatedData);

        // // Handle the tags
        // $newTags = $request->input('tags');
        // $newTagColors = $request->input('tag_colors');
        // $client->load('tags'); // reload the tags relationship
        // $currentTagIds = $client->tags ? $client->tags->pluck('id')->toArray() : [];

        // if ($newTags) {
        //     foreach ($newTags as $index => $tagName) {
        //         $tagColor = $newTagColors[$index] ?? null;
        //         $tag = Tag::firstOrCreate(['name' => $tagName, 'color' => $tagColor]);
        //         if (!in_array($tag->id, $currentTagIds)) {
        //             $client->tags()->attach($tag->id); // attach new tags
        //         } else {
        //             $key = array_search($tag->id, $currentTagIds);
        //             unset($currentTagIds[$key]); // remove tag from the list of current tags
        //         }
        //     }
        // }
        // // Any tags that are still in $currentTagIds have been removed in the request
        // foreach ($currentTagIds as $tagId) {
        //     $client->tags()->detach($tagId);
        // }

        // Check if a note has been provided
        if ($request->has('client_notes') && !empty($request->client_notes)) {
            // Create or update the note
            $client->clientNote()->updateOrCreate(
                ['client_id' => $client->id],
                ['content' => $request->client_notes]
            );
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
            'file' => 'required|mimes:csv,txt|max:2048', // Limit the file size to 2MB
        ]);

        $file = $request->file('file');
        $csvData = file_get_contents($file);
        $rows = array_map(function($row) {
            return array_map('trim', str_getcsv($row, ";")); // Use semicolon as the delimiter and trim white spaces
        }, array_filter(array_map('trim', explode("\n", $csvData)))); // Trim white spaces from each line and skip empty lines
        $header = array_shift($rows);

        // Check if the CSV file is empty after the headers
        if (empty($rows)) {
            return redirect()->back()->withErrors([
                'file' => 'The CSV file is empty.',
            ]);
        }

        DB::beginTransaction();

        $invalidRows = [];
        try {
            foreach ($rows as $index => $row) {
                if (count($header) != count($row)) {
                    $invalidRows[] = $index + 2; // Add 2 to account for 0-indexing and the header row
                    continue;
                }
                $row = array_combine($header, $row);

                // Validate the data
                $validator = Validator::make($row, [
                    'name' => 'required',
                    'email' => 'nullable|email',
                    // Add more validation rules if necessary
                ]);

                if ($validator->fails()) {
                    $invalidRows[] = $index + 2;
                    $errorMessages[] = 'Row ' . ($index + 2) . ': ' . $validator->errors()->first();
                    continue;
                }

            // foreach ($rows as $row) {
            //     if (count($header) != count($row)) {
            //         // Print out the header and row for debugging
            //         echo "Header: ", implode(", ", $header), "\n";
            //         echo "Row: ", implode(", ", $row), "\n";
            //     }
            //     $row = array_combine($header, $row);

                Client::create([
                    'user_id' => auth()->id(), // assuming the user is authenticated
                    'name' => $row['name'],
                    'email' => $row['email'] ?? null,
                    'cvr' => $row['cvr'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'address' => $row['address'] ?? null,
                    'country' => 'DK',
                    'status' => 'lead',
                ]);
        }

        if (!empty($invalidRows)) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'file' => 'Invalid data in the following rows: ' . implode('. ', $errorMessages),
            ]);
        }

        DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'file' => 'An error occurred while importing the clients.',
            ]);
        }

        // Flash a success message to the session
        session()->flash('success', 'Clients imported successfully.');

        return redirect()->route('clients.index');
    }

    public function fetchClients()
    {
        $clients = Client::where('user_id', auth()->id())->get(); // Adjust based on your logic
        return response()->json($clients);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['error' => 'Client not found.'], 404);
        }

        $client->status = $request->status;
        $client->save();

        // Trigger webhook after status update if any active webhook exists
        $this->triggerClientStatusUpdatedWebhook($client);

        // Return the updated status or other relevant data
        return response()->json([
            'message' => 'Status updated successfully.',
            'newStatus' => $client->status // Return the new status to update the UI
        ]);
    }

    protected function triggerClientStatusUpdatedWebhook(Client $client)
    {
        // Find all active webhooks for the 'client_status_updated' event that belong to the current user
        $webhooks = Webhook::where('event', 'client_status_updated')
                            ->where('active', true) // Check if the webhook is active
                            ->where('user_id', auth()->id()) // Ensure the webhook belongs to the current user
                            ->get();

        foreach ($webhooks as $webhook) {
            // Prepare the data payload for the webhook
            $payload = [
                'client_id' => $client->id,
                'status' => $client->status,
                'updated_at' => $client->updated_at->toDateTimeString(),
            ];

            // Trigger the webhook by sending an HTTP POST request
            $this->sendWebhookNotification($webhook, $payload);
        }
    }

    protected function sendWebhookNotification(Webhook $webhook, array $payload)
    {
        // Send an HTTP POST request to the webhook URL
        $response = Http::post($webhook->url, $payload);

        if ($response->successful()) {
            \Log::info('Webhook notification sent successfully.', ['webhook' => $webhook->id, 'response' => $response->body()]);
        } else {
            \Log::error('Failed to send webhook notification.', ['webhook' => $webhook->id, 'response' => $response->body()]);
        }
    }

    public function filterClients(Request $request)
    {
        $hasEmail = $request->has_email;
        $hasPhone = $request->has_phone;

        $clients = Client::when($hasEmail, function ($query) {
            return $query->whereNotNull('email');
        })->when($hasPhone, function ($query) {
            return $query->whereNotNull('phone');
        })->get();

       // return view('clients.index', compact('clients'));
    }

    public function addTag(Request $request)
    {
        $client = Client::findOrFail($request->client_id);
        $tag = Tag::firstOrCreate(['name' => $request->tag, 'color' => $request->tag_color]);
        $client->tags()->attach($tag->id);

        return response()->json(['success' => true]);
    }

    public function removeTag(Client $client, Tag $tag)
    {
        $client->tags()->detach($tag->id);

        return response()->json(['success' => true]);
    }

    // public function updateNote(Request $request, Client $client)
    // {
    //     \Log::info('updateNote called');
    //     \Log::info($request->all());
    //     $validatedData = $request->validate([
    //         'notes' => 'required|max:65000',
    //     ]);

    //     $client->note()->updateOrCreate(
    //         ['client_id' => $client->id],
    //         ['content' => $validatedData['notes']]
    //     );

    //     // return back()->with('success', 'Note updated successfully');
    //     return redirect()->route('clients.edit', ['client' => $client->id])->with('success', 'Note updated successfully');
    // }
}
