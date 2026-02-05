<?php

use App\Mcp\Servers\PikeServer;
use App\Mcp\Tools\GenerateInvoicePdfTool;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::delete('invoice-settings.json');
});

describe('GenerateInvoicePdfTool', function () {
    describe('successful generation', function () {
        it('generates invoice pdf with minimal required fields', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-001',
                'bill_to_address' => "John Doe\n123 Main Street\nNew York, NY 10001",
                'line_items' => [
                    [
                        'description' => 'Web Development Services',
                        'quantity' => 1,
                        'rate' => 1500.00,
                    ],
                ],
            ]);

            $response->assertOk();
        });

        it('generates invoice pdf with all optional fields', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-002',
                'from_address' => "Acme Corp\n456 Business Ave\nLos Angeles, CA 90001",
                'bill_to_address' => "Jane Smith\n789 Customer Lane\nChicago, IL 60601",
                'ship_to_address' => "Jane Smith\n100 Shipping Dock\nChicago, IL 60602",
                'purchase_order' => 'PO-12345',
                'payment_terms' => 'Net 30',
                'notes' => 'Thank you for your business!',
                'terms' => 'Payment due within 30 days. Late payments subject to 1.5% monthly interest.',
                'line_items' => [
                    [
                        'description' => 'Consulting Services',
                        'quantity' => 10,
                        'rate' => 150.00,
                    ],
                    [
                        'description' => 'Software License',
                        'quantity' => 1,
                        'rate' => 499.00,
                    ],
                ],
                'tax_percent' => 8.25,
                'discount' => 100.00,
                'shipping' => 25.00,
                'amount_paid' => 500.00,
            ]);

            $response->assertOk();
        });

        it('generates invoice with multiple line items', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-003',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['description' => 'Item 1', 'rate' => 100.00],
                    ['description' => 'Item 2', 'rate' => 200.00, 'quantity' => 2],
                    ['description' => 'Item 3', 'rate' => 50.00, 'quantity' => 5],
                ],
            ]);

            $response->assertOk();
        });

        it('applies default settings when not overridden', function () {
            // First set up some defaults
            Storage::put('invoice-settings.json', json_encode([
                'from_address' => 'Default Company',
                'payment_terms' => 'Net 15',
                'tax_percent' => 5.0,
            ]));

            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-004',
                'bill_to_address' => 'Customer',
                'line_items' => [
                    ['description' => 'Service', 'rate' => 100.00],
                ],
            ]);

            $response->assertOk();
        });
    });

    describe('validation errors', function () {
        it('requires invoice number', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'bill_to_address' => '123 Test Street',
                'line_items' => [
                    ['rate' => 100.00],
                ],
            ]);

            $response->assertHasErrors();
        });

        it('requires bill to address', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-005',
                'line_items' => [
                    ['rate' => 100.00],
                ],
            ]);

            $response->assertHasErrors();
        });

        it('requires at least one line item', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-006',
                'bill_to_address' => '123 Test Street',
                'line_items' => [],
            ]);

            $response->assertHasErrors();
        });

        it('requires rate for each line item', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-007',
                'bill_to_address' => '123 Test Street',
                'line_items' => [
                    ['description' => 'Some service', 'quantity' => 1],
                ],
            ]);

            $response->assertHasErrors();
        });

        it('validates tax_percent is not over 100', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-008',
                'bill_to_address' => '123 Test Street',
                'line_items' => [
                    ['rate' => 100.00],
                ],
                'tax_percent' => 150,
            ]);

            $response->assertHasErrors();
        });

        it('validates rate is not negative', function () {
            $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
                'invoice_number' => 'INV-009',
                'bill_to_address' => '123 Test Street',
                'line_items' => [
                    ['rate' => -50.00],
                ],
            ]);

            $response->assertHasErrors();
        });
    });
});
