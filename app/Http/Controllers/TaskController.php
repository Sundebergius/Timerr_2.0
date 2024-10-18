<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\Task;
use App\Models\Product;
use App\Models\RegistrationProject;
use App\Models\TaskProject;
use App\Models\TaskHourly;
use App\Models\TaskDistance;
use App\Models\TaskProduct;
use App\Models\TaskOther;
use App\Models\CustomField;
use App\Models\ChecklistSection;
use App\Models\ChecklistItem;
use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    //old store method 
    // public function store(Request $request, Project $project)
    // {
    //     $request->validate([
    //         'title' => 'required',
    //         'start_date' => 'required|date',
    //         'end_date' => 'nullable|date',
    //         'location' => 'required',
    //         'type' => 'required',
    //     ]);

    //     $task = new Task;
    //     $task->title = $request->title;
    //     $task->start_date = $request->start_date;
    //     $task->end_date = $request->end_date ? $request->end_date : null;
    //     $task->location = $request->location;
    //     $task->type = $request->type;
    //     $task->project_id = $project->id; // Set the project_id to the id of the project

    //     $task->save();

    //     // Redirect to the show route for the project
    //     return redirect()->route('projects.show', $project);
    // }

    public function store(Request $request)
    {
        // Fetch the project
        $project = Project::findOrFail($request->project_id);

        // Abort if the authenticated user is not the owner of the project
        if ($project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        error_log(print_r($request->all(), true));
        $data = $request->all();

        // Add the client_id to the data array
        $data['client_id'] = $project->client_id;

        // Initialize $task variable
        $task = null;

        switch ($data['task_type']) {
            case 'project_based':
                $task = $this->createProjectBasedTask($data);
                break;
            case 'hourly':
                $task = $this->createHourlyTask($data);
                break;
            case 'distance':
                $task = $this->createDistanceTask($data);
                break;
            case 'product':
                $task = $this->createProductTask($data);
                break;
            case 'other':
                $task = $this->createOtherTask($data);
                break;
        }

       // Trigger webhook after task creation
        // if ($task) {
        //     $this->triggerTaskCreatedWebhook($task);
        // } else {
        //     error_log('Task creation failed or task type was invalid.');
        // }
    }

    protected function triggerTaskCreatedWebhook(Task $task)
    {
        \Log::info('Triggering webhook for task created', ['task_id' => $task->id]);

        $webhooks = Webhook::where('event', 'task_created')
                            ->where('active', true)
                            ->where('user_id', auth()->id())
                            ->get();

        foreach ($webhooks as $webhook) {
            $payload = $this->prepareTaskData($task);
            $this->sendWebhookNotification($webhook, $payload);
        }
    }

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

    public function completeTask(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Perform the task completion logic
        $task->status = 'completed'; // Assuming you have a status field
        $task->save();

        // Trigger webhook after task completion
        $this->triggerTaskCompletedWebhook($task);

        return redirect()->route('tasks.show', $task);
    }

    protected function triggerTaskCompletedWebhook(Task $task)
    {
        $webhooks = Webhook::where('event', 'task_completed')
                            ->where('active', true)
                            ->where('user_id', auth()->id())
                            ->get();

        foreach ($webhooks as $webhook) {
            $payload = $this->prepareTaskData($task);
            $this->sendWebhookNotification($webhook, $payload);
        }
    }

    protected function sendWebhookNotification(Webhook $webhook, array $payload)
    {
        $response = Http::post($webhook->url, $payload);

        if (!$response->successful()) {
            \Log::error('Failed to send webhook notification', [
                'webhook_url' => $webhook->url,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
        }
    }

    public function create(Project $project)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('tasks.create', ['project' => $project]); 
    }

    private function createProjectBasedTask(array $data)
    {
        // Validate the data
        $validatedData = Validator::make($data, [
            'user_id' => 'required|integer',
            'project_id' => 'required|integer',
            'task_title' => 'required|string', 
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string', 
            'project_location' => 'nullable|string',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($validatedData['project_id']);

        return DB::transaction(function () use ($validatedData, $project) {
            // Create a new TaskProject without task_id
            $taskProject = TaskProject::create([
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'price' => $validatedData['price'],
                'currency' => 'DKK',  // Hardcode currency as 'DKK'
                'project_location' => $validatedData['project_location'],
            ]);
        
            // Create a new task
            $task = Task::create([
                'project_id' => $validatedData['project_id'],
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['task_title'],
                'task_type' => 'project_based', 
                'client_id' => $project->client_id ?? null, 
                'taskable_id' => $taskProject->id, 
                'taskable_type' => TaskProject::class, 
            ]);

            return $task;
        });
    }

    private function createHourlyTask(array $data)
    {
        // Validate the data
        $validatedData = Validator::make($data, [
            'project_id' => 'required|integer',
            'task_title' => 'required|string',
            'rate_per_hour' => 'required|numeric',
            'user_id' => 'required|integer',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($validatedData['project_id']);

        // Convert the hourly rate to a rate per minute using bcdiv for precision
        $ratePerMinute = bcdiv($validatedData['rate_per_hour'], 60, 10);
        
        DB::transaction(function () use ($validatedData, $project, $ratePerMinute) {
            // Create a new TaskHourly without task_id
            $taskHourly = TaskHourly::create([
                'rate_per_hour' => $validatedData['rate_per_hour'],
                'rate_per_minute' => $ratePerMinute,
            ]);
    
            // Create a new task
            $task = Task::create([
                'project_id' => $validatedData['project_id'],
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['task_title'],
                'task_type' => 'hourly',
                'taskable_id' => $taskHourly->id,
                'taskable_type' => TaskHourly::class,
                'client_id' => $project->client_id ?? null,
            ]);
        });
    }

    public function createDistanceTask(array $data)
    {
        // Validate the data
        $validatedData = Validator::make($data, [
            'project_id' => 'required|integer',
            'task_title' => 'required|string',
            'price_per_km' => 'required|numeric',
            'user_id' => 'required|integer', // Add this validation for user_id
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($validatedData['project_id']);
        
        DB::transaction(function () use ($validatedData, $project) {
            // Create a new TaskDistance without task_id
            $taskDistance = TaskDistance::create([
                'price_per_km' => $validatedData['price_per_km'],
            ]);

            // Create a new task
            $task = Task::create([
                'project_id' => $validatedData['project_id'],
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['task_title'],
                'task_type' => 'distance',
                'taskable_id' => $taskDistance->id,
                'taskable_type' => TaskDistance::class,
                'client_id' => $project->client_id ?? null,
            ]);
        });
    }


    public function createProductTask(array $data)
    {
        Log::info('Request data: ', $data);

        // Validate the data
        $validatedData = Validator::make($data, [
            'project_id' => 'required|integer',
            'task_title' => 'required|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0',
            'products.*.type' => 'required|string|in:product,service',
            'products.*.total_price' => 'required|numeric|min:0', // Validate total_price
            'products.*.attributes' => 'array',
            'products.*.attributes.*.attribute' => 'required_if:products.*.type,service|string|max:255',
            'products.*.attributes.*.quantity' => 'required_if:products.*.type,service|integer|min:0',
            'products.*.attributes.*.price' => 'required_if:products.*.type,service|numeric|min:0',
            'user_id' => 'required|integer',  // Add validation for user_id
        ])->validate();

        Log::info('Validated data: ', $validatedData);

        // Fetch the project
        $project = Project::findOrFail($validatedData['project_id']);

        DB::transaction(function () use ($validatedData, $project) {
            try {
                // Create the main task for the product
                $task = Task::create([
                    'project_id' => $validatedData['project_id'],
                    'user_id' => $validatedData['user_id'],
                    'title' => $validatedData['task_title'],
                    'task_type' => 'product',
                    'client_id' => $project->client_id ?? null,
                    'taskable_type' => TaskProduct::class,
                ]);

                // Loop through the products and handle each one
                foreach ($validatedData['products'] as $productData) {
                    Log::info('Processing product data: ', $productData);

                    // Store the attributes as JSON if it's a service
                    $attributes = $productData['type'] === 'service' 
                        ? json_encode($productData['attributes']) 
                        : null;

                    // Create a TaskProduct entry for each product
                    $taskProduct = TaskProduct::create([
                        'task_id' => $task->id,
                        'product_id' => $productData['product_id'],
                        'type' => $productData['type'],
                        'quantity' => $productData['quantity'],
                        'attributes' => $attributes, // JSON data for services, null for physical products
                        'total_price' => $productData['total_price'], // Save the total price for both products and services
                    ]);
                    
                }
            } catch (\Exception $e) {
                Log::error('Failed to create task and task product: ' . $e->getMessage());
            }
        });
    }

    public function show(Project $project, Task $task)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($task->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('tasks.show', ['task' => $task, 'project' => $project]);
    }

    public function edit(Project $project, Task $task)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($task->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Get the products that belong to the authenticated user
        $products = Product::where('user_id', auth()->id())->get();

        return view('tasks.edit', ['task' => $task, 'project' => $project, 'products' => $products]);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        \Log::info('Update function called with task type:', ['task_type' => $task->task_type]); // Log the task type

        // Abort if the authenticated user is not the owner of the project
        if ($task->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $data = $request->all();

        // Get the products that belong to the authenticated user
        $products = Product::where('user_id', auth()->id())->get();
        $data['products'] = $products;

        switch ($task->task_type) {
            case 'project_based':
                $this->updateProjectBasedTask($data, $task);
                break;
            case 'hourly':
                $this->updateHourlyTask($data, $task);
                break;
            case 'distance':
                \Log::info('Updating distance task with data:', $data); // Log the data for the 'distance' task type
                $this->updateDistanceTask($data, $task);
                break;
            case 'product':
                $this->updateProductTask($request, $task);
                break;
            case 'other':
                $this->updateOtherTask($data, $task);
                break;
        }

        return redirect()->route('projects.show', $project);
    }

    private function updateProjectBasedTask(array $data, Task $task)
    {
        $validator = Validator::make($data, [
            'user_id' => 'required|integer',
            'title' => 'required|string',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'location' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $validatedData = $validator->validate();
    
        DB::transaction(function () use ($validatedData, $task) {
            $task->taskable->update([
                'start_date' => $validatedData['startDate'],
                'end_date' => $validatedData['endDate'],
                'price' => $validatedData['price'],
                'currency' => 'DKK',  // Hardcode currency as 'DKK'
                'project_location' => $validatedData['location'],
            ]);
    
            $task->update([
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['title'],
            ]);
        });
    }

    private function updateHourlyTask(array $data, Task $task)
    {
        try {
            // Validate the data
            $validatedData = Validator::make($data, [
                'title' => 'required|string',
                'hourly_wage' => 'required|numeric',
                'user_id' => 'required|integer',
                'registrations.*.hours' => 'required|integer',
                'registrations.*.minutes' => 'required|integer',
                'registrations.*._delete' => 'sometimes|string|in:true', // Add this line

            ])->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in updateHourlyTask: ', $e->errors());
            throw $e;
        }
        \Log::info('Registrations: ', $validatedData['registrations']);

    
        // Convert the hourly rate to a rate per minute using bcdiv for precision
        $ratePerMinute = bcdiv($validatedData['hourly_wage'], 60, 10);
    
        DB::transaction(function () use ($validatedData, $task, $ratePerMinute) {
            // Update the TaskHourly
            $task->taskable->update([
                'rate_per_hour' => $validatedData['hourly_wage'],
                'rate_per_minute' => $ratePerMinute,
            ]);
    
            // Update the task
            $task->update([
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['title'],
            ]);
    
            // Update or delete the RegistrationHourly models
            foreach ($validatedData['registrations'] as $id => $registration) {
                if (isset($registration['_delete'])) {
                    \Log::info('Attempting to delete registration: ', ['id' => $id]);
                    app('App\Http\Controllers\RegistrationController')->deleteHourlyRegistration($id);
                    \Log::info('Registration deleted successfully: ', ['id' => $id]);
                } else {
                    $minutesWorked = $registration['hours'] * 60 + $registration['minutes'];
                    $task->taskable->registrationHourly()->where('id', $id)->update([
                        'minutes_worked' => $minutesWorked,
                        'earnings' => $minutesWorked * $ratePerMinute,
                    ]);
                    \Log::info('Registration updated successfully: ', ['id' => $id]);
                }
            }
        });
    }

    private function updateDistanceTask(array $data, Task $task)
    {
        // Validate the data
        $validatedData = Validator::make($data, [
            'title' => 'required|string',
            'price_per_km' => 'required|numeric',
            'registrations.*.distance' => 'required|numeric',
            'registrations.*._delete' => 'sometimes|string|in:true',
        ])->validate();

        DB::transaction(function () use ($validatedData, $task) {
            // Update the TaskDistance
            $task->taskable->update([
                'price_per_km' => $validatedData['price_per_km'],
            ]);

            // Update the task
            $task->update([
                'title' => $validatedData['title'],
            ]);

            // Update or delete the RegistrationDistance models
            foreach ($validatedData['registrations'] as $id => $registration) {
                if (isset($registration['_delete'])) {
                    \Log::info('Attempting to delete registration: ', ['id' => $id]);
                    app('App\Http\Controllers\RegistrationController')->deleteDistanceRegistration($id);
                    \Log::info('Registration deleted successfully: ', ['id' => $id]);
                } else {
                    $distanceTravelled = $registration['distance'];
                    $task->taskable->registrationDistances()->where('id', $id)->update([
                        'distance' => $distanceTravelled,
                    ]);
                    \Log::info('Registration updated successfully: ', ['id' => $id]);
                }
            }
        });
    }

    public function updateProductTask(Request $request, Task $task)
{
    Log::info('Request data: ', $request->all());

    // Validation rules for both products and services
    $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'items' => 'array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.type' => 'required|in:product,service',
        'items.*.quantity' => 'nullable|integer|min:1',
        'items.*._delete' => 'nullable|in:true,false', // Allow deletion flag for products
        'items.*.attributes' => 'nullable|array', // For services
        'items.*.attributes.*.quantity' => 'nullable|integer|min:1', // Attribute quantities for services
        'items.*.attributes.*.price' => 'nullable|numeric|min:0', // Attribute prices for services
        'items.*.attributes.*._delete' => 'nullable|in:true,false', // Allow deletion flag for service attributes
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        Log::info('Validation failed: ', $validator->errors()->all());
        return response()->json($validator->errors(), 400);
    }

    $validatedData = $validator->validated();

    Log::info('Validation passed');
    Log::info('Validated Data: ', $validatedData);

    // Start transaction to update task and its products/services
    DB::transaction(function () use ($validatedData, $task) {
        try {
            // Update the task's title
            $task->update([
                'user_id' => request()->user_id,
                'title' => $validatedData['title'],
            ]);

            // Retrieve existing TaskProduct entries
            $existingTaskProducts = TaskProduct::where('task_id', $task->id)->get()->keyBy('product_id');

            // Loop through all products/services
            foreach ($validatedData['items'] as $productData) {
                $productID = $productData['product_id'];
                $type = $productData['type'];

                // Check if the product is marked for deletion
                if (isset($productData['_delete']) && $productData['_delete'] === 'true') {
                    if ($existingTaskProducts->has($productID)) {
                        $existingTaskProducts->get($productID)->delete();
                    }
                    continue; // Skip further processing if product is deleted
                }

                // Handle service attribute deletions
                $newQuantity = $productData['quantity'] ?? 0;
                $attributes = [];

                if ($type === 'service' && isset($productData['attributes'])) {
                    foreach ($productData['attributes'] as $attribute => $data) {
                        // Check if the attribute is marked for deletion
                        if (!isset($data['_delete']) || $data['_delete'] !== 'true') {
                            // Keep attributes not marked for deletion
                            $attributes[] = [
                                'attribute' => $attribute,
                                'quantity' => $data['quantity'],
                                'price' => $data['price'] ?? 0, // Default to 0 if price is missing
                            ];
                        }
                    }

                    // Recalculate total quantity based on remaining attributes
                    $newQuantity = array_sum(array_column($attributes, 'quantity'));

                    // If all attributes are deleted, mark the entire service for deletion
                    if (empty($attributes)) {
                        $existingTaskProducts->get($productID)->delete();
                        continue;
                    }
                }

                if ($existingTaskProducts->has($productID)) {
                    $existingTaskProduct = $existingTaskProducts->get($productID);

                    // Update quantity and attributes
                    $existingTaskProduct->update([
                        'quantity' => $newQuantity,
                        'attributes' => isset($attributes) ? json_encode($attributes) : null,
                    ]);
                } else {
                    // Handle new products/services
                    TaskProduct::create([
                        'task_id' => $task->id,
                        'product_id' => $productID,
                        'type' => $type,
                        'quantity' => $newQuantity,
                        'attributes' => isset($attributes) ? json_encode($attributes) : null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update task and task product: ' . $e->getMessage());
            throw $e;
        }
    });

    return response()->json(['message' => 'Task updated successfully.']);
}

public function createOtherTask(array $data)
{
    Log::info('createOtherTask method called');

    // Validate the array data
    $validator = Validator::make($data, [
        'project_id' => 'required|integer',
        'task_title' => 'required|string',
        'description' => 'required|string',
        'user_id' => 'required|integer',
        'customFields' => 'nullable|array',
        'checklistSections' => 'nullable|array',
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed: ', $validator->errors()->toArray());
        return response()->json($validator->errors(), 400);
    }

    $validatedData = $validator->validated();
    Log::info('Validated Data: ' . print_r($validatedData, true));

    // Start a database transaction for creating a new task
    $task = DB::transaction(function () use ($validatedData) {
        try {
            // Create a new TaskOther model
            $otherTask = TaskOther::create([
                'description' => $validatedData['description'],
            ]);

            // Create the main task with the newly created TaskOther ID
            $task = Task::create([
                'title' => $validatedData['task_title'],
                'task_type' => 'other',
                'taskable_type' => TaskOther::class,
                'taskable_id' => $otherTask->id,
                'project_id' => $validatedData['project_id'],
                'user_id' => $validatedData['user_id'],
            ]);

            // Handle customFields
            if (isset($validatedData['customFields'])) {
                foreach ($validatedData['customFields'] as $fieldData) {
                    if (!empty(trim($fieldData['value']))) {
                        Log::info('Creating new custom field for task_id: ' . $task->id . ' with field: ' . $fieldData['value']);
                        CustomField::create([
                            'task_id' => $task->id,
                            'field' => $fieldData['value'],
                            'position' => CustomField::where('task_id', $task->id)->max('position') + 1,
                        ]);
                    }
                }
            }

            // Handle checklistSections
            if (isset($validatedData['checklistSections'])) {
                foreach ($validatedData['checklistSections'] as $sectionData) {
                    $createdSection = ChecklistSection::create([
                        'task_id' => $task->id,
                        'title' => $sectionData['title'],
                    ]);

                    if (isset($sectionData['items'])) {
                        foreach ($sectionData['items'] as $itemData) {
                            ChecklistItem::create([
                                'checklist_section_id' => $createdSection->id,
                                'item' => $itemData,
                                'position' => ChecklistItem::where('checklist_section_id', $createdSection->id)->max('position') + 1,
                            ]);
                        }
                    }
                }
            }

            Log::info('Other task successfully created.');
            return $task; // Return the created task
        } catch (\Exception $e) {
            Log::error('Failed to create other task: ' . $e->getMessage());
            throw $e;
        }
    });

    // Return a success response
    return response()->json(['message' => 'Task created successfully.']);
}




private function updateOtherTask(array $data, Task $task)
{
    // Validate the data
    Log::info('updateOtherTask method called');

    $validator = Validator::make($data, [
        'title' => 'required|string',
        'description' => 'required|string',
        'customFields' => 'nullable|array',
        'checklistSections' => 'nullable|array',
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed: ', $validator->errors()->toArray());
        return;
    }

    $validatedData = $validator->validated();
    Log::info('Validated Data: ' . print_r($validatedData, true));

    DB::transaction(function () use ($validatedData, $task) {
        // Update the TaskOther
        $task->taskable->update([
            'description' => $validatedData['description'],
        ]);

        // Update the task
        $task->update([
            'title' => $validatedData['title'],
        ]);

        // Handle customFields
        if (isset($validatedData['customFields'])) {
            foreach ($validatedData['customFields'] as $fieldId => $fieldData) {
                if (strpos($fieldId, 'new_') === 0) {
                    // Create a new custom field
                    $maxPosition = CustomField::where('task_id', $task->id)->max('position') ?? 0;
                    if (!empty(trim($fieldData['field']))) {
                        CustomField::create([
                            'task_id' => $task->id,
                            'field' => $fieldData['field'],
                            'position' => ++$maxPosition,
                        ]);
                    }
                } else {
                    // Existing custom field
                    if (isset($fieldData['_delete']) && $fieldData['_delete'] === 'true') {
                        CustomField::where('id', $fieldId)->delete();
                    } else {
                        CustomField::updateOrCreate(
                            ['id' => $fieldId, 'task_id' => $task->id],
                            ['field' => $fieldData['field']]
                        );
                    }
                }
            }
        }

        // Handle checklistSections
        if (isset($validatedData['checklistSections'])) {
            foreach ($validatedData['checklistSections'] as $sectionId => $sectionData) {
                if (isset($sectionData['_delete']) && $sectionData['_delete'] === 'true') {
                    ChecklistSection::where('id', $sectionId)->delete();
                } else {
                    $createdSection = ChecklistSection::updateOrCreate(
                        ['id' => $sectionId, 'task_id' => $task->id],
                        ['title' => $sectionData['title']]
                    );

                    if (isset($sectionData['items'])) {
                        foreach ($sectionData['items'] as $itemId => $itemData) {
                            if (isset($itemData['_delete']) && $itemData['_delete'] === 'true') {
                                ChecklistItem::where('id', $itemId)->delete();
                            } else {
                                $maxItemPosition = ChecklistItem::where('checklist_section_id', $createdSection->id)->max('position') ?? 0;
                                ChecklistItem::updateOrCreate(
                                    ['id' => $itemId, 'checklist_section_id' => $createdSection->id],
                                    ['item' => $itemData['item'], 'position' => ++$maxItemPosition]
                                );
                            }
                        }
                    }
                }
            }
        }
    });
}



    public function destroy(Project $project, Task $task)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($task->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Call the deleteWithRegistrations method on the taskable relation if it exists
        if ($task->taskable) {
            $task->taskable->deleteWithRegistrations();
        }

        // Delete the task
        $task->delete();

        return redirect()->route('projects.show', $task->project);
    }


    // Other methods for updating, deleting, and retrieving tasks
    // ...
}
