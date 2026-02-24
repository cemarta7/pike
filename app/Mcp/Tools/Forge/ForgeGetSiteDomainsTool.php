<?php

namespace App\Mcp\Tools\Forge;

use App\Services\ForgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class ForgeGetSiteDomainsTool extends Tool
{
    protected string $description = 'Gets all domains (primary + aliases) for a Forge site.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer'],
        ]);

        $domains = $forgeService->getSiteDomains($validated['site_id']);

        return Response::structured([
            'site_id' => $validated['site_id'],
            'domains' => $domains,
        ]);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_id' => $schema->integer()->description('The Forge site ID.')->required(),
        ];
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'site_id' => $schema->integer()->description('The site ID.')->required(),
            'domains' => $schema->array()
                ->description('All domains for the site (primary domain first, then aliases).')
                ->items($schema->string())
                ->required(),
        ];
    }
}
