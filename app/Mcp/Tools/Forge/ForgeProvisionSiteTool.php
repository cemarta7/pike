<?php

namespace App\Mcp\Tools\Forge;

use App\Services\ForgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ForgeProvisionSiteTool extends Tool
{
    protected string $description = 'Provisions a complete site on Forge: creates site, installs git repo, obtains SSL, sets deploy script, configures nginx, and deploys.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:253'],
            'repository' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'project_type' => ['nullable', 'string', 'in:php,html,symfony,symfony_dev,symfony_four'],
            'php_version' => ['nullable', 'string', 'max:10'],
            'isolated' => ['nullable', 'boolean'],
            'deployment_script' => ['nullable', 'string'],
            'nginx_configuration' => ['nullable', 'string'],
        ]);

        $result = $forgeService->provisionSite($validated);

        return Response::structured($result);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'domain' => $schema->string()->description('The domain name for the site.')->required(),
            'repository' => $schema->string()->description('The git repository path (e.g., "user/repo").')->required(),
            'branch' => $schema->string()->description('The branch to deploy. Defaults to "main".'),
            'project_type' => $schema->string()->description('The project type. Defaults to "php".'),
            'php_version' => $schema->string()->description('The PHP version. Defaults to "php83".'),
            'isolated' => $schema->boolean()->description('Whether to use PHP user isolation. Defaults to true.'),
            'deployment_script' => $schema->string()->description('Custom deployment script content.'),
            'nginx_configuration' => $schema->string()->description('Custom nginx configuration content.'),
        ];
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'site_id' => $schema->integer()->description('The created site ID.')->required(),
            'domain' => $schema->string()->description('The site domain.')->required(),
            'steps' => $schema->object()->description('Status of each provisioning step.')->required(),
        ];
    }
}
