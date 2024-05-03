<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Task;
use App\Models\RegistrationProject;
use App\Models\TaskProject;
use App\Models\TaskHourly;
use App\Models\TaskDistance;
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
        }

    }

    public function create(Project $project)
    {
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
                'title' => $validatedData['task_title'],
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
        
            // Update the TaskProject with the task_id
            $taskProject->update(['task_id' => $task->id]);
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
            //'minutes_worked' => 'required|numeric',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($validatedData['project_id']);

        // Convert the hourly rate to a rate per minute using bcdiv for precision
        $ratePerMinute = bcdiv($validatedData['rate_per_hour'], 60, 10);
        
        DB::transaction(function () use ($validatedData, $project, $ratePerMinute) {
            // Create a new TaskHourly without task_id
            $taskHourly = TaskHourly::create([
                'title' => $validatedData['task_title'],
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
    
            // Update the TaskHourly with the task_id
            $taskHourly->update(['task_id' => $task->id]);
        });
    }

    public function createDistanceTask(Request $request)
    {
        // Validate the data
        $validatedData = Validator::make($request->all(), [
            'title' => 'required|string',
            'price_per_km' => 'required|numeric',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($request->project_id);

        DB::transaction(function () use ($validatedData, $request, $project) {
            // Create a new TaskDistance without task_id
            $taskDistance = TaskDistance::create([
                'title' => $validatedData['title'],
                'distance' => $validatedData['distance'] ?? 0,
                'price_per_km' => $validatedData['price_per_km'],
            ]);
    
            // Create a new task
            $task = Task::create([
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
                'title' => $validatedData['title'],
                'task_type' => 'distance',
                'taskable_id' => $taskDistance->id,
                'taskable_type' => TaskDistance::class,
                'client_id' => $project->client_id ?? null,
            ]);
    
            // Update the TaskDistance with the task_id
            $taskDistance->update(['task_id' => $task->id]);
        });
    }

    public function createOtherTask(Request $request)
    {
        // Validate the data
        $validatedData = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
        ])->validate();

        // Fetch the project
        $project = Project::findOrFail($request->project_id);

        DB::transaction(function () use ($validatedData, $request, $project) {
            // Create a new OtherTask without task_id
            $otherTask = OtherTask::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
            ]);
    
            // Create a new task
            $task = Task::create([
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
                'title' => $validatedData['title'],
                'task_type' => 'other',
                'taskable_id' => $taskDistance->id,
                'taskable_type' => TaskOther::class,
                'client_id' => $project->client_id ?? null,
            ]);
    
            // Update the TaskDistance with the task_id
            $taskDistance->update(['task_id' => $task->id]);
        });
    }


    // Other methods for updating, deleting, and retrieving tasks
    // ...
}
