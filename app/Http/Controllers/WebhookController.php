<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Webhook;
use App\Models\Client;
use App\Models\Task;
use App\Models\Project;

class WebhookController extends Controller
{
    public function index()
    {
        $userId = auth()->id(); // Get the ID of the logged-in user
        $webhooks = auth()->user()->webhooks; // Get webhooks for the logged-in user

        // Fetch only completed projects that belong to the logged-in user
        $projects = Project::where('user_id', $userId)
                        ->where('status', 'completed')
                        ->get();

        return view('integrations.webhooks.index', compact('webhooks', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'name' => 'required|string',
            'event' => 'required|string',
        ]);

        \Log::info('Validated Data:', $validated);

        try {
            // Check if a webhook with the same user_id, url, and event already exists
            $existingWebhook = auth()->user()->webhooks()
                ->where('url', $validated['url'])
                ->where('event', $validated['event'])
                ->first();

            if ($existingWebhook) {
                return redirect()->back()->withErrors([
                    'url' => 'A webhook with this URL and event already exists for your account.'
                ])->withInput();
            }

            // Create the webhook since it doesn't exist
            $webhook = auth()->user()->webhooks()->create($validated);

            \Log::info('Created Webhook:', $webhook->toArray());

            return redirect()->route('integrations.webhooks.index')->with('success', 'Webhook created successfully.');

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database Error:', ['error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred. Please try again later.'])->withInput();
        } catch (\Exception $e) {
            \Log::error('General Error:', ['error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred. Please try again later.'])->withInput();
        }
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'name' => 'required|string',
            'event' => 'required|string',
        ]);

        $webhook->update($validated);

        return redirect()->route('integrations.webhooks.index')->with('success', 'Webhook updated successfully.');
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();
        return redirect()->route('integrations.webhooks.index')->with('success', 'Webhook deleted successfully.');
    }

    public function sendWebhook(Request $request, Project $project)
    {
        // Validate the request if needed
        $validated = $request->validate([
            'webhook_url' => 'required|url'
        ]);

        // Prepare data for the webhook
        $data = $this->prepareProjectData($project);

        // Send data to the webhook URL
        $response = Http::post($validated['webhook_url'], $data);

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Project data sent successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to send project data.');
        }
    }

    public function handleClientStatusUpdated(Request $request)
    {
        // Validate the incoming request payload
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|string',
            'updated_at' => 'required|date',
        ]);

        // Handle the received webhook data
        $client = Client::find($validated['client_id']);
        if ($client) {
            // Process the client status update (e.g., log it, update another system, etc.)
            // Example: Log client status update
            \Log::info('Client status updated via webhook:', [
                'client_id' => $client->id,
                'status' => $client->status,
                'updated_at' => $client->updated_at,
            ]);

            // Respond with a success status
            return response()->json(['message' => 'Webhook handled successfully.'], 200);
        }

        // Respond with an error status if client is not found
        return response()->json(['message' => 'Client not found.'], 404);
    }

    public function handleProjectCreated(Request $request)
    {
        // Validate the incoming request payload
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'created_at' => 'required|date',
        ]);

        // Handle the received webhook data
        $project = Project::find($validated['project_id']);
        if ($project) {
            // Process the project creation data (e.g., log it, update another system, etc.)
            \Log::info('Project created via webhook:', [
                'project_id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'created_at' => $project->created_at,
            ]);

            // Respond with a success status
            return response()->json(['message' => 'Webhook handled successfully.'], 200);
        }

        // Respond with an error status if project is not found
        return response()->json(['message' => 'Project not found.'], 404);
    }

    protected function sendWebhookNotification(Webhook $webhook, array $payload)
    {
        try {
            $response = Http::post($webhook->url, $payload);

            if (!$response->successful()) {
                \Log::error('Failed to send webhook notification', [
                    'webhook_id' => $webhook->id,
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending webhook notification', [
                'webhook_id' => $webhook->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function triggerWebhook(Request $request)
    {
        // Validate the request
        $request->validate([
            'webhook_id' => 'required|exists:webhooks,id',
            'task_id' => 'nullable|exists:tasks,id', // Optional field
            'project_id' => 'nullable|exists:projects,id', // Optional field
        ]);

        $webhook = Webhook::find($request->input('webhook_id'));

        if (!$webhook) {
            return back()->withErrors(['error' => 'Webhook not found.']);
        }

        // Prepare the data to be sent (if project_id is provided)
        $data = [];
        switch ($webhook->event) {
            case 'task_created':
            case 'task_completed':
                $task = Task::find($request->input('task_id'));
                if ($task) {
                    $data = $this->prepareTaskData($task);
                } else {
                    return back()->withErrors(['error' => 'Task not found.']);
                }
                break;

            // Handle other events...

            default:
                return back()->withErrors(['error' => 'Unsupported event type.']);
        }

        // Send data to the webhook URL
        try {
            $response = Http::post($webhook->url, $data);
            if ($response->successful()) {
                return back()->with('status', 'Webhook triggered successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to trigger webhook.']);
            }
        } catch (\Exception $e) {
            \Log::error('Error triggering webhook: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while triggering the webhook.']);
        }
    }

    public function toggleActive(Webhook $webhook)
    {
        // Toggle the active status
        $webhook->active = !$webhook->active;
        $webhook->save();

        // Redirect back with a success message
        return redirect()->route('integrations.webhooks.index')->with('success', 'Webhook status updated successfully.');
    }

    public function handleProjectStatusChange($projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Check if the project is completed
        if ($project->status !== 'completed') {
            return response()->json(['error' => 'Project is not completed'], 400);
        }

        // Fetch the active webhooks for the current user
        $webhooks = auth()->user()->webhooks()->where('event', 'project_completed')->where('active', true)->get();

        // Prepare data for the webhook
        $data = $this->prepareProjectData($project);

        foreach ($webhooks as $webhook) {
            // Send data to the webhook URL
            $response = Http::post($webhook->url, $data);

            if (!$response->successful()) {
                // Handle failure (log or notify)
                \Log::error('Failed to send webhook data', [
                    'webhook_url' => $webhook->url,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function handleProjectCompleted(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        // Retrieve the project by ID
        $project = \App\Models\Project::find($validated['project_id']);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Prepare data for the webhook
        $data = $this->prepareProjectData($project);

        // Fetch the active webhooks for the 'project_completed' event
        $webhooks = Webhook::where('event', 'project_completed')->where('active', true)->get();

        if ($webhooks->isEmpty()) {
            // Optionally log or handle the absence of webhooks
            Log::info('No webhooks configured for event "project_completed".', ['project_id' => $project->id]);
            return response()->json(['status' => 'No webhooks to process.'], 200);
        }

        // Send data to each webhook URL
        foreach ($webhooks as $webhook) {
            try {
                $response = Http::post($webhook->url, $data);

                if (!$response->successful()) {
                    // Handle failure (log or notify)
                    Log::error('Failed to send webhook data', [
                        'webhook_url' => $webhook->url,
                        'response_status' => $response->status(),
                        'response_body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                // Log any exception that occurs during the HTTP request
                Log::error('Error sending webhook', [
                    'webhook_url' => $webhook->url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['status' => 'Webhooks processed.'], 200);
    }


    // Handle task created event
    public function handleTaskCreated(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
        ]);

        $task = Task::find($validated['task_id']);
        
        if (!$task) {
            return response()->json(['error' => 'Task not found.'], 404);
        }

        // Prepare and send data to the webhook URL
        $payload = $this->prepareTaskData($task);
        $webhook = Webhook::where('event', 'task_created')->where('active', true)->first();

        if ($webhook) {
            $this->sendWebhookNotification($webhook, $payload);
        }

        return response()->json(['status' => 'Webhook processed.'], 200);
    }

    // Handle task completed event
    public function handleTaskCompleted(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
        ]);

        $task = Task::find($validated['task_id']);
        
        if (!$task) {
            return response()->json(['error' => 'Task not found.'], 404);
        }

        // Prepare and send data to the webhook URL
        $payload = $this->prepareTaskData($task);
        $webhook = Webhook::where('event', 'task_completed')->where('active', true)->first();

        if ($webhook) {
            $this->sendWebhookNotification($webhook, $payload);
        }

        return response()->json(['status' => 'Webhook processed.'], 200);
    }

    // Helper function to prepare task data for webhook
    protected function prepareTaskData(Task $task)
    {
        return [
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'user_id' => $task->user_id,
            'client_id' => $task->client_id,
            'taskable_id' => $task->taskable_id,
            'taskable_type' => $task->taskable_type,
            'title' => $task->title,
            'task_type' => $task->task_type,
            'created_at' => $task->created_at->toDateTimeString(),
            'updated_at' => $task->updated_at->toDateTimeString(),
        ];
    }

    protected function prepareProjectData($project)
    {
        // Fetch tasks related to the project
        $tasks = $project->tasks;

        // Separate tasks by type
        $projectTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProject');
        $hourlyTasks = $tasks->where('taskable_type', 'App\\Models\\TaskHourly');
        $distanceTasks = $tasks->where('taskable_type', 'App\\Models\\TaskDistance');
        $productTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProduct');
        $otherTasks = $tasks->where('taskable_type', 'App\\Models\\TaskOther');

        // Extract products
    $products = collect();
    foreach ($productTasks as $task) {
        foreach ($task->taskProduct as $taskProduct) {
            $products->push([
                'product_title' => $taskProduct->product->title ?? 'Unnamed Product',  // Use 'title' instead of 'name'
                'total_sold' => $taskProduct->total_sold,
                'unit_price' => $taskProduct->product->price,
                'total_price' => $taskProduct->product->price * $taskProduct->total_sold
            ]);
        }
    }

    // Calculate totals
    $total = 0;
    foreach ($projectTasks as $task) {
        $total += $task->taskable->price;
    }
    foreach ($hourlyTasks as $task) {
        $hours = $task->taskable->registrationHourly->sum('minutes_worked') / 60;
        $total += $hours * $task->taskable->rate_per_hour;
    }
    foreach ($products as $product) {
        $total += $product['total_price'];
    }
    foreach ($distanceTasks as $task) {
        $distance = $task->taskable->registrationDistances->sum('distance');
        $total += $distance * $task->taskable->price_per_km;
    }

    // Return data in a structured format
    return [
        'project_id' => $project->id,
        'project_title' => $project->title,
        'client_name' => $project->client->name ?? 'Unknown Client', // Ensure client relationship is defined
        'start_date' => $project->start_date->toIso8601String(),
        'end_date' => optional($project->end_date)->toIso8601String(),
        'project_status' => $project->status,
        'total_amount' => round($total, 2),  // Removed VAT calculations
        'products' => $products->toArray(),
        'tasks' => [
            'project_tasks' => $projectTasks->map(fn($task) => [
                'task_id' => $task->id,
                'description' => $task->description ?? 'No description', // Handle null descriptions
                'price' => $task->taskable->price
            ])->values()->toArray(), // Convert to array and handle missing descriptions

            'hourly_tasks' => $hourlyTasks->map(function ($task) {
                $hourlyRegistrations = $task->taskable->registrationHourly;

                return [
                    'task_id' => $task->id,
                    'registrations' => $hourlyRegistrations->map(function ($registration) {
                        return [
                            'comment' => $registration->comment ?? 'No comment', // Handle null comments
                            'minutes_worked' => $registration->minutes_worked
                        ];
                    })->filter()->values()->toArray(), // Filter out null comments
                    'rate_per_hour' => $task->taskable->rate_per_hour,
                    'hours_worked' => $hourlyRegistrations->sum('minutes_worked') / 60
                ];
            })->values()->toArray(),

            'distance_tasks' => $distanceTasks->map(fn($task) => [
                'task_id' => $task->id,
                'description' => $task->description ?? 'No description', // Handle null descriptions
                'price_per_km' => $task->taskable->price_per_km,
                'total_distance' => $task->taskable->registrationDistances->sum('distance')
            ])->values()->toArray() // Convert to array and handle missing descriptions
        ]

        ];
    }

    // public function handleStripeWebhook(Request $request)
    // {
    //     $payload = $request->getContent();
    //     $signature = $request->headers->get('Stripe-Signature');
    //     $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

    //     // Verify the webhook signature
    //     try {
    //         $event = \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
    //     } catch (\UnexpectedValueException $e) {
    //         // Invalid payload
    //         return response()->json(['error' => 'Invalid payload'], 400);
    //     } catch (\Stripe\Exception\SignatureVerificationException $e) {
    //         // Invalid signature
    //         return response()->json(['error' => 'Invalid signature'], 400);
    //     }

    //     // Handle the event
    //     switch ($event['type']) {
    //         case 'invoice.payment_succeeded':
    //             // Handle successful payment
    //             break;
    //         case 'customer.subscription.created':
    //             // Handle subscription creation
    //             break;
    //         // Add more cases for other events you want to handle
    //     }

    //     return response()->json(['status' => 'success']);
    // }
}
