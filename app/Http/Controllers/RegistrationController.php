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
            'comment' => 'nullable|string',
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
            'comment' => $validatedData['comment'],
        ]);

        // Redirect to the project page after the registration is created which was the initial way of doing it
        // return redirect()->route('projects.show', ['project' => $task->project_id]);

        // Return a JSON response
        return response()->json(['success' => true]);
    }

    public function deleteHourlyRegistration($registrationId)
    {
        $registration = RegistrationHourly::find($registrationId);

        if ($registration) {
            $registration->delete();
            return response()->json(['message' => 'Registration deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Registration not found.'], 404);
    }

    public function storeDistanceRegistration(Request $request, $projectId, $taskId)
    {
        \Log::info('Store Distance Registration Request:', $request->all());

        try {
            // Validate the request data
            $validatedData = $request->validate([
                'distance' => 'required|numeric',
            ]);

            // Find the task
            $task = Task::find($taskId);

            if (!$task) {
                \Log::error('Task not found for ID: ' . $taskId);
                return response()->json(['error' => 'Task not found'], 404);
            }

            // Find or create the task distance
            $taskDistance = $task->taskable;

            if (!$taskDistance) {
                \Log::error('Task distance not found for Task ID: ' . $taskId);
                return response()->json(['error' => 'Task distance not found'], 404);
            }

            // Create the registration distance
            RegistrationDistance::create([
                'user_id' => $task->user_id,
                'task_distance_id' => $taskDistance->id,
                'distance' => $validatedData['distance'],
            ]);

            // Log success
            \Log::info('Registration distance created successfully.');

            // Return JSON response
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error storing distance registration: ' . $e->getMessage());
            
            // Return error response
            return response()->json(['error' => 'An error occurred while storing distance registration.'], 500);
        }
    }

    public function deleteDistanceRegistration($registrationId)
    {
        $registration = RegistrationDistance::find($registrationId);

        if ($registration) {
            $registration->delete();
            return response()->json(['message' => 'Registration deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Registration not found'], 404);
        }
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
