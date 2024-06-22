<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use PDF;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = new Project;
        $project->fill([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'user_id' => auth()->id(),
        ]);

        // If a client ID was provided, associate the project with the client
        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
            $project->client()->associate($client);
        }

        $project->save();

        return redirect()->route('projects.show', $project);
    }

    public function toggleCompletion(Project $project)
    {
        if ($project->status == 'completed') {
            $project->status = 'ongoing';
        } else {
            $project->status = 'completed';
        }
        
        $project->save();

        return redirect()->route('projects.index', $project);
    }

    public function invoice(Project $project)
    {
        if ($project->status != 'completed') {
            abort(404); // Or redirect to a different page with an error message
        }

        // Fetch tasks related to the project
        $tasks = $project->tasks;

        // Separate project-based and hourly tasks
        $projectTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProject');
        $hourlyTasks = $tasks->where('taskable_type', 'App\\Models\\TaskHourly');
        $distanceTasks = $tasks->where('taskable_type', 'App\\Models\\TaskDistance');
        $productTasks = $tasks->where('taskable_type', 'App\\Models\\TaskProduct');
        $otherTasks = $tasks->where('taskable_type', 'App\\Models\\TaskOther');

        // Load the related taskable entities
        $productTasks->load('taskProduct.product');

        // Extract products
        $products = collect();
        foreach ($productTasks as $task) {
            foreach ($task->taskProduct as $taskProduct) {
                // Assuming 'product' is the correct relationship name within 'TaskProduct' model
                $products->push($taskProduct->product);
            }
        }

        // Load the related taskable entities
        $projectTasks->load('taskable');
        $hourlyTasks->load('taskable.registrationHourly');
        $distanceTasks->load('taskable.registrationDistances');
        $productTasks->load('taskable');
        $otherTasks->load('taskable');

        // Calculate totals
        $total = 0;
        foreach ($projectTasks as $task) {
            $total += $task->taskable->price;
        }
        foreach ($hourlyTasks as $task) {
            $hours = $task->taskable->registrationHourly->sum('minutes_worked') / 60;
            $total += $hours * $task->taskable->rate_per_hour;
        }
        foreach ($products as $product) {
            $total += $product->price * $product->quantitySold;
        }
        foreach ($distanceTasks as $task) {
            $distance = $task->taskable->registrationDistances->sum('distance');
            $total += $distance * $task->taskable->price_per_km;
        }

        $vat = $total * 0.25;
        $totalWithVat = $total * 1.25;

        // Generate PDF
        $pdf = PDF::loadView('invoices.show', [
            'project' => $project,
            'projectTasks' => $projectTasks,
            'hourlyTasks' => $hourlyTasks,
            'distanceTasks' => $distanceTasks,
            'productTasks' => $productTasks,
            'products' => $products,
            'otherTasks' => $otherTasks,
            'total' => $total,
            'vat' => $vat,
            'totalWithVat' => $totalWithVat,
        ]);

        // Save PDF to storage
        $fileName = 'invoices/invoice_' . $project->id . '_' . time() . '.pdf';
        Storage::put($fileName, $pdf->output());

        // Save invoice details to database
        $invoice = new Invoice();
        $invoice->user_id = auth()->id();
        $invoice->project_id = $project->id;
        $invoice->client_id = $project->client->id;
        $invoice->title = 'Invoice for Project ' . $project->id;
        $invoice->total = $totalWithVat;
        $invoice->vat = $vat;
        $invoice->file_path = $fileName;
        $invoice->save();

        return $pdf->stream($project->id . '_invoice.pdf');
    }

    public function updateInvoiceStatus(Request $request, Project $project)
    {
        $project->update(['invoice_status' => $request->status]);

        return response()->json(['message' => 'Invoice status updated successfully']);
    }

    public function updateClient(Request $request, Project $project)
    {
        $project->update(['client_id' => $request->client_id]);

        // Update the client_id of all tasks belonging to the project
        foreach ($project->tasks as $task) {
            $task->update(['client_id' => $request->client_id]);
        }

        return response()->json(['message' => 'Client and tasks updated successfully']);
    }

    public function index()
    {
        $projects = Project::where('user_id', auth()->id())->get();
        $clients = Client::where('user_id', auth()->id())->get();

        foreach ($projects as $project) {
            $project->updateStatus();
        }

        return view('projects.index', compact('projects', 'clients'));
    }

    public function show(Project $project)
    {
        // Abort if the authenticated user is not the owner of the project
        if ($project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
    
        $clients = Client::where('user_id', auth()->id())->get(); // Retrieve the clients created by the authenticated user
    
        return view('projects.show', compact('project', 'clients'));
    }

    public function create()
    {
        $clients = Client::where('user_id', auth()->id())->get();
        return view('projects.create', compact('clients'));
    }
}
