<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Client;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $contracts = $project->contracts; // Make sure the Project model has a contracts() method
        return view('projects.show', compact('project', 'contracts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $clients = Client::all(); // Assuming you have a Client model
        return view('projects.contracts.create', ['project' => $project, 'clients' => $clients]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'client_id' => 'required|exists:clients,id',
            'title' => 'nullable|string|max:255',
            'service_description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'total_amount' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'due_date' => 'nullable|date',
            'payment_terms' => 'nullable',
            'additional_terms' => 'nullable',
        ]);

        $contract = Contract::create($validatedData);

        return redirect()->route('projects.show', ['project' => $contract->project_id])
            ->with('success', 'Contract created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contract = Contract::findOrFail($id);
        $project = $contract->project; // Fetch the project related to the contract
        $client = $contract->client; // Fetch the client related to the contract
        return view('projects.contracts.show', compact('contract', 'project', 'client'));    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contract = Contract::findOrFail($id);
        $project = $contract->project; // Fetch the project related to the contract
        $client = $contract->client; // Fetch the client related to the contract
        return view('projects.contracts.edit', compact('contract', 'project', 'client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Fetch the contract
        $contract = Contract::findOrFail($id);
    
        // Validate the request data
        $validatedData = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'client_id' => 'nullable|exists:clients,id',
            'service_description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'total_amount' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'due_date' => 'nullable|date',
            'payment_terms' => 'nullable|string',
            'status' => 'nullable|string',
            'is_signed' => 'nullable|boolean',
            'additional_terms' => 'nullable|string',
        ]);
    
        // Update the contract with the validated data
        $contract->update($validatedData);
    
        // Redirect the user back to the contract show page with a success message
        return redirect()->route('projects.contracts.show', ['project' => $contract->project->id, 'contract' => $contract->id])
                         ->with('success', 'Contract updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Contract $contract)
    {
        $contract->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Contract deleted successfully!');
    }
}
