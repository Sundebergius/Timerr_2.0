<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $project = new Project;
        $project->title = $request->title;
        $project->user_id = auth()->id(); // Get the currently authenticated user's ID

        // If a client ID was provided, associate the project with the client
        if ($request->has('client_id')) {
            $project->client_id = $request->client_id;
        }

        $project->save();

        return redirect()->route('projects.show', $project);
    }

    public function index()
    {
        $projects = Project::where('user_id', auth()->id())->get();
        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    public function create()
    {
        return view('projects.create');
    }
}
