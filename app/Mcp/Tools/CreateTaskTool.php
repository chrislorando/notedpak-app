<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateTaskTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'create-task';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Creates a new task.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $description = $request->get('description');
        $project_id = $request->get('project_id');
        $is_completed = $request->get('is_completed', false);

        if (empty($description) || empty($project_id)) {
            return Response::text('Error: Task description and project ID are required');
        }

        $task = Task::create([
            'description' => $description,
            'project_id' => $project_id,
            'is_completed' => $is_completed,
        ]);

        return Response::text(json_encode([
            'id' => $task->id,
            'description' => $task->description,
            'project_id' => $task->project_id,
            'is_completed' => $task->is_completed,
            'created_at' => $task->created_at,
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
            'description' => $schema->string(),
            'project_id' => $schema->string(),
            'is_completed' => $schema->boolean(),
        ];
    }
}
