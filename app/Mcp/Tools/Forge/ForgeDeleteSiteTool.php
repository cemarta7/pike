<?php

namespace App\Mcp\Tools\Forge;

use App\Services\ForgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ForgeDeleteSiteTool extends Tool
{
    protected string $description = 'Deletes a site from the configured Laravel Forge server. This action is irreversible.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer'],
        ]);

        $forgeService->deleteSite($validated['site_id']);

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
            'site_id' => $schema->integer()->description('The Forge site ID to delete.')->required(),
        ];
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('Whether the site was deleted.')->required(),
            'site_id' => $schema->integer()->description('The deleted site ID.')->required(),
        ];
    }
}
