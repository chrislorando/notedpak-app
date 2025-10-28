<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateProjectTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'update-project';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Updates an existing project.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $name = $request->get('name');
        $description = $request->get('description');

        if (empty($id)) {
            return Response::text('Error: Project ID is required');
        }

        $project = Project::find($id);

        if (!$project) {
            return Response::text('Error: Project not found');
        }

        if (isset($name)) {
            $project->name = $name;
        }

        if (isset($description)) {
            $project->description = $description;
        }

        $project->save();

        return Response::text(json_encode([
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'updated_at' => $project->updated_at,
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
            'name' => $schema->string(),
            'description' => $schema->string(),
        ];
    }
}
