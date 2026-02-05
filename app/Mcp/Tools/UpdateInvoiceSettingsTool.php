<?php

namespace App\Mcp\Tools;

use App\Services\InvoiceSettingsService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class UpdateInvoiceSettingsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Updates the invoice default settings.
        Only provide the fields you want to update. Omitted fields will keep their current values.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, InvoiceSettingsService $settings): ResponseFactory
    {
        $validated = $request->validate([
            'from_address' => ['nullable', 'string', 'max:500'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        // Filter out null values so we only update provided fields
        $updates = array_filter($validated, fn ($value) => $value !== null);

        $updated = $settings->update($updates);

        return Response::structured([
            'success' => true,
            'settings' => $updated,
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'from_address' => $schema->string()
                ->description('Default sender/company address for invoices.'),

            'payment_terms' => $schema->string()
                ->description('Default payment terms (e.g., "Net 30", "Due on Receipt").'),

            'notes' => $schema->string()
                ->description('Default notes to display on invoices.'),

            'terms' => $schema->string()
                ->description('Default terms and conditions for invoices.'),

            'logo_url' => $schema->string()
                ->description('URL to the company logo, or "storage/path/to/logo.png" for local files.'),

            'tax_percent' => $schema->number()
                ->description('Default tax percentage (e.g., 8.25 for 8.25%).'),
        ];
    }

    /**
     * Get the tool's output schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()
                ->description('Whether the update was successful.')
                ->required(),

            'settings' => $schema->object()
                ->description('The updated settings.')
                ->required(),
        ];
    }
}
