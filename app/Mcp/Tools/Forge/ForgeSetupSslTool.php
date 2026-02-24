<?php

namespace App\Mcp\Tools\Forge;

use App\Services\ForgeService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ForgeSetupSslTool extends Tool
{
    protected string $description = "Obtains a Let's Encrypt SSL certificate for a Forge site.";

    public function handle(Request $request, ForgeService $forgeService): ResponseFactory
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer'],
            'domain' => ['required', 'string', 'max:253'],
        ]);

        $forgeService->obtainLetsEncryptCertificate($validated['site_id'], $validated['domain']);

        return Response::structured([
            'success' => true,
            'site_id' => $validated['site_id'],
            'domain' => $validated['domain'],
        ]);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_id' => $schema->integer()->description('The Forge site ID.')->required(),
            'domain' => $schema->string()->description('The domain to issue the certificate for.')->required(),
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
            'domain' => $schema->string()->description('The domain.')->required(),
        ];
    }
}
