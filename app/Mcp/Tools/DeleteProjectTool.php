<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class DeleteProjectTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'delete-project';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Deletes a project.
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
            return Response::text('Error: Project ID is required');
        }

        $project = Project::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$project) {
            return Response::text('Error: Project not found or unauthorized');
        }

        $projectName = $project->name;
        $project->delete();

        return Response::text(json_encode([
            'message' => "Project '{$projectName}' deleted successfully",
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
