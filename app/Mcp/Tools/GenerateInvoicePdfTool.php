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
            'from_address' => ['nullable', 'string', 'max:500'],
            'bill_to_address' => ['required', 'string', 'max:500'],
            'ship_to_address' => ['nullable', 'string', 'max:500'],
            'purchase_order' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.description' => ['nullable', 'string', 'max:500'],
            'line_items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'line_items.*.rate' => ['required', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'logo_base64' => ['nullable', 'string'],
        ], [
            'invoice_number.required' => 'An invoice number is required. Example: "INV-001".',
            'bill_to_address.required' => 'A bill-to address is required for the invoice.',
            'line_items.required' => 'At least one line item is required.',
            'line_items.min' => 'At least one line item is required.',
            'line_items.*.rate.required' => 'Each line item must have a rate.',
            'line_items.*.rate.numeric' => 'Each line item rate must be a number.',
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
                ->description('The invoice number (e.g., "INV-001").')
                ->required(),

            'from_address' => $schema->string()
                ->description('The sender/company address (who is this from?).'),

            'bill_to_address' => $schema->string()
                ->description('The billing address for the customer.')
                ->required(),

            'ship_to_address' => $schema->string()
                ->description('Optional shipping address if different from billing.'),

            'purchase_order' => $schema->string()
                ->description('Optional PO number reference.'),

            'payment_terms' => $schema->string()
                ->description('Payment terms (e.g., "Net 30", "Due on Receipt").'),

            'notes' => $schema->string()
                ->description('Additional notes to display on the invoice.'),

            'terms' => $schema->string()
                ->description('Terms and conditions for the invoice.'),

            'line_items' => $schema->array()
                ->description('Array of line items for the invoice.')
                ->items(
                    $schema->object()
                        ->properties([
                            'description' => $schema->string()->description('Description of item/service.'),
                            'quantity' => $schema->integer()->description('Quantity of items.')->default(1),
                            'rate' => $schema->number()->description('Rate/price per item.'),
                        ])
                        ->required(['rate'])
                )
                ->required(),

            'tax_percent' => $schema->number()
                ->description('Tax percentage to apply (e.g., 8.25 for 8.25%).'),

            'discount' => $schema->number()
                ->description('Discount amount to subtract from total.'),

            'shipping' => $schema->number()
                ->description('Shipping cost to add to total.'),

            'amount_paid' => $schema->number()
                ->description('Amount already paid (subtracted from balance due).'),

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
