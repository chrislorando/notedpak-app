<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateProjectTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'create-project';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Creates a new project.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $name = $request->get('name');
        $description = $request->get('description');

        if (empty($name)) {
            return Response::text('Error: Project name is required');
        }

        $project = Project::create([
            'name' => $name,
            'description' => $description,
        ]);

        return Response::text(json_encode([
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'created_at' => $project->created_at,
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
            'name' => $schema->string(),
            'description' => $schema->string(),
        ];
    }
}
