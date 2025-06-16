<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Cache;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // return Inertia::render('projects/index', [
        //     'projects' => auth()->user()->projects()->with('tasks')->get(),
        // ]);

        $search = $request->query('q');

        $projects = filled($search)
        ? auth()->user()->projects()
            ->select('uuid', 'name', 'color', 'description')
            // ->with(['tasks' => function ($query) {
            //     $query->select('id', 'name', 'description', 'project_id', 'user_id')->draft();
            // }])
            ->withCount('draftTasks')
            ->whereLike('name', '%'.$search.'%')
            ->get()
        : auth()->user()->projects()
            ->select('uuid', 'name', 'color', 'description')
            // ->with(['tasks' => function ($query) {
            //     $query->select('id', 'name', 'description', 'project_id', 'user_id')->draft();
            // }])
            ->withCount('draftTasks')
        ->get();

        $tasks =  auth()->user()?->tasks()->importantDraft()->count();


        return response()->json(['projects'=>$projects, 'countImportantTasks'=>$tasks]);
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
            'name' => 'required|string|max:255|unique:projects,name,NULL,id,user_id,'.auth()->user()->id,
            'description' => 'required|string|max:1000',
            'color' => 'nullable|string|max:1000',
        ]);

        $model = auth()->user()->projects()->create($request->only('name', 'description', 'color', 'user_id'));
        // $model->name = $request->name;
        // $model->description = $request->description;
        // $model->color = $request->color;
        // $model->save();

        return redirect()->route('tasks.show', $model->uuid)->with('success', 'Project created successfully.');
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
            ],
        ]);
    

        // return Inertia::render('projects/show', [
        //     'project' => $project,
        //     'draftTasks' => $project->tasks()->draft()->orderBy('description')->get(),
        //     'completedTasks' => $project->tasks()->completed()->orderBy('description')->get(),
           
        // ]);
   
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
        $request->validate([
            'name' => 'required|min:2|unique:projects,name,'.$id.',id,user_id,'.auth()->user()->id,
            'description' => 'required|string|max:1000',
            'color' => 'nullable|string|max:1000',
        ]);

       
        $model = auth()->user()->projects()->where('id', $id)->firstOrFail();
        $model->name = $request->name;
        $model->description = $request->description;
        $model->color = $request->color;
        $model->save();

        return redirect()->route('projects.show', $model->uuid)->with('success', 'Project created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = auth()->user()->projects()->where('uuid', $id)->first();

        $deletedCreatedAt = $model?->created_at;

        if($model->delete()){
            $latest = auth()->user()->projects()
                ->where('created_at', '<=', $deletedCreatedAt)
                ->latest()
                ->first();

            return redirect()->route('tasks.show', $latest?->uuid)->with('success', 'Project has been removed.');

        }

        // return redirect()->route('dashboard')->with('error', 'Project not found.');
       
    }

    
    public function taskChange(Request $request, string $id)
    {
        $task = Task::where('uuid', $request->uuid)->firstOrFail();
        $task->is_completed = $request->is_completed;
        $task->save();

        return redirect()->route('projects.show', $id)->with('success', 'Task has been completed.');
    }

    public function taskBookmark(Request $request, string $id)
    {
        $task = Task::where('uuid', $request->uuid)->firstOrFail();
        $task->is_important = $request->is_important;
        $task->save();

        return redirect()->route('projects.show', $id)->with('success', 'Task has been completed.');
    }
}
