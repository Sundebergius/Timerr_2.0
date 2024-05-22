<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Project;

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
        return view('projects.contracts.create', ['project' => $project]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'client_id' => 'required|exists:clients,id',
            'service_description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'total_amount' => 'required|numeric',
            'currency' => 'required',
            'due_date' => 'required|date',
            'payment_terms' => 'required',
            'additional_terms' => 'nullable',
        ]);

        $contract = new Contract([
            'project_id' => $request->get('project_id'),
            'client_id' => $request->get('client_id'),
            'service_description' => $request->get('service_description'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'total_amount' => $request->get('total_amount'),
            'currency' => $request->get('currency'),
            'due_date' => $request->get('due_date'),
            'payment_terms' => $request->get('payment_terms'),
            'additional_terms' => $request->get('additional_terms'),
        ]);

        $contract->save();

        return redirect()->route('projects.show')->with('success', 'Contract created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contract = Contract::findOrFail($id);
        return view('projects.contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contract = Contract::findOrFail($id);
        return view('projects.contracts.edit', compact('contract'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
