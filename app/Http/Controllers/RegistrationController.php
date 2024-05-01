<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\RegistrationProject;
use App\Models\RegistrationHourly;

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
            'seconds_worked' => 'integer',
        ]);

        $task = Task::find($taskId);
        $taskHourly = $task->taskable;

        $totalSeconds = $validatedData['hours_worked'] * 3600 + $validatedData['minutes_worked'] * 60 + ($validatedData['seconds_worked'] ?? 0);
        $totalMinutes = ceil($totalSeconds / 60); // Round up to the nearest minute
        $earningsPerMinute = $taskHourly->rate_per_hour / 60;
        $earnings = $totalMinutes * $earningsPerMinute;

        // Round earnings to the nearest whole number
        $earnings = ceil($earnings);

        RegistrationHourly::create([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'task_hourly_id' => $taskHourly->id, // Store the task_hourly_id for reference
            'title' => $task->title,
            'seconds_worked' => $totalSeconds,
            'hourly_rate' => $taskHourly->rate_per_hour,
            'earnings' => $earnings,
        ]);

        \Log::info('Redirecting to project: ' . $task->project_id);
        \Log::info('Calculated earnings: ' . $earnings);
        return redirect()->route('projects.show', ['project' => $task->project_id]);
    }

    public function createRegistration(Project $project, Task $task)
    {
        // Decide which view to return based on the type of the task
        if ($task->task_type == 'project_based') {
            return view('registrations.create_project', compact('project', 'task'));
        } elseif ($task->task_type == 'hourly') {
            return view('registrations.create_hourly', compact('project', 'task'));
        }

        // You can pass the project and task to the view if you need them
        return view('registrations.create', compact('project', 'task', 'task->taskable_type', 'task->taskable_id'));
    }
}
