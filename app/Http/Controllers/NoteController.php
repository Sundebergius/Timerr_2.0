<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Project;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $notes = $project->notes;

        return view('projects.show', ['notes' => $notes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        return view('projects.notes.create', ['project' => $project]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $note = new Note;
        $note->title = $request->title;
        $note->content = $request->content;
        // $note->user_id = auth()->id();
        $project->notes()->save($note);

        return redirect()->route('projects.notes.show', [$project, $note]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $notes = $project->notes;
        $contracts = $project->contracts;
        return view('projects.show', compact('project', 'notes', 'contracts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, Note $note)
    {
        return view('projects.notes.edit', ['note' => $note]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Note $note)
    {
        $note->content = $request->content;
        $note->save();

        return redirect()->route('projects.notes.show', [$project, $note]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Note $note)
    {
        $note->delete();

        return redirect()->route('projects.notes.index', $project);
    }
}