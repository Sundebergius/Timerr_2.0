<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Services\PlanService;
use App\Models\ClientNote;
use App\Models\UserSettings;
use App\Models\ContactPerson;
use App\Models\Webhook;

use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    // protected $planService;

    // public function __construct(PlanService $planService)
    // {
    //     $this->planService = $planService;
    // }

    public function index(Request $request)
    {
        $colorClasses = [
            'rose' => 'bg-rose-100 text-rose-500 hover:bg-rose-200',
            'sky' => 'bg-sky-100 text-sky-500 hover:bg-sky-200',
            'emerald' => 'bg-emerald-100 text-emerald-500 hover:bg-emerald-200',
            'gray' => 'bg-gray-100 text-gray-500 hover:bg-gray-200',
        ];

        $user = auth()->user();
    
        // Fetch the current user and get the subscription plan name (e.g., 'free', 'freelancer')
        $subscriptionPlan = app(\App\Services\PlanService::class)->getPlanNameByPriceId($user->subscription('default')?->stripe_price ?? null);

        // Get the client limit from PlanService based on the user's subscription plan
        $clientLimit = app(\App\Services\PlanService::class)->getPlanLimits($subscriptionPlan)['clients'] ?? 5;

        // Get the current client count for the user
        $clientCount = $user->clients()->count();

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

        // Fetch user settings or create default ones if they don't exist
        $settings = $user->settings;

        if (!$settings) {
            $settings = $user->settings()->create([
                'show_email' => true,   // Default values you want
                'show_address' => true,
                'show_phone' => true,
                'show_cvr' => false,
                'show_city' => false,
                'show_zip_code' => false,
                'show_country' => false,
                'show_notes' => false,
                'show_contact_persons' => false,
            ]);
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

                        return view('clients.index', compact('clients', 'clientCount', 'clientLimit', 'colorClasses', 'settings'));
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
            'client_notes.*' => 'nullable|string',
            // Validation for contact persons
            'contact_persons.*.name' => 'nullable|string|max:255',
            'contact_persons.*.email' => 'nullable|email|max:255',
            'contact_persons.*.phone' => 'nullable|string|max:20',
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

        // Handle the creation of client notes
        if ($request->has('client_notes')) {
            foreach ($request->input('client_notes') as $noteContent) {
                if (!empty($noteContent)) {
                    ClientNote::create([
                        'client_id' => $client->id,
                        'content' => $noteContent,
                    ]);
                }
            }
        }

        // Handle the creation of contact persons (if any)
        if ($request->has('contact_persons')) {
            foreach ($request->input('contact_persons') as $person) {
                if (!empty($person['name'])) {
                    ContactPerson::create([
                        'client_id' => $client->id,
                        'name' => $person['name'],
                        'email' => $person['email'] ?? null,
                        'phone' => $person['phone'] ?? null,
                        'notes' => $person['notes'] ?? null,
                    ]);
                }
            }
        }

        // Check if there are any webhooks for the current user and event
        $webhooks = Webhook::where('event', 'client_created')
            ->where('user_id', auth()->id())
            ->get();

        // Only trigger webhook if there are any
        if ($webhooks->isNotEmpty()) {
            $this->triggerWebhookEvent('client_created', [
                'client_id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ]);
        }
    
        return redirect()->route('clients.index')->with('success', 'Client created successfully!');
    }

    protected function triggerWebhookEvent($event, $payload)
    {
        // Fetch only the webhooks related to the specific event and user
        $webhooks = Webhook::where('event', $event)
                            ->where('user_id', auth()->id()) // Ensure it only fetches for the current user
                            ->get();

        // If no webhooks exist for this event, do nothing
        if ($webhooks->isEmpty()) {
            return;
        }

        // Send webhook notifications for each matching webhook
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
        Log::info('Update function called for client: ' . $client->id);

        Log::info('Request data received:', $request->all());
    
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'status' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'city' => 'nullable',
            'zip_code' => 'nullable',
            'country' => 'nullable',
            'categories' => 'nullable',
            'client_notes.*' => 'nullable|string',
            'new_client_notes.*' => 'nullable|string', // Add validation for new client notes
            'contact_persons.*.name' => 'nullable|string|max:255',
            'contact_persons.*.email' => 'nullable|email|max:255',
            'contact_persons.*.phone' => 'nullable|string|max:20',
            'contact_persons.*.notes' => 'nullable|string',
            'new_contact_persons.*.name' => 'nullable|string|max:255', // Add validation for new contact persons
            'new_contact_persons.*.email' => 'nullable|email|max:255',
            'new_contact_persons.*.phone' => 'nullable|string|max:20',
            'new_contact_persons.*.notes' => 'nullable|string',
        ]);
    
        Log::info('Validation passed for client: ' . $client->id);
    
        // Update the client with the validated data
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

        // Update or create existing client notes
        if ($request->has('client_notes')) {
            $existingNoteIds = $client->clientNotes()->pluck('id')->toArray();
            $newNoteContents = $request->input('client_notes');

            foreach ($newNoteContents as $noteId => $content) {
                if (!empty($content)) {
                    if (in_array($noteId, $existingNoteIds)) {
                        // Update existing note
                        $client->clientNotes()->where('id', $noteId)->update(['content' => $content]);
                        unset($existingNoteIds[array_search($noteId, $existingNoteIds)]);
                    }
                }
            }

            // Delete notes that were removed
            if (!empty($existingNoteIds)) {
                ClientNote::whereIn('id', $existingNoteIds)->delete();
            }
        }

        // Create new notes
        if ($request->has('new_client_notes')) {
            foreach ($request->input('new_client_notes') as $newNote) {
                if (!empty($newNote)) {
                    ClientNote::create([
                        'client_id' => $client->id,
                        'content' => $newNote,
                    ]);
                }
            }
        }

        // Update or create existing contact persons
        if ($request->has('contact_persons')) {
            $existingContactIds = $client->contactPersons()->pluck('id')->toArray();
            $newContacts = $request->input('contact_persons');

            foreach ($newContacts as $contactId => $person) {
                if (!empty($person['name'])) {
                    if (in_array($contactId, $existingContactIds)) {
                        // Update existing contact person
                        $client->contactPersons()->where('id', $contactId)->update([
                            'name' => $person['name'],
                            'email' => $person['email'] ?? null,
                            'phone' => $person['phone'] ?? null,
                            'notes' => $person['notes'] ?? null,
                        ]);
                        unset($existingContactIds[array_search($contactId, $existingContactIds)]);
                    }
                }
            }

            // Delete contact persons that were removed
            if (!empty($existingContactIds)) {
                ContactPerson::whereIn('id', $existingContactIds)->delete();
            }
        }

        // Create new contact persons
        if ($request->has('new_contact_persons')) {
            foreach ($request->input('new_contact_persons') as $newContact) {
                if (!empty($newContact['name'])) {
                    ContactPerson::create([
                        'client_id' => $client->id,
                        'name' => $newContact['name'],
                        'email' => $newContact['email'] ?? null,
                        'phone' => $newContact['phone'] ?? null,
                        'notes' => $newContact['notes'] ?? null,
                    ]);
                }
            }
        }

        Log::info('Update function completed successfully for client: ' . $client->id);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully!');
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

        // Load related client notes and contact persons
        $client->load('clientNotes', 'contactPersons'); // Eager load relationships

        return view('clients.edit', compact('client', 'statuses'));
    }

    public function saveSettings(Request $request)
    {
        \Log::info('Form submitted with data: ', $request->all());

        // Validate the request
        $validated = $request->validate([
            'show_email' => 'boolean',
            'show_address' => 'boolean',
            'show_phone' => 'boolean',
            'show_cvr' => 'boolean',
            'show_city' => 'boolean',
            'show_zip_code' => 'boolean',
            'show_country' => 'boolean',
            'show_notes' => 'boolean',
            'show_contact_persons' => 'boolean',
        ]);

        // Update or create the user settings
        $userSettings = auth()->user()->settings()->updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        // Redirect back with a success message
        return redirect()->back()->with('status', 'Settings updated successfully.');
    }

    public function getClients(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Fetch clients for the authenticated user
        $clients = Client::where('user_id', auth()->id())->get();

        // Return the data in JSON format
        return response()->json($clients);
    }

    public function showSettingsModal()
    {
        // Get the authenticated user's settings
        $user = auth()->user();
        $settings = $user->settings;

        // If the user doesn't have settings, create default settings
        if (!$settings) {
            $settings = $user->settings()->create([
                'show_email' => true,   // Default values you want
                'show_address' => true,
                'show_phone' => true,
                'show_cvr' => false,
                'show_city' => false,
                'show_zip_code' => false,
                'show_country' => false,
                'show_notes' => false,
                'show_contact_persons' => false,
            ]);
        }

        // Pass the settings to the view
        return view('settings-modal', compact('settings'));
    }

    public function downloadTemplate()
    {
        // Path to the CSV template
        $file = public_path('csv/template.csv');
        
        // Define the headers for the response
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template.csv"',
            'Content-Transfer-Encoding' => 'binary',
        ];

        // Return the file as a download response
        return response()->download($file, 'template.csv', $headers);
    }

    public function import(Request $request, PlanService $planService)
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

        // Fetch the user's plan and client limits
        $user = auth()->user();
        $planName = $planService->getPlanNameByPriceId($user->subscription('default')?->stripe_price ?? null);
        $planLimits = $planService->getPlanLimits($planName);
        $clientLimit = $planLimits['clients'] ?? 5; // Default to 5 for free plan
        $currentClientCount = $user->clients()->count();

        // Check if importing the new clients will exceed the limit
        $rowsToImport = count($rows);
        if ($currentClientCount + $rowsToImport > $clientLimit) {
            return redirect()->back()->withErrors([
                'file' => "You can only have {$clientLimit} clients on the {$planName} plan. You already have {$currentClientCount}, and importing {$rowsToImport} would exceed your limit.",
            ]);
        }

        DB::beginTransaction();

        $invalidRows = [];
        $errorMessages = [];
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
                ]);

                if ($validator->fails()) {
                    $invalidRows[] = $index + 2;
                    $errorMessages[] = 'Row ' . ($index + 2) . ': ' . $validator->errors()->first();
                    continue;
                }

                // Create the client
                Client::create([
                    'user_id' => auth()->id(),
                    'name' => $row['name'],
                    'email' => $row['email'] ?? null,
                    'cvr' => $row['cvr'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'address' => $row['address'] ?? null,
                    'city' => $row['city'] ?? null,
                    'zip_code' => $row['zip_code'] ?? null,
                    'country' => $row['country'] ?? 'DK',
                    'status' => 'lead', // Set default status
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
