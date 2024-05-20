<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\Product;
use App\Models\RegistrationProject;
use App\Models\RegistrationHourly;
use App\Models\RegistrationDistance;
use App\Models\RegistrationProduct;
use App\Models\TaskProduct;

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
        $earnings = $totalMinutes * $taskHourly->rate_per_minute;

        // Round earnings to the nearest whole number
        $earnings = ceil($earnings);

        RegistrationHourly::create([
            'user_id' => $task->user_id,
            'task_hourly_id' => $taskHourly->id,            
            'minutes_worked' => $totalMinutes,
            'earnings' => $earnings,
        ]);

        return redirect()->route('projects.show', ['project' => $task->project_id]);
    }

    public function storeDistanceRegistration(Request $request, $projectId, $taskId)
    {
        $validatedData = $request->validate([
            'distance_driven' => 'required|numeric',
        ]);

        $task = Task::find($taskId);
        $taskDistance = $task->taskable;

        RegistrationDistance::create([
            'user_id' => $task->user_id,
            'task_distance_id' => $taskDistance->id,
            'distance' => $validatedData['distance_driven'],
        ]);

        return redirect()->route('projects.show', ['project' => $projectId]);
    }

    public function storeProductRegistration(Request $request, $taskId)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        $task = Task::find($taskId);
        $taskProduct = TaskProduct::where('product_id', $validatedData['product_id'])->first();

        if (!$taskProduct) {
            return redirect()->back()->with('error', 'Product not found in task products.');
        }

        RegistrationProduct::create([
            'user_id' => $task->user_id,
            'task_product_id' => $taskProduct->id,
            'quantity' => $validatedData['quantity'],
        ]);

        return redirect()->route('projects.show', ['project' => $task->project_id]);
    }

    public function createRegistration(Project $project, Task $task)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Decide which view to return based on the type of the task
        if ($task->task_type == 'project_based') {
            return view('registrations.create_project', compact('project', 'task'));
        } elseif ($task->task_type == 'hourly') {
            return view('registrations.create_hourly', compact('project', 'task'));
        } elseif ($task->task_type == 'distance') {
            return view('registrations.create_distance', compact('project', 'task'));
        } elseif ($task->task_type == 'product') {
            $products = Product::where('user_id', auth()->id())->get();
            return view('registrations.create_product', compact('project', 'task', 'products'));            
        }

        // You can pass the project and task to the view if you need them
        return view('registrations.create', compact('project', 'task', 'task->taskable_type', 'task->taskable_id'));
    }
}
