<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Models\TaskProduct;
use App\Models\Webhook;
use PDF;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = new Project;
        $project->fill([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'user_id' => auth()->id(),
        ]);

        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
            $project->client()->associate($client);
        }

        $project->save();

        $this->triggerProjectCreatedWebhook($project);

        return redirect()->route('projects.show', $project);
    }

    // protected function createEvent($project)
    // {    
    //     $start = $project->start_date->format('Y-m-d\TH:i');
    //     $end = $project->end_date->format('Y-m-d\TH:i');
    
    //     try {
    //         $response = Http::withHeaders([
    //             'X-CSRF-TOKEN' => csrf_token(),
    //         ])->post('https://your-calendar-api-endpoint', $payload);
                
    //         if ($response->failed()) {
    //             \Log::error('HTTP request failed', [
    //                 'status' => $response->status(),
    //                 'body' => $response->body(),
    //             ]);
    //         } else {
    //             \Log::info('Event created successfully', ['response' => $response->json()]);
    //         }
    //     } catch (\Exception $e) {
    //         \Log::error('Exception during event creation', ['message' => $e->getMessage()]);
    //     }
    // }

    protected function triggerProjectCreatedWebhook(Project $project)
    {
        // Find all active webhooks for the 'project_created' event that belong to the current user
        $webhooks = Webhook::where('event', 'project_created')
                            ->where('active', true) // Check if the webhook is active
                            ->where('user_id', auth()->id()) // Ensure the webhook belongs to the current user
                            ->get();

        foreach ($webhooks as $webhook) {
            // Prepare the data payload for the webhook
            $payload = [
                'project_id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'created_at' => $project->created_at->toDateTimeString(),
            ];

            // Trigger the webhook by sending an HTTP POST request
            $this->sendWebhookNotification($webhook, $payload);
        }
    }

    protected function sendWebhookNotification(Webhook $webhook, array $payload)
    {
        // Send an HTTP POST request to the webhook URL with the payload data
        $response = Http::post($webhook->url, $payload);

        if (!$response->successful()) {
            // Log the failure to send the webhook
            \Log::error('Failed to send webhook notification', [
                'webhook_url' => $webhook->url,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
        }
    }

    public function toggleCompletion(Project $project)
    {
        // Store the previous status for comparison
        $previousStatus = $project->status;

        // Toggle the project status
        $project->status = $previousStatus === 'completed' ? 'ongoing' : 'completed';
        $project->save();

        // Check if status changed to 'completed' and handle accordingly
        if ($project->status === 'completed' && $previousStatus !== 'completed') {
            // Ensure webhooks are configured for the event
            $webhooks = auth()->user()->webhooks()->where('event', 'project_completed')->get();

            if ($webhooks->isEmpty()) {
                \Log::info('No webhooks configured for event "send_project_data".', ['project_id' => $project->id]);
                // Optionally notify the user or handle the absence of webhooks
            } else {
                // Trigger webhook if the status has changed to 'completed'
                try {
                    app('App\Http\Controllers\WebhookController')->handleProjectStatusChange($project->id);
                } catch (\Exception $e) {
                    \Log::error('Error triggering webhook for project ' . $project->id, ['error' => $e->getMessage()]);
                }
            }
        }

        // Redirect back to the projects index
        return redirect()->route('projects.index');
    }

    public function showTaskSelectionForm(Request $request, Project $project)
    {
        // Fetch tasks related to the project
        $tasks = $project->tasks;

        // Separate different task types for easy access in the view
        $projectTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProject');
        $hourlyTasks = $tasks->where('taskable_type', 'App\\Models\\TaskHourly');
        $distanceTasks = $tasks->where('taskable_type', 'App\\Models\\TaskDistance');
        $productTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProduct');
        $otherTasks = $tasks->where('taskable_type', 'App\\Models\\TaskOther');

        // Eager load related taskable data to minimize database queries in the view
        $projectTasks->load('taskable');
        $hourlyTasks->load('taskable.registrationHourly');
        $distanceTasks->load('taskable.registrationDistances');
        
        // Load related product data for product-based tasks to access product attributes and pricing
        $productTasks->load('taskProduct.product');

        // Retrieve the user's plan
        $user = auth()->user();
        $subscriptionPlan = app(\App\Services\PlanService::class)->getPlanNameByPriceId(
            $user->subscription('default')?->stripe_price ?? null
        );

        // Pass tasks to the selection view
        return view('reports.select_tasks', compact(
            'project',
            'projectTasks',
            'hourlyTasks',
            'distanceTasks',
            'productTasks',
            'otherTasks',
            'subscriptionPlan', // Add subscriptionPlan to the view
        ));
    }

    public function generateReport(Project $project, Request $request)
    {
        try {
            if ($project->status != 'completed') {
                abort(404); 
            }
    
            // Validate request input
            $validated = $request->validate([
                'report_title' => 'nullable|string|max:255',
                'client_name' => 'nullable|string|max:255',
                'client_email' => 'nullable|email',
                'report_date' => 'nullable|date',
                'project_id' => 'nullable|string',
                'report_logo' => 'nullable|file|max:2048|mimetypes:image/jpeg,image/png,image/svg+xml',
                'notes_content' => 'nullable|string',
                'include_signature' => 'nullable|boolean', 
                'vat' => 'nullable|numeric|min:0|max:100',  // Range check for VAT
                'discount' => 'nullable|numeric|min:0|max:100',  // Range check for Discount
            ]);
    
            // Force checkbox values to boolean
            $notesContent = $request->input('notes_content');
            $includeNotes = !empty($notesContent);
            $includeSignature = filter_var($request->input('include_signature', false), FILTER_VALIDATE_BOOLEAN);
    
            // Set header details only if fields are provided
            $headerDetails = [
                'title' => $validated['report_title'] ?? '',
                'client_name' => $validated['client_name'] ?? '',
                'client_email' => $validated['client_email'] ?? '',
                'report_date' => $validated['report_date'] ?? '',
                'project_id' => $validated['project_id'] ?? '',
            ];
    
            // Handle logo if provided
            $logoData = null;
            if ($request->hasFile('report_logo') && $request->file('report_logo')->isValid()) {
                try {
                    $logoData = base64_encode($request->file('report_logo')->openFile()->fread($request->file('report_logo')->getSize()));
                    \Log::info("Logo successfully processed for report.");
                } catch (\Exception $e) {
                    \Log::warning("Failed to process logo file: " . $e->getMessage());
                }
            }
    
            // Get the user's subscription plan
            $user = auth()->user();
            $subscriptionPlan = app(\App\Services\PlanService::class)->getPlanNameByPriceId(
                $user->subscription('default')?->stripe_price ?? null
            );
    
            // Determine VAT and discount eligibility based on subscription
            $vatEnabled = $subscriptionPlan !== 'free' && $request->has('vat'); // Paid users only
            $vatRate = $vatEnabled ? ($validated['vat'] ?? 0) : 0;
            $discountRate = $subscriptionPlan !== 'free' ? ($validated['discount'] ?? 0) : 0;
    
            // Check if watermark should be included
            $includeWatermark = $subscriptionPlan === 'free' || $request->has('add_watermark');
    
            // Get selected tasks and calculate totals
            $selectedTasks = $request->input('selected_tasks', []);
            $tasks = $project->tasks()->whereIn('id', $selectedTasks)->get();
    
            // Separate and load related entities for each task type
            $projectTasks = $tasks->filter(fn($task) => $task->taskable_type === 'App\\Models\\TaskProject');
            $hourlyTasks = $tasks->filter(fn($task) => $task->taskable_type === 'App\\Models\\TaskHourly');
            $distanceTasks = $tasks->filter(fn($task) => $task->taskable_type === 'App\\Models\\TaskDistance');
            $productTasks = $tasks->filter(fn($task) => $task->taskable_type === 'App\\Models\\TaskProduct');
            $otherTasks = $tasks->filter(fn($task) => $task->taskable_type === 'App\\Models\\TaskOther');

            // Load relationships for each task type
            $projectTasks->load('taskable');
            $hourlyTasks->load('taskable.registrationHourly');
            $distanceTasks->load('taskable.registrationDistances');
            $productTasks->load('taskProduct.product');
            $otherTasks->load(['taskable', 'customFields', 'checklistSections.checklistItems']);

            // Calculate subtotal
            $subtotal = 0;
            foreach ($projectTasks as $task) {
                $subtotal += $task->taskable->price ?? 0;
            }
            foreach ($hourlyTasks as $task) {
                // Total minutes worked and earnings per minute calculation
                $totalMinutesWorked = $task->taskable->registrationHourly->sum('minutes_worked');
                $earningsPerMinute = ($task->taskable->rate_per_hour ?? 0) / 60;
                $hourlyEarnings = $totalMinutesWorked * $earningsPerMinute;
            
                // Round up to match the index view logic
                $subtotal += ceil($hourlyEarnings);
            }
            foreach ($distanceTasks as $task) {
                $distance = $task->taskable->registrationDistances->sum('distance');
                $subtotal += $distance * ($task->taskable->price_per_km ?? 0);
            }
    
            $products = collect();
            foreach ($productTasks as $task) {
                foreach ($task->taskProduct as $taskProduct) {
                    $product = $taskProduct->product;
                    $productPrice = $product->price ?? 0;
                    $quantitySold = $taskProduct->quantity ?? 0;
                    $productTotal = $productPrice * $quantitySold;

                    // Check if the product is a service and has attributes
                    $isService = $product->type === 'service';
                    $serviceAttributes = [];
                    if ($isService) {
                        $serviceAttributes = json_decode($taskProduct->attributes, true) ?? [];
                        foreach ($serviceAttributes as $attribute) {
                            $attributeTotal = ($attribute['price'] ?? 0) * ($attribute['quantity'] ?? 0);
                            $productTotal += $attributeTotal;
                        }
                    }

                    // Store each product with relevant details for the PDF
                    $products->push([
                        'product' => $product,
                        'total_sold' => $quantitySold,
                        'total_price' => $productTotal,
                        'is_service' => $isService,
                        'service_attributes' => $serviceAttributes,
                    ]);

                    $subtotal += $productTotal;
                }
            }
    
            // Apply VAT and discount conditionally
            $totalWithVat = $vatEnabled ? $subtotal * (1 + $vatRate / 100) : $subtotal;
            $totalWithVatAndDiscount = $totalWithVat * (1 - $discountRate / 100);

            // Filter out empty fields from header details
            $filteredHeaderDetails = array_filter($headerDetails, function ($value) {
                return !is_null($value) && $value !== '';
            });
    
            $data = [
                'project' => $project,
                'projectTasks' => $projectTasks,
                'hourlyTasks' => $hourlyTasks,
                'distanceTasks' => $distanceTasks,
                'products' => $products,
                'otherTasks' => $otherTasks,
                'subtotal' => $subtotal,
                'vat' => $vatRate,
                'discount' => $discountRate,
                'totalWithVat' => $vatEnabled ? $totalWithVat : null,
                'totalWithVatAndDiscount' => $vatEnabled ? $totalWithVatAndDiscount : null,
                'headerDetails' => $filteredHeaderDetails,
                'logoData' => $logoData,
                'includeNotes' => $includeNotes,
                'notesContent' => $includeNotes ? $notesContent : '',
                'includeSignature' => $includeSignature,
                'userPlan' => $subscriptionPlan,
                'includeWatermark' => $includeWatermark,
            ];
    
            // Generate and return the PDF
            $pdf = PDF::loadView('reports.project_report', $data);
            return $pdf->download('project_report_' . $project->id . '.pdf');
        } catch (\Exception $e) {
            \Log::error("Error generating report: " . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error generating the report.');
        }
    }          

    public function invoice(Project $project)
    {
        if ($project->status != 'completed') {
            abort(404); // Or redirect to a different page with an error message
        }

        // Check if an invoice already exists for the project
        $existingInvoice = $project->invoices->first(); // Assuming a 'invoices' relationship exists on the Project model
        if ($existingInvoice) {
            // Redirect or inform the user that an invoice already exists
            return redirect()->route('invoices.show', $existingInvoice->id)
                            ->with('warning', 'An invoice for this project already exists.');
        }

        // Fetch tasks related to the project
        $tasks = $project->tasks;

        // Separate project-based and hourly tasks
        $projectTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProject');
        $hourlyTasks = $tasks->where('taskable_type', 'App\\Models\\TaskHourly');
        $distanceTasks = $tasks->where('taskable_type', 'App\\Models\\TaskDistance');
        $productTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProduct');
        $otherTasks = $tasks->where('taskable_type', 'App\\Models\\TaskOther');

        // Load the related taskable entities
        $productTasks->load('taskProduct.product');

        // Extract products
        $products = collect();
        foreach ($productTasks as $task) {
            foreach ($task->taskProduct as $taskProduct) {
                // Assuming 'product' is the correct relationship name within 'TaskProduct' model
                $products->push([
                    'product' => $taskProduct->product,
                    'total_sold' => $taskProduct->total_sold,
                    'total_price' => $taskProduct->product->price * $taskProduct->total_sold
                ]);
            }
        }

        // Calculate default issue date and due date
        $issueDate = now()->toDateString();
        $dueDate = now()->addDays(30)->toDateString();

        // Calculate days between issue date and due date
        $daysBetween = \Carbon\Carbon::parse($dueDate)->diffInDays(\Carbon\Carbon::parse($issueDate));


        // Load the related taskable entities
        $projectTasks->load('taskable');
        $hourlyTasks->load('taskable.registrationHourly');
        $distanceTasks->load('taskable.registrationDistances');
        $productTasks->load('taskable');
        $otherTasks->load('taskable');

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

        $vat = $total * 0.25;
        $totalWithVat = $total * 1.25;

        // Fetch clients
        $clients = Client::all();

        return view('invoices.preview', compact('project', 'clients', 'total', 'vat', 'totalWithVat', 'daysBetween'));


        // // Pass data to the view
        // return view('invoices.edit', [
        //     'project' => $project,
        //     'projectTasks' => $projectTasks,
        //     'hourlyTasks' => $hourlyTasks,
        //     'distanceTasks' => $distanceTasks,
        //     'productTasks' => $productTasks,
        //     'products' => $products,
        //     'otherTasks' => $otherTasks,
        //     'total' => $total,
        //     'vat' => $vat,
        //     'totalWithVat' => $totalWithVat,
        // ]);

        // // Generate PDF
        // $pdf = PDF::loadView('invoices.show', [
        //     'project' => $project,
        //     'projectTasks' => $projectTasks,
        //     'hourlyTasks' => $hourlyTasks,
        //     'distanceTasks' => $distanceTasks,
        //     'productTasks' => $productTasks,
        //     'products' => $products,
        //     'otherTasks' => $otherTasks,
        //     'total' => $total,
        //     'vat' => $vat,
        //     'totalWithVat' => $totalWithVat,
        // ]);

        // // Save PDF to storage
        // $fileName = 'invoices/invoice_' . $project->id . '_' . time() . '.pdf';
        // Storage::put($fileName, $pdf->output());

        // // Find or create the invoice
        // $invoice = Invoice::firstOrNew(['project_id' => $project->id]);
        // $invoice->user_id = auth()->id();
        // $invoice->project_id = $project->id;
        // $invoice->client_id = $project->client ? $project->client->id : null;
        // $invoice->title = 'Invoice for Project ' . $project->id;
        // $invoice->total = $totalWithVat;
        // $invoice->vat = $vat;
        // $invoice->file_path = $fileName;
        // $invoice->save();

        // return $pdf->stream($project->id . '_invoice.pdf');
    }

    public function updateInvoiceStatus(Request $request, Project $project)
    {
        $project->update(['invoice_status' => $request->status]);

        return response()->json(['message' => 'Invoice status updated successfully']);
    }

    public function updateClient(Request $request, Project $project)
    {
        $project->update(['client_id' => $request->client_id]);

        // Update the client_id of all tasks belonging to the project
        foreach ($project->tasks as $task) {
            $task->update(['client_id' => $request->client_id]);
        }

        return response()->json(['message' => 'Client and tasks updated successfully']);
    }

    public function index()
    {
        $user = auth()->user();

        // Fetch the current user's projects, paginated
        $projects = Project::where('user_id', $user->id)->paginate(10);

        // Fetch the current user's clients
        $clients = Client::where('user_id', $user->id)->get();

        // Get the user's subscription plan
        $subscriptionPlan = app(\App\Services\PlanService::class)->getPlanNameByPriceId($user->subscription('default')?->stripe_price ?? null);

        // Get the project limit for the user's plan
        $projectLimit = app(\App\Services\PlanService::class)->getPlanLimits($subscriptionPlan)['projects'] ?? 3; // Default to 3 for 'free' plan

        // Get the current number of projects created by the user
        $projectCount = $projects->total();

        foreach ($projects as $project) {
            // Update project status
            $project->updateStatus();

            // Iterate over each task of the project
            foreach ($project->tasks as $task) {
                \Log::info("Checking taskable relation", [
                    'task_id' => $task->id,
                    'taskable_id' => $task->taskable_id,
                    'taskable_type' => $task->taskable_type
                ]);

                // Check if the task type is 'TaskProduct'
                if ($task->taskable_type === 'App\Models\TaskProduct') {
                    // Fetch related products manually for TaskProduct
                    $relatedProducts = TaskProduct::where('task_id', $task->id)->with('product')->get();

                    // Log task and related product details
                    foreach ($relatedProducts as $taskProduct) {
                        \Log::info("Task ID: {$task->id} is of type TaskProduct with related product.", [
                            'Task Details' => $task->toArray(),
                            'Product Details' => $taskProduct->product->toArray() ?? 'No product associated'
                        ]);
                    }
                }
            }
        }

        // Pass data to the view
        return view('projects.index', compact('projects', 'clients', 'projectCount', 'projectLimit'));
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        // Store the project ID in session
        session(['current_project_id' => $project->id]);

        $clients = Client::where('user_id', auth()->id())->get();

        return view('projects.show', compact('project', 'clients'));
    }


    public function create()
    {
        $clients = Client::where('user_id', auth()->id())->get();
        return view('projects.create', compact('clients'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        // Store the project ID in session
        session(['current_project_id' => $project->id]);

        $clients = Client::where('user_id', auth()->id())->get();

        return view('projects.edit', compact('project', 'clients'));
    }

    public function update(Request $request, Project $project)
    {
        // Authorize the update action
        $this->authorize('update', $project);

        $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->fill($request->all());

        // If a client ID was provided, associate the project with the client
        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
            $project->client()->associate($client);
        }

        $project->save();

        return redirect()->route('projects.index');
    }

    public function destroy(Project $project)
    {
        // Authorize the delete action
        $this->authorize('delete', $project);

        // Delete related data
        $project->tasks()->delete();
        $project->notes()->delete();
        $project->contracts()->delete();
        $project->invoices()->delete();

        // Delete the project
        $project->delete();

        return redirect()->route('projects.index');
    }

    public function getProjects(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Fetch projects for the authenticated user
        $projects = Project::where('user_id', auth()->id())->get();

        // Return the data in JSON format
        return response()->json($projects);
    }

    public function fetchProjects()
    {
        $projects = Project::where('user_id', auth()->id())->get(); // Adjust based on your logic
        return response()->json($projects);
    }

    public function sendProjectToDinero(Request $request, $id)
    {
        // Retrieve the project by its ID
        $project = Project::with('tasks')->findOrFail($id);

        // Ensure the project is completed before sending
        if ($project->status !== 'completed') {
            return response()->json(['error' => 'Project is not completed yet'], 400);
        }

        // Format the project and tasks data for Dinero
        $invoiceData = [
            "contact_id" => $project->client_id, // Assuming this maps to a Dinero contact
            "date" => now()->format('Y-m-d'),
            "lines" => $project->tasks->map(function ($task) {
                // Customize the mapping based on the task type and Dinero's requirements
                return [
                    "description" => $task->title,
                    "quantity" => 1, // Assuming 1 unit for simplicity; adjust as needed
                    "unit_price" => $task->taskable->price ?? 0, // Assuming `price` is in the taskable models
                ];
            })->toArray(),
            // Add any additional fields Dinero requires
        ];

        // Send the data to Dinero
        $response = Http::withToken($request->input('dinero_api_key'))
                        ->post('https://api.dinero.dk/v1/invoices', $invoiceData);

        // Handle the response from Dinero
        if ($response->successful()) {
            return response()->json(['message' => 'Project data sent to Dinero successfully']);
        } else {
            return response()->json(['error' => 'Failed to send project data to Dinero'], $response->status());
        }
    }
}
