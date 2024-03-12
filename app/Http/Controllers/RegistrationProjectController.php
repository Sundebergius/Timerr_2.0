<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistrationProject;

class RegistrationProjectController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
            'name' => 'required',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'price' => 'nullable|numeric',
            'project_location' => 'nullable',
            // Add validation rules for other fields
        ]);

        $registrationProject = new RegistrationProject;
        $registrationProject->user_id = auth()->id(); // Assuming the user is authenticated
        $registrationProject->task_id = $request->task_id; // Assuming you're passing the task ID in the request
        $registrationProject->name = $validatedData['name'];
        $registrationProject->description = $validatedData['description'] ?? null;
        $registrationProject->start_date = $validatedData['start_date'] ?? null;
        $registrationProject->end_date = $validatedData['end_date'] ?? null;
        $registrationProject->price = $validatedData['price'] ?? null;
        $registrationProject->project_location = $validatedData['project_location'] ?? null;
        // Set other fields

        $registrationProject->save();

        return redirect()->route('project.index')->with('success', 'Task registered successfully');
    }
}
