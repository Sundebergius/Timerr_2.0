<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
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
            'title' => 'required|string',
            'name' => 'required|string',
            // 'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'price' => 'nullable|numeric',
            'project_location' => 'nullable|string',
        ])->validate();

        // Create a new task
        $task = Task::create([
            'project_id' => $validatedData['project_id'],
            'user_id' => $validatedData['user_id'],
            'title' => $validatedData['title'],
            'name' => $validatedData['name'],
            // 'description' => $validatedData['description'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'price' => $validatedData['price'],
            'project_location' => $validatedData['project_location'],
            'task_type' => 'project_based', // Set the task type to project_based
        ]);
    }

    private function createHourlyTask(array $data)
    {
        // Logic for creating an hourly task
        // ...
    }

    // Other methods for updating, deleting, and retrieving tasks
    // ...
}
