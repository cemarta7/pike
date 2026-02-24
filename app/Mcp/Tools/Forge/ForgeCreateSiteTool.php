<?php

namespace App\Mcp\Tools\Forge;

use App\Services\ForgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ForgeCreateSiteTool extends Tool
{
    protected string $description = 'Creates a new site on the configured Laravel Forge server.';

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:253'],
            'project_type' => ['nullable', 'string', 'in:php,html,symfony,symfony_dev,symfony_four'],
            'php_version' => ['nullable', 'string', 'max:10'],
            'isolated' => ['nullable', 'boolean'],
        ]);

        $site = $forgeService->createSite(
            $validated['domain'],
            $validated['project_type'] ?? 'php',
            $validated['php_version'] ?? 'php83',
            $validated['isolated'] ?? true,
        );

        return Response::structured([
            'site_id' => $site->id,
            'domain' => $site->name,
            'status' => $site->status,
            'php_version' => $site->phpVersion,
        ]);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'domain' => $schema->string()
                ->description('The domain name for the site (e.g., "example.com").')
                ->required(),
            'project_type' => $schema->string()
                ->description('The project type (php, html, symfony, symfony_dev, symfony_four). Defaults to "php".'),
            'php_version' => $schema->string()
                ->description('The PHP version (e.g., "php83"). Defaults to "php83".'),
            'isolated' => $schema->boolean()
                ->description('Whether to use PHP user isolation. Defaults to true.'),
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
            'status' => $schema->string()->description('The site status.')->required(),
            'php_version' => $schema->string()->description('The PHP version.')->required(),
        ];
    }
}
