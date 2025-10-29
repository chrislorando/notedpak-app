<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListTasksTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'list-tasks';


    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Lists all task items in the user's task list.
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

        $todos = Task::where('owner_id', $user->id)
            ->get()
            ->map(fn ($todo) => [
                'id' => $todo->id,
                'project_name' => $todo->project->name,
                'description' => $todo->description,
                'is_completed' => $todo->is_completed,
                'created_at' => $todo->created_at,
                'updated_at' => $todo->updated_at,
            ])->toArray();

        return Response::text(json_encode($todos, JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
        ];
    }
}
