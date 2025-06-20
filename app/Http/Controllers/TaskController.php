<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Cache;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $uuid)
    {

        $project = auth()->user()->projects()->where('uuid', $uuid)->firstOrFail();

        Inertia::share([
            'project' => fn () => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'color' => $project->color,
                'tasks' => $project->tasks,
                'user_id' => $project->user_id,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'uuid' => $project->uuid,
            ],
        ]);
    

        return Inertia::render('tasks/Index', [
            'project' => $project,
            'draftTasks' => $project->tasks()->draft()->orderBy('id', 'desc')->get(),
            'completedTasks' => $project->tasks()->completed()->orderBy('description')->get(),
           
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:1000',
            'project_uuid' => 'required|string',
        ]);

        // dd($request->project_uuid);
        $project = auth()->user()->projects()->where('uuid', $request->project_uuid)->firstOrFail();

        $model = new Task();
        $model->description = $request->description;
        $model->project_id = $project->id;
        $model->save();

        return redirect()->route('projects.show', $project->uuid)->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = auth()->user()->projects()->where('uuid', $id)->firstOrFail();

        Inertia::share([
            'project' => fn () => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'color' => $project->color,
                'tasks' => $project->tasks,
                'user_id' => $project->user_id,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'uuid' => $project->uuid,
                'edit_url'=> route('projects.edit', $project->uuid),
                'delete_url'=> route('projects.destroy', $project->uuid),
                // 'importantTasks' => $project->tasks()->importantDraft()->get(),
            ],
        ]);
    

        return Inertia::render('tasks/show', [
            'project' => $project,
            'draftTasks' => $project->tasks()->draft()->get(),
            'completedTasks' => $project->tasks()->completed()->get(),
           
        ]);
   
    }

    /**
     * Display the specified resource.
     */
    public function important()
    {
        $tasks = auth()->user()->tasks()->with('project')->importantDraft()->get();
        return Inertia::render('tasks/important', [
            'tasks' => $tasks,
           
        ]);
   
    }

    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd( $request->all());
        $request->validate([
            'description' => 'required|string',
            // 'uuid' => 'required|string',
        ]);


        $model = auth()->user()->tasks()->where('uuid', $id)->firstOrFail();
        $model->description = $request->description;
        $model->save();

        // return redirect()->route('projects.show', $model->project->uuid)->with('success', 'Task created successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = auth()->user()->tasks()->where('uuid', $id)->first();
        $model->delete();
     
       
    }

    
    public function complete(Request $request, string $id)
    {
        $task = Task::where('uuid', $request->uuid)->firstOrFail();
        $task->is_completed = !$task->is_completed;
        $task->save();

        // return redirect()->route('tasks.show', $id)->with('success', 'Task has been completed.');
    }

    public function bookmark(Request $request, string $id)
    {
        $task = Task::where('uuid', $request->uuid)->firstOrFail();
        $task->is_important = !$task->is_important;
        $task->save();

        // return redirect()->route('tasks.show', $id)->with('success', 'Task has been completed.');
    }
}
