<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchTaskTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'search-tasks';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Searches for tasks by description.
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

        $query = $request->get('query');

        if (empty($query)) {
            return Response::text('Error: Search query is required');
        }

        $tasks = Task::where('owner_id', $user->id)
            ->where('description', 'like', "%{$query}%")
            ->get()
            ->map(fn ($task) => [
                'id' => $task->id,
                'project_name' => $task->project->name,
                'description' => $task->description,
                'is_completed' => $task->is_completed,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ])
            ->toArray();

        return Response::text(json_encode($tasks, JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string(),
        ];
    }
}
