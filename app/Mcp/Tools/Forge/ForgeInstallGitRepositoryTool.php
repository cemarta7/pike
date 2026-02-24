<?php

namespace App\Mcp\Tools\Forge;

use App\Services\ForgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ForgeInstallGitRepositoryTool extends Tool
{
    protected string $description = 'Installs a git repository on a Forge site.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer'],
            'repository' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'in:github,gitlab,bitbucket,custom'],
        ]);

        $forgeService->installGitRepository(
            $validated['site_id'],
            $validated['repository'],
            $validated['branch'] ?? 'main',
            $validated['provider'] ?? 'gitlab',
        );

        return Response::structured([
            'success' => true,
            'site_id' => $validated['site_id'],
            'repository' => $validated['repository'],
            'branch' => $validated['branch'] ?? 'main',
        ]);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_id' => $schema->integer()->description('The Forge site ID.')->required(),
            'repository' => $schema->string()->description('The repository path (e.g., "user/repo").')->required(),
            'branch' => $schema->string()->description('The branch to deploy. Defaults to "main".'),
            'provider' => $schema->string()->description('The git provider (github, gitlab, bitbucket, custom). Defaults to "gitlab".'),
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
            'repository' => $schema->string()->description('The installed repository.')->required(),
            'branch' => $schema->string()->description('The deployed branch.')->required(),
        ];
    }
}
