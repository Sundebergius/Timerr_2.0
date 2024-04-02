<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\RegistrationProject;
use App\Models\TaskProject;
use App\Models\TaskHourly;
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
        }

        // Redirect or return response
        // ...
    }

    public function create(Project $project)
    {
        // Convert $project to a JSON string
        // $projectJson = json_encode($project->toArray());
        // return view('tasks.create', ['project' => $projectJson]);
        return view('tasks.create', ['project' => $project]);
        
    }

    private function createProjectBasedTask(array $data)
    {
        // Validate the data
        $validatedData = Validator::make($data, [
            'user_id' => 'required|integer',
            'project_id' => 'required|integer',
            'task_title' => 'required|string', // Changed from 'title'
            //'project_title' => 'required|string', // New validation rule
            //'name' => 'required|string',
            // 'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string', // Add validation for 'currency'
            'project_location' => 'nullable|string',
        ])->validate();

        // Create a new TaskProject
        $taskProject = TaskProject::create([
            //'user_id' => $validatedData['user_id'],
            //'task_id' => $task->id,
            'title' => $validatedData['task_title'],
            // 'description' => $validatedData['description'],
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
            'task_type' => 'project_based', // Set the task type to project_based
            'taskable_id' => $taskProject->id,
            'taskable_type' => TaskProject::class,
        ]); 
    }

    private function createHourlyTask(array $data)
    {
        // Validate the data
        $validatedData = Validator::make($data, [
            'user_id' => 'required|integer',
            'project_id' => 'required|integer',
            'task_title' => 'required|string',
            'rate_per_hour' => 'required|numeric',
            //'hours' => 'required|numeric',
        ])->validate();

        // Convert the hourly rate to a rate per minute
        $ratePerMinute = $validatedData['rate_per_hour'] / 60;

        // Create a new TaskHourly
        $taskHourly = TaskHourly::create([
            //'user_id' => $validatedData['user_id'],
            //'task_id' => $validatedData['task_id'],
            'title' => $validatedData['task_title'],
            'rate_per_hour' => $validatedData['rate_per_hour'],
            'rate_per_minute' => $ratePerMinute, // Changed from 'hourly_rate'
        ]);

        // // Create a new RegistrationHourly
        // $registrationHourly = RegistrationHourly::create([
        //     'user_id' => $validatedData['user_id'],
        //     'title' => $validatedData['task_title'],
        //     'rate_per_minute' => $ratePerMinute,
        //     //'hourly_rate' => $validatedData['rate'],
        //     //'hours' => $validatedData['hours'],
        // ]);

        $task = Task::create([
            'project_id' => $validatedData['project_id'],
            'user_id' => $validatedData['user_id'],
            'title' => $validatedData['task_title'],
            'task_type' => 'hourly', // Set the task type to hourly
            'taskable_id' => $taskHourly->id,
            'taskable_type' => TaskHourly::class,
        ]);
    }

    // Other methods for updating, deleting, and retrieving tasks
    // ...
}
