<?php

namespace App\Mcp\Tools\Forge;

use App\Services\ForgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ForgeUpdateDeploymentScriptTool extends Tool
{
    protected string $description = 'Updates the deployment script for a Forge site.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer'],
            'script' => ['required', 'string'],
        ]);

        $forgeService->updateDeploymentScript($validated['site_id'], $validated['script']);

        return Response::structured([
            'success' => true,
            'site_id' => $validated['site_id'],
        ]);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_id' => $schema->integer()->description('The Forge site ID.')->required(),
            'script' => $schema->string()->description('The deployment script content.')->required(),
        ];
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('Whether the operation succeeded.')->required(),
            'site_id' => $schema->integer()->description('The site ID.')->required(),
        ];
    }
}
