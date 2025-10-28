<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class DeleteTaskTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'delete-task';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Deletes a task.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->get('id');

        if (empty($id)) {
            return Response::text('Error: Task ID is required');
        }

        $task = Task::find($id);

        if (!$task) {
            return Response::text('Error: Task not found');
        }

        $taskDescription = $task->description;
        $task->delete();

        return Response::text(json_encode([
            'message' => "Task '{$taskDescription}' deleted successfully",
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
        ];
    }
}
