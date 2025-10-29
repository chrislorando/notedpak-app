<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchProjectTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'search-projects';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Searches for projects by name or description.
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

        $projects = Project::where('user_id', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
            ])
            ->toArray();

        return Response::text(json_encode($projects, JSON_PRETTY_PRINT));
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
