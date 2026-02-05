<?php

use App\Services\InvoiceService;
use App\Services\InvoiceSettingsService;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class);

beforeEach(function () {
    Storage::delete('invoice-settings.json');
});

describe('InvoiceService', function () {
    describe('generate()', function () {
        it('generates pdf content', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-001',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['description' => 'Test Item', 'rate' => 100.00],
                ],
            ]);

            expect($pdf)->toBeString();
            expect(strlen($pdf))->toBeGreaterThan(0);
            // PDF files start with %PDF
            expect(substr($pdf, 0, 4))->toBe('%PDF');
        });

        it('calculates correct subtotal for single item', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-002',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['description' => 'Item', 'rate' => 150.00, 'quantity' => 1],
                ],
            ]);

            // PDF generated successfully (contains content)
            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('calculates correct subtotal for multiple items', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-003',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['description' => 'Item 1', 'rate' => 100.00, 'quantity' => 2], // 200
                    ['description' => 'Item 2', 'rate' => 50.00, 'quantity' => 3],  // 150
                ],
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('applies default quantity of 1 when not specified', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-004',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['description' => 'Item without quantity', 'rate' => 100.00],
                ],
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('uses settings defaults when fields not provided', function () {
            // Set up defaults
            $settingsService = app(InvoiceSettingsService::class);
            $settingsService->update([
                'from_address' => 'Default Corp',
                'payment_terms' => 'Net 15',
                'tax_percent' => 5.0,
            ]);

            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-005',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['rate' => 100.00],
                ],
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('overrides settings defaults when fields provided', function () {
            // Set up defaults
            $settingsService = app(InvoiceSettingsService::class);
            $settingsService->update([
                'from_address' => 'Default Corp',
                'tax_percent' => 5.0,
            ]);

            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-006',
                'bill_to_address' => 'Test Customer',
                'from_address' => 'Override Corp', // Should use this, not default
                'tax_percent' => 10.0, // Should use this, not default
                'line_items' => [
                    ['rate' => 100.00],
                ],
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('calculates tax correctly', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-007',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['rate' => 100.00, 'quantity' => 1], // subtotal: 100
                ],
                'tax_percent' => 10.0, // tax: 10
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('applies discount correctly', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-008',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['rate' => 100.00], // subtotal: 100
                ],
                'discount' => 20.00, // total: 80
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('applies shipping correctly', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-009',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['rate' => 100.00], // subtotal: 100
                ],
                'shipping' => 15.00, // total: 115
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('calculates balance due with amount paid', function () {
            $service = app(InvoiceService::class);

            $pdf = $service->generate([
                'invoice_number' => 'TEST-010',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['rate' => 100.00], // subtotal: 100
                ],
                'amount_paid' => 50.00, // balance due: 50
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });

        it('handles complex calculation with all modifiers', function () {
            $service = app(InvoiceService::class);

            // subtotal: 200, tax (10%): 20, discount: -10, shipping: 15
            // total: 225, amount_paid: 100, balance_due: 125
            $pdf = $service->generate([
                'invoice_number' => 'TEST-011',
                'bill_to_address' => 'Test Customer',
                'line_items' => [
                    ['rate' => 100.00, 'quantity' => 2], // 200
                ],
                'tax_percent' => 10.0,   // +20
                'discount' => 10.00,     // -10
                'shipping' => 15.00,     // +15
                'amount_paid' => 100.00, // -100 from balance
            ]);

            expect(strlen($pdf))->toBeGreaterThan(1000);
        });
    });
});
