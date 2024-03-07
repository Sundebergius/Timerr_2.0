<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'location' => 'required',
            'type' => 'required',
        ]);

        $task = new Task;
        $task->title = $request->title;
        $task->start_date = $request->start_date;
        $task->end_date = $request->end_date ? $request->end_date : null;
        $task->location = $request->location;
        $task->type = $request->type;
        $task->project_id = $project->id; // Set the project_id to the id of the project

        $task->save();

        // Redirect to the show route for the project
        return redirect()->route('projects.show', $project);
    }

    public function create(Project $project)
    {
        return view('tasks.create', ['project' => $project]);
    }
}
