<?php

use App\Mcp\Servers\PikeServer;
use App\Mcp\Tools\GenerateInvoicePdfTool;

test('generates invoice pdf successfully', function () {
    $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
        'invoice_number' => 'TEST-001',
        'bill_to_address' => '123 Test Street, Test City, TS 12345',
        'line_items' => [
            [
                'sku' => 'KEY-001',
                'stock' => 'STK-123',
                'vin' => '1HGCM82633A123456',
                'year_make_model' => '2020 Honda Accord',
                'quantity' => 1,
                'price' => 150.00,
            ],
        ],
    ]);

    $response->assertOk();
});

test('generates invoice pdf with multiple line items', function () {
    $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
        'invoice_number' => 'TEST-002',
        'purchase_order' => 'PO-12345',
        'note_to_customer' => 'Thank you for your business!',
        'bill_to_address' => '456 Customer Ave, Client City, CC 67890',
        'technician_phone' => '555-123-4567',
        'technician_email' => 'tech@example.com',
        'line_items' => [
            [
                'sku' => 'KEY-001',
                'price' => 100.00,
                'quantity' => 2,
            ],
            [
                'sku' => 'KEY-002',
                'year_make_model' => '2019 Toyota Camry',
                'price' => 75.50,
                'quantity' => 1,
            ],
        ],
    ]);

    $response->assertOk();
});

test('requires invoice number', function () {
    $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
        'bill_to_address' => '123 Test Street',
        'line_items' => [
            ['price' => 100.00],
        ],
    ]);

    $response->assertHasErrors();
});

test('requires bill to address', function () {
    $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
        'invoice_number' => 'TEST-003',
        'line_items' => [
            ['price' => 100.00],
        ],
    ]);

    $response->assertHasErrors();
});

test('requires at least one line item', function () {
    $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
        'invoice_number' => 'TEST-004',
        'bill_to_address' => '123 Test Street',
        'line_items' => [],
    ]);

    $response->assertHasErrors();
});

test('requires price for each line item', function () {
    $response = PikeServer::tool(GenerateInvoicePdfTool::class, [
        'invoice_number' => 'TEST-005',
        'bill_to_address' => '123 Test Street',
        'line_items' => [
            ['sku' => 'KEY-001', 'quantity' => 1],
        ],
    ]);

    $response->assertHasErrors();
});
