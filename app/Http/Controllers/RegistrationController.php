<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\RegistrationProject;
use App\Models\RegistrationHourly;
use App\Models\RegistrationDistance;

class RegistrationController extends Controller
{
    public function storeProjectRegistration(Request $request, $projectId, $taskId)
    {
        $validatedData = $request->validate([
            'type' => 'required|string',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'amount' => 'nullable|numeric',
            'location' => 'nullable|string',
        ]);
    
        $task = Task::find($taskId);
    
        RegistrationProject::create([
            'user_id' => $task->user_id,
            'task_id' => $taskId,
            'title' => $task->title,
            'type' => $validatedData['type'],
            'description' => $validatedData['description'],
            'date' => $validatedData['date'],
            'amount' => $validatedData['amount'],
            'currency' => 'DKK',
            'location' => $validatedData['location'],
        ]);
    
        return redirect()->route('projects.show', ['project' => $task->project_id]);
    }

    public function storeHourlyRegistration(Request $request, $projectId, $taskId)
    {
        $validatedData = $request->validate([
            'hours_worked' => 'required|integer',
            'minutes_worked' => 'required|integer',
        ]);

        $task = Task::find($taskId);
        $taskHourly = $task->taskable;

        $totalMinutes = $validatedData['hours_worked'] * 60 + $validatedData['minutes_worked'];
        $earningsPerMinute = $taskHourly->rate_per_hour / 60;
        $earnings = $totalMinutes * $earningsPerMinute;
        // Round earnings to the nearest whole number
        $earnings = ceil($earnings);

        RegistrationHourly::create([
            'user_id' => $task->user_id,
            'task_hourly_id' => $taskHourly->id, // Store the task_hourly_id for reference
            'minutes_worked' => $totalMinutes,
            'earnings' => $earnings,
        ]);

        \Log::info('Redirecting to project: ' . $task->project_id);
        \Log::info('Calculated earnings: ' . $earnings);
        return redirect()->route('projects.show', ['project' => $task->project_id]);
    }

    public function storeDistanceRegistration(Request $request, $projectId, $taskId)
    {
        $validatedData = $request->validate([
            'distance_driven' => 'required|numeric',
        ]);

        $task = Task::find($taskId);

        RegistrationDistance::create([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'title' => $task->title,
            'distance' => $validatedData['distance_driven'],
        ]);

        return redirect()->route('projects.show', ['project' => $projectId]);
    }

    public function createRegistration(Project $project, Task $task)
    {
        // Decide which view to return based on the type of the task
        if ($task->task_type == 'project_based') {
            return view('registrations.create_project', compact('project', 'task'));
        } elseif ($task->task_type == 'hourly') {
            return view('registrations.create_hourly', compact('project', 'task'));
        } elseif ($task->task_type == 'distance') {
            return view('registrations.create_distance', compact('project', 'task'));
        }

        // You can pass the project and task to the view if you need them
        return view('registrations.create', compact('project', 'task', 'task->taskable_type', 'task->taskable_id'));
    }
}
