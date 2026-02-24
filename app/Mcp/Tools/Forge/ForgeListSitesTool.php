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
class ForgeListSitesTool extends Tool
{
    protected string $description = 'Lists all sites on the configured Laravel Forge server.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $sites = $forgeService->listSites();

        return Response::structured([
            'sites' => array_map(fn ($site) => [
                'id' => $site->id,
                'domain' => $site->name,
                'status' => $site->status,
                'repository' => $site->repository,
                'branch' => $site->repositoryBranch,
                'php_version' => $site->phpVersion,
                'project_type' => $site->projectType,
            ], $sites),
        ]);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'sites' => $schema->array()
                ->description('List of sites on the server.')
                ->items(
                    $schema->object()
                        ->properties([
                            'id' => $schema->integer()->description('The site ID.'),
                            'domain' => $schema->string()->description('The site domain.'),
                            'status' => $schema->string()->description('The site status.'),
                            'repository' => $schema->string()->description('The git repository.'),
                            'branch' => $schema->string()->description('The deployed branch.'),
                            'php_version' => $schema->string()->description('The PHP version.'),
                            'project_type' => $schema->string()->description('The project type.'),
                        ])
                )
                ->required(),
        ];
    }
}
