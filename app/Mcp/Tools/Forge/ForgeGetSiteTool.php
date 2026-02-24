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
class ForgeGetSiteTool extends Tool
{
    protected string $description = 'Gets detailed information about a specific site on the Forge server.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer'],
        ]);

        $site = $forgeService->getSite($validated['site_id']);

        return Response::structured([
            'id' => $site->id,
            'domain' => $site->name,
            'aliases' => $site->aliases,
            'directory' => $site->directory,
            'wildcards' => $site->wildcards,
            'status' => $site->status,
            'repository' => $site->repository,
            'repository_provider' => $site->repositoryProvider,
            'repository_branch' => $site->repositoryBranch,
            'repository_status' => $site->repositoryStatus,
            'quick_deploy' => $site->quickDeploy,
            'deployment_status' => $site->deploymentStatus,
            'project_type' => $site->projectType,
            'php_version' => $site->phpVersion,
            'is_secured' => $site->isSecured,
            'username' => $site->username,
            'deployment_url' => $site->deploymentUrl,
            'created_at' => $site->createdAt,
            'tags' => $site->tags,
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
            'id' => $schema->integer()->description('The site ID.')->required(),
            'domain' => $schema->string()->description('The site domain.')->required(),
            'status' => $schema->string()->description('The site status.')->required(),
            'repository' => $schema->string()->description('The git repository.'),
            'repository_branch' => $schema->string()->description('The deployed branch.'),
            'php_version' => $schema->string()->description('The PHP version.'),
            'is_secured' => $schema->boolean()->description('Whether the site uses HTTPS.'),
            'deployment_status' => $schema->string()->description('The deployment status.'),
            'created_at' => $schema->string()->description('When the site was created.'),
        ];
    }
}
