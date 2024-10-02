<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\TaskProduct;

class DashboardController extends Controller
{
    // Main dashboard method
    public function index()
    {
        // Fetch project-related data
        $projects = $this->projectDashboard();

        // Fetch top 3 clients
        $clients = $this->clientDashboard();

        // Other dashboard data can be fetched and added here
        // Example: $notifications = $this->fetchNotifications();

        return view('dashboard', compact('projects', 'clients'));
    }

    public function projectDashboard()
    {
        $user = auth()->user();

        // Fetch the current user's latest 3 projects with their tasks
        $projects = Project::where('user_id', $user->id)
                    ->with('tasks')
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();

        foreach ($projects as $project) {
            // Initialize total value for the project
            $total = 0;

            // Fetch tasks related to the project
            $tasks = $project->tasks;

            // Separate tasks based on type
            $projectTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProject');
            $hourlyTasks = $tasks->where('taskable_type', 'App\\Models\\TaskHourly');
            $distanceTasks = $tasks->where('taskable_type', 'App\\Models\\TaskDistance');
            $productTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProduct');
            $otherTasks = $tasks->where('taskable_type', 'App\\Models\\TaskOther');

            // Load the related taskable entities
            $productTasks->load('taskProduct.product');

            // Extract and calculate total for product-based tasks
            foreach ($productTasks as $task) {
                foreach ($task->taskProduct as $taskProduct) {
                    $total += ($taskProduct->product->price ?? 0) * ($taskProduct->total_sold ?? 1);
                }
            }

            // Load the related taskable entities for other task types
            $projectTasks->load('taskable');
            $hourlyTasks->load('taskable.registrationHourly');
            $distanceTasks->load('taskable.registrationDistances');

            // Calculate totals for project-based tasks
            foreach ($projectTasks as $task) {
                $total += $task->taskable->price ?? 0;
            }

            // Calculate totals for hourly tasks
            foreach ($hourlyTasks as $task) {
                $hours = $task->taskable->registrationHourly->sum('minutes_worked') / 60;
                $total += $hours * $task->taskable->rate_per_hour ?? 0;
            }

            // Calculate totals for distance-based tasks
            foreach ($distanceTasks as $task) {
                $distance = $task->taskable->registrationDistances->sum('distance');
                $total += $distance * $task->taskable->price_per_km ?? 0;
            }

            // Assign the total value to the project for displaying in the dashboard
            $project->total_value = $total;
        }

        return $projects;
    }

    public function clientDashboard()
    {
        $user = auth()->user();

        // Fetch the top 3 clients for the dashboard
        return Client::where('user_id', $user->id)
                    ->withCount('projects') // Assuming clients have related projects
                    ->take(3) // Limit to 3 clients
                    ->get();
    }
}