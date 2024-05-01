<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use PDF;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            // 'description' => 'required',
            // 'start_date' => 'required|date',
            // 'end_date' => 'required|date',
            // 'status' => 'required|string',
            // 'client_id' => 'exists:clients,id', // Ensure the client ID exists in the clients table
        ]);

        $project = new Project;
        $project->fill([
            'title' => $request->title,
            // 'description' => $request->description,
            // 'start_date' => $request->start_date,
            // 'end_date' => $request->end_date,
            // 'status' => $request->status,
            //'user_id' => auth()->id(), // Get the currently authenticated user's ID
        ]);
        $project->user_id = auth()->id(); // Get the currently authenticated user's ID

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
        $projectTasks = $tasks->where('taskable_type', 'App\\TaskProject');
        $hourlyTasks = $tasks->where('taskable_type', 'App\\TaskHourly');

        // Load the related taskable entities
        $projectTasks->load('taskable');
        $hourlyTasks->load('taskable.registrations');

        $pdf = PDF::loadView('invoices.show', [
            'project' => $project,
            'projectTasks' => $projectTasks,
            'hourlyTasks' => $hourlyTasks,
        ]);

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

        return response()->json(['message' => 'Client updated successfully']);
    }

    public function index()
    {
        $projects = Project::where('user_id', auth()->id())->get();
        $clients = Client::where('user_id', auth()->id())->get();

        return view('projects.index', compact('projects', 'clients'));
    }

    public function show(Project $project)
    {
        $clients = Client::where('user_id', auth()->id())->get(); // Retrieve the clients created by the authenticated user
        
        return view('projects.show', compact('project', 'clients'));
    }

    public function create()
    {
        return view('projects.create');
    }
}
