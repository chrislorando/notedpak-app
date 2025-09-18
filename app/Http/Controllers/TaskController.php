<?php

namespace App\Http\Controllers;

use App\Enums\Category;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskFiles;
use Cache;
use Illuminate\Http\Request;
use Storage;
use Inertia\Inertia;
use Str;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $uuid)
    {
        $sortBy = $request->query('sort') ?? 'description';
        $sortOrder = $sortBy=='description' ? 'asc' : 'desc';
  
        $project = auth()->user()->projects()->where('uuid', $uuid)->firstOrFail();
        $projects = auth()->user()->projects()
        ->limit(value: 10)
        ->get();

        Inertia::share([
            // 'project' => fn () => [
            //     'id' => $project->id,
            //     'name' => $project->name,
            //     'description' => $project->description,
            //     'color' => $project->color,
            //     'tasks' => $project->tasks,
            //     'user_id' => $project->user_id,
            //     'created_at' => $project->created_at,
            //     'updated_at' => $project->updated_at,
            //     'uuid' => $project->uuid,
            // ],
            'listOptions' => $projects,
            'categoryOptions' => collect(Category::cases())->map(fn($case) => [
                'label' => ucfirst($case->value),
                'value' => $case->value,
                'class' => $case->colorClass(),
            ]),
        ]);
    

        return Inertia::render('tasks/Index', [
            'project' => $project,
            'draftTasks' => $project->tasks()->draft()->with('attachments')->orderBy($sortBy, $sortOrder)->get(),
            'completedTasks' => $project->tasks()->completed()->with('attachments')->orderBy($sortBy, $sortOrder)->get(),
           
        ]);
    }

    public function search(Request $request)
    {
        $sortBy = 'description';
        $sortOrder = 'asc';
  
        $tasks = auth()->user()->tasks()
        ->with(['project','attachments'])
        ->whereLike('description',  '%'.$request->query('q').'%')
        ->orderBy($sortBy, $sortOrder)
        ->limit(value: 20)
        ->get();

        $projects = auth()->user()->projects()
        ->limit(value: 10)
        ->get();

        Inertia::share([
            'listOptions' => $projects,
            'categoryOptions' => collect(Category::cases())->map(fn($case) => [
                'label' => ucfirst($case->value),
                'value' => $case->value,
                'class' => $case->colorClass(),
            ]),
        ]);
    

        return Inertia::render('tasks/Search', [
            'tasks' => $tasks,
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
        $request->validate([
            'description' => 'required|string',
            'note' => 'nullable|string',
            'due_date' => 'nullable',
            'categories' => 'nullable',
        ]);

        // dd($request->all());
        $model = auth()->user()->tasks()->where('uuid', $id)->firstOrFail();
        $model->description = $request->description;
        $model->note = $request->note;
        $model->due_date = $request->due_date;
        $model->categories = $request->categories;
        $model->save();

        // return redirect()->route('projects.show', $model->project->uuid)->with('success', 'Task created successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = auth()->user()->tasks()->where('uuid', $id)->first();
        if ($model) {
        
            foreach ($model->attachments as $file) {
            
                if ($file->url) {
                    // Storage::delete(str_replace('/storage/', '', $file->url));
                    $parsedUrl = parse_url($file->url, PHP_URL_PATH);
    
                    $path = ltrim($parsedUrl, '/');
                    $bucket = config('filesystems.disks.minio.bucket');
                    if (str_starts_with($path, $bucket . '/')) {
                        $path = substr($path, strlen($bucket) + 1);
                    }

                    $disk = config('filesystems.default', 's3');
                    Storage::disk($disk)->delete($path);
                }
                $file->delete();
            }
            $model->delete();
        }  
    }

    
    public function complete(string $id)
    {
        $task = Task::where('uuid', $id)->firstOrFail();
        $task->is_completed = !$task->is_completed;
        $task->save();

        // return redirect()->route('tasks.show', $id)->with('success', 'Task has been completed.');
    }

    public function bookmark(string $id)
    {
        $task = Task::where('uuid', $id)->firstOrFail();
        $task->is_important = !$task->is_important;
        $task->save();

        // return redirect()->route('tasks.show', $id)->with('success', 'Task has been completed.');
    }

    public function searchListOptions(Request $request)
    {
        $projects = auth()->user()->projects()
        ->when($request->search, fn ($q, $search) =>
            $q->where('name', 'like', "%{$search}%")
        )
        ->limit(20)
        ->get();

        return response()->json($projects);
    }

    public function copyTask(Request $request, $id)
    {
        try {
            \DB::beginTransaction();

            $task = Task::with(['attachments'])->where('uuid', $id)->firstOrFail();

            $copy = $task->replicate();

            $copy->project_id = $request->project_id;
            $copy->uuid = Str::uuid();
            $copy->created_at = now();
            $copy->updated_at = now();
            $copy->save();

            foreach ($task->attachments as $f) {
                $attachment = $f->replicate();
                $attachment->task_id = $copy->id;
                $attachment->created_at = now();
                $attachment->updated_at = now();
                $attachment->save();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => 'Failed to copy project: ' . $e->getMessage()], 500);
            // throw $e;
        }
    }

    public function moveTask(Request $request, $id)
    {
        try {
            \DB::beginTransaction();
            $task = Task::with(['attachments'])->where('uuid', $id)->firstOrFail();

            $copy = $task->replicate();

            $copy->project_id = $request->project_id;
            $copy->uuid = Str::uuid();
            $copy->created_at = now();
            $copy->updated_at = now();
            $copy->save();

            foreach ($task->attachments as $f) {
                $attachment = $f->replicate();
                $attachment->task_id = $copy->id;
                $attachment->created_at = now();
                $attachment->updated_at = now();
                $attachment->save();
            }

            $task->delete();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => 'Failed to copy project: ' . $e->getMessage()], 500);
        }
    }

    public function uploadFile(Request $request, $uuid)
    {
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->storePublicly('attachments');

            $file = new TaskFiles();
            $file->task_id = Task::where('uuid', $uuid)->firstOrFail()->id;
            $file->name = $request->file('attachment')->getClientOriginalName();
            $file->size = $request->file('attachment')->getSize();
            $file->url = Storage::url($path);
            $file->save();  
        }
    }

    public function deleteFile(Request $request, $id)
    {
        $model = TaskFiles::where('id', $id)->firstOrFail();
        if ($model->url) {
            $parsedUrl = parse_url($model->url, PHP_URL_PATH);

            $path = ltrim($parsedUrl, '/');
            $bucket = config('filesystems.disks.minio.bucket');
            if (str_starts_with($path, $bucket . '/')) {
                $path = substr($path, strlen($bucket) + 1);
            }

            $disk = config('filesystems.default', 's3');
            Storage::disk($disk)->delete($path);
        }
        $model->delete();
    }
}
