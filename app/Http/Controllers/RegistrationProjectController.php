<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistrationProject;

class RegistrationProjectController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
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
        $registrationProject->description = $validatedData['description'];
        $registrationProject->start_date = $validatedData['start_date'];
        $registrationProject->end_date = $validatedData['end_date'];
        $registrationProject->price = $validatedData['price'];
        $registrationProject->project_location = $validatedData['project_location'];
        // Set other fields

        $registrationProject->save();

        return redirect()->route('project.index')->with('success', 'Task registered successfully');
    }
}
