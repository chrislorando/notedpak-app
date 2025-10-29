<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CompleteTaskTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'complete-task';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Marks a task as completed.
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

        if (empty($id)) {
            return Response::text('Error: Task ID is required');
        }

        $task = Task::where('owner_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$task) {
            return Response::text('Error: Task not found or unauthorized');
        }

        $task->is_completed = true;
        $task->save();

        return Response::text(json_encode([
            'id' => $task->id,
            'description' => $task->description,
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
        ];
    }
}
