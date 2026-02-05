<?php

use App\Mcp\Servers\PikeServer;
use App\Mcp\Tools\GetInvoiceSettingsTool;
use App\Mcp\Tools\UpdateInvoiceSettingsTool;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::delete('invoice-settings.json');
});

describe('GetInvoiceSettingsTool', function () {
    it('returns default settings when no settings file exists', function () {
        $response = PikeServer::tool(GetInvoiceSettingsTool::class, []);

        $response->assertOk();
    });

    it('returns saved settings when settings file exists', function () {
        Storage::put('invoice-settings.json', json_encode([
            'from_address' => 'Test Company',
            'payment_terms' => 'Net 60',
            'notes' => 'Test notes',
            'terms' => 'Test terms',
            'logo_url' => 'https://example.com/logo.png',
            'tax_percent' => 10.0,
        ]));

        $response = PikeServer::tool(GetInvoiceSettingsTool::class, []);

        $response->assertOk();
    });
});

describe('UpdateInvoiceSettingsTool', function () {
    describe('successful updates', function () {
        it('updates from_address', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'from_address' => "Acme Corp\n123 Business St\nNew York, NY 10001",
            ]);

            $response->assertOk();

            // Verify persisted
            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['from_address'])->toContain('Acme Corp');
        });

        it('updates payment_terms', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'payment_terms' => 'Due on Receipt',
            ]);

            $response->assertOk();

            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['payment_terms'])->toBe('Due on Receipt');
        });

        it('updates notes', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'notes' => 'Thank you for your business!',
            ]);

            $response->assertOk();

            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['notes'])->toBe('Thank you for your business!');
        });

        it('updates terms', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'terms' => 'All sales final. No refunds.',
            ]);

            $response->assertOk();

            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['terms'])->toBe('All sales final. No refunds.');
        });

        it('updates logo_url', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'logo_url' => 'https://example.com/logo.png',
            ]);

            $response->assertOk();

            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['logo_url'])->toBe('https://example.com/logo.png');
        });

        it('updates tax_percent', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'tax_percent' => 8.25,
            ]);

            $response->assertOk();

            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['tax_percent'])->toBe(8.25);
        });

        it('updates multiple fields at once', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'from_address' => 'Multi Update Corp',
                'payment_terms' => 'Net 45',
                'tax_percent' => 7.5,
            ]);

            $response->assertOk();

            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['from_address'])->toBe('Multi Update Corp');
            expect($settings['payment_terms'])->toBe('Net 45');
            expect($settings['tax_percent'])->toBe(7.5);
        });

        it('preserves existing settings when updating single field', function () {
            // Set initial settings
            PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'from_address' => 'Original Company',
                'payment_terms' => 'Net 30',
            ]);

            // Update only one field
            PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'notes' => 'New notes',
            ]);

            $settings = json_decode(Storage::get('invoice-settings.json'), true);
            expect($settings['from_address'])->toBe('Original Company');
            expect($settings['payment_terms'])->toBe('Net 30');
            expect($settings['notes'])->toBe('New notes');
        });
    });

    describe('validation errors', function () {
        it('validates tax_percent max is 100', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'tax_percent' => 150,
            ]);

            $response->assertHasErrors();
        });

        it('validates tax_percent min is 0', function () {
            $response = PikeServer::tool(UpdateInvoiceSettingsTool::class, [
                'tax_percent' => -5,
            ]);

            $response->assertHasErrors();
        });
    });
});
