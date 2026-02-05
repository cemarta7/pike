<?php

namespace App\Mcp\Tools;

use App\Services\InvoiceService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class GenerateInvoicePdfTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Generates a PDF invoice from the provided invoice data.
        Returns the PDF as a base64-encoded string in a structured response.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, InvoiceService $invoiceService): ResponseFactory
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'purchase_order' => ['nullable', 'string', 'max:100'],
            'note_to_customer' => ['nullable', 'string', 'max:1000'],
            'bill_to_address' => ['required', 'string', 'max:500'],
            'technician_phone' => ['nullable', 'string', 'max:50'],
            'technician_email' => ['nullable', 'string', 'email', 'max:255'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.sku' => ['nullable', 'string', 'max:100'],
            'line_items.*.stock' => ['nullable', 'string', 'max:100'],
            'line_items.*.vin' => ['nullable', 'string', 'max:50'],
            'line_items.*.year_make_model' => ['nullable', 'string', 'max:200'],
            'line_items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'line_items.*.price' => ['required', 'numeric', 'min:0'],
            'logo_base64' => ['nullable', 'string'],
        ], [
            'invoice_number.required' => 'An invoice number is required. Example: "INV-2024-001".',
            'bill_to_address.required' => 'A bill-to address is required for the invoice.',
            'line_items.required' => 'At least one line item is required.',
            'line_items.min' => 'At least one line item is required.',
            'line_items.*.price.required' => 'Each line item must have a price.',
            'line_items.*.price.numeric' => 'Each line item price must be a number.',
        ]);

        $pdfContent = $invoiceService->generate($validated);

        return Response::structured([
            'pdf_base64' => base64_encode($pdfContent),
            'mime_type' => 'application/pdf',
            'invoice_number' => $validated['invoice_number'],
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
            'invoice_number' => $schema->string()
                ->description('The invoice number (e.g., "INV-2024-001").')
                ->required(),

            'purchase_order' => $schema->string()
                ->description('Optional purchase order reference number.'),

            'note_to_customer' => $schema->string()
                ->description('Optional note to display on the invoice.'),

            'bill_to_address' => $schema->string()
                ->description('The billing address for the customer.')
                ->required(),

            'technician_phone' => $schema->string()
                ->description('Technician phone number to display on the invoice.'),

            'technician_email' => $schema->string()
                ->description('Technician email address to display on the invoice.'),

            'line_items' => $schema->array()
                ->description('Array of line items for the invoice.')
                ->items(
                    $schema->object()
                        ->properties([
                            'sku' => $schema->string()->description('Product SKU.'),
                            'stock' => $schema->string()->description('Stock number.'),
                            'vin' => $schema->string()->description('Vehicle Identification Number.'),
                            'year_make_model' => $schema->string()->description('Vehicle year, make, and model.'),
                            'quantity' => $schema->integer()->description('Quantity of items.')->default(1),
                            'price' => $schema->number()->description('Price per item.'),
                        ])
                        ->required(['price'])
                )
                ->required(),

            'logo_base64' => $schema->string()
                ->description('Base64-encoded company logo image.'),
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
            'pdf_base64' => $schema->string()
                ->description('Base64-encoded PDF content.')
                ->required(),

            'mime_type' => $schema->string()
                ->description('MIME type of the response (application/pdf).')
                ->required(),

            'invoice_number' => $schema->string()
                ->description('The invoice number from the request.')
                ->required(),
        ];
    }
}
