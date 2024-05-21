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

        switch ($data['task_type']) {
            case 'project_based':
                $this->createProjectBasedTask($data);
                break;
            case 'hourly':
                $this->createHourlyTask($data);
                break;
            case 'distance':
                $this->createDistanceTask($data);
                break;
            case 'product': // added this case
                $this->createProductTask($data); // call a method to create a product task
                break;
            case 'other': // added this case
                $this->createOtherTask($data); // call a method to create an other task
                break;
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

        DB::transaction(function () use ($validatedData, $project) {
            // Create a new TaskProject without task_id
            $taskProject = TaskProject::create([
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'price' => $validatedData['price'],
                'currency' => $validatedData['currency'],
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

    public function createDistanceTask(Request $request)
    {
        // Validate the data
        $validatedData = Validator::make($request->all(), [
            'project_id' => 'required|integer',
            'task_title' => 'required|string',
            'price_per_km' => 'required|numeric',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($request->project_id);

        DB::transaction(function () use ($validatedData, $request, $project) {
            // Create a new TaskDistance without task_id
            $taskDistance = TaskDistance::create([
                //'distance' => $validatedData['distance'] ?? 0,
                'price_per_km' => $validatedData['price_per_km'],
            ]);
    
            // Create a new task
            $task = Task::create([
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
                'title' => $validatedData['task_title'],
                'task_type' => 'distance',
                'taskable_id' => $taskDistance->id,
                'taskable_type' => TaskDistance::class,
                'client_id' => $project->client_id ?? null,
            ]);
        });
    }

    public function createProductTask(Request $request)
    {
        Log::info('Request data: ', $request->all());

        $validatedData = Validator::make($request->all(), [
            'project_id' => 'required|integer',
            'task_title' => 'required|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($request->project_id);

        DB::transaction(function () use ($validatedData, $request, $project) {
            try {
                // Create a new Task
                $task = Task::create([
                    'project_id' => $request->project_id,
                    'user_id' => $request->user_id,
                    'title' => $validatedData['task_title'],
                    'task_type' => 'product',
                    'client_id' => $project->client_id ?? null,
                    // 'taskable_id' => $taskProduct->id,
                    'taskable_type' => TaskProduct::class,
                ]);

                // Create TaskProduct entries
                foreach ($validatedData['products'] as $productData) {
                    TaskProduct::create([
                        'task_id' => $task->id,
                        'product_id' => $productData['product_id'],
                        'total_sold' => $productData['quantity'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to create task and task product: ' . $e->getMessage());
            }
        });
    }

    public function createOtherTask(Request $request)
    {
        Log::info('createOtherTask method called');
        // Validate the data
        $validatedData = Validator::make($request->all(), [
            'task_title' => 'required|string',
            'description' => 'required|string',
            'customFields' => 'nullable|array',
            'checklistSections' => 'nullable|array',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($request->project_id);

        DB::transaction(function () use ($validatedData, $request, $project) {
            // Create a new OtherTask without task_id
            $taskOther = TaskOther::create([
                'description' => $validatedData['description'],
            ]);
    
            // Create a new task
            $task = Task::create([
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
                'title' => $validatedData['task_title'],
                'task_type' => 'other',
                'taskable_id' => $taskOther->id,
                'taskable_type' => TaskOther::class,
                'client_id' => $project->client_id ?? null,
            ]);

             // Handle customFields and checklistSections
            if (isset($validatedData['customFields'])) {
                $position = 0;
                foreach ($validatedData['customFields'] as $field) {
                    $value = $field['value'];
                    $position = $field['position'];

                    CustomField::create([
                        'task_id' => $task->id,
                        'field' => trim($value) !== '' ? $value : null, // Set 'field' to null if it's empty
                        'position' => $position,
                    ]);
            
                    Log::info('Position: '.$position);
                    $position++; // Increment the counter for each custom field
                }
            }

            if (isset($validatedData['checklistSections'])) {
                foreach ($validatedData['checklistSections'] as $section) {
                    if (trim($section['title']) !== '') {
                        $createdSection = ChecklistSection::create([
                            'title' => $section['title'],
                            'task_id' => $task->id,
                        ]);
            
                        // Check if the ChecklistSection was created successfully
                        if (!$createdSection) {
                            Log::error('Failed to create ChecklistSection');
                            continue;
                        }
            
                        $position = 0;
                        foreach ($section['items'] as $item) {
                            if (trim($item) !== '') {
                                $createdItem = ChecklistItem::create([
                                    'checklist_section_id' => $createdSection->id,
                                    'item' => $item,
                                    'position' => $position,
                                ]);
            
                                // Check if the ChecklistItem was created successfully
                                if (!$createdItem) {
                                    Log::error('Failed to create ChecklistItem for section: ' . $createdSection->id);
                                }
                                $position++;
                            }
                        }
                    }
                }
            }
        });
    }

    public function show(Project $project, Task $task)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($task->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('tasks.show', ['task' => $task]);
    }

    public function edit(Project $project, Task $task)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($task->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('tasks.edit', ['task' => $task]);
    }

    public function destroy(Project $project, Task $task)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($task->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Call the deleteWithRegistrations method on the taskable relation
        $task->taskable->deleteWithRegistrations();

        // Delete the task
        $task->delete();

        return redirect()->route('projects.show', $task->project);
    }


    // Other methods for updating, deleting, and retrieving tasks
    // ...
}
