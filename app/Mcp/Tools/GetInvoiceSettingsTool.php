<?php

namespace App\Mcp\Tools;

use App\Services\InvoiceSettingsService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class GetInvoiceSettingsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Retrieves the current invoice default settings.
        Returns settings like from_address, payment_terms, notes, terms, logo_url, and tax_percent.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, InvoiceSettingsService $settings): ResponseFactory
    {
        return Response::structured($settings->all());
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    /**
     * Get the tool's output schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'from_address' => $schema->string()
                ->description('Default sender/company address.')
                ->required(),

            'payment_terms' => $schema->string()
                ->description('Default payment terms.')
                ->required(),

            'notes' => $schema->string()
                ->description('Default notes for invoices.')
                ->required(),

            'terms' => $schema->string()
                ->description('Default terms and conditions.')
                ->required(),

            'logo_url' => $schema->string()
                ->description('URL or storage path to the company logo.')
                ->required(),

            'tax_percent' => $schema->number()
                ->description('Default tax percentage.')
                ->required(),
        ];
    }
}
