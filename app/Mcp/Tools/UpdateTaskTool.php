<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateTaskTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'update-task';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Updates an existing task.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return Response::text('Error: Unauthenticated');
        }

        $id = $request->get('id');
        $description = $request->get('description');
        $project_id = $request->get('project_id');
        $is_completed = $request->get('is_completed');

        if (empty($id)) {
            return Response::text('Error: Task ID is required');
        }

        $task = Task::where('owner_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$task) {
            return Response::text('Error: Task not found or unauthorized');
        }

        if (isset($description)) {
            $task->description = $description;
        }

        if (isset($project_id)) {
            $task->project_id = $project_id;
        }

        if (isset($is_completed)) {
            $task->is_completed = $is_completed;
        }

        $task->save();

        return Response::text(json_encode([
            'id' => $task->id,
            'description' => $task->description,
            'project_id' => $task->project_id,
            'is_completed' => $task->is_completed,
            'updated_at' => $task->updated_at,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string(),
            'description' => $schema->string(),
            'project_id' => $schema->string(),
            'is_completed' => $schema->boolean(),
        ];
    }
}
