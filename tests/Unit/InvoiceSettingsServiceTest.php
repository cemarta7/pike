<?php

use App\Services\InvoiceSettingsService;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class);

beforeEach(function () {
    Storage::delete('invoice-settings.json');
});

describe('InvoiceSettingsService', function () {
    describe('all()', function () {
        it('returns default settings when no file exists', function () {
            $service = new InvoiceSettingsService;

            $settings = $service->all();

            expect($settings)->toHaveKeys([
                'from_address',
                'payment_terms',
                'notes',
                'terms',
                'logo_url',
                'tax_percent',
            ]);
            expect($settings['payment_terms'])->toBe('Net 30');
            expect($settings['tax_percent'])->toBe(0);
        });

        it('creates settings file with defaults when none exists', function () {
            $service = new InvoiceSettingsService;

            $service->all();

            expect(Storage::exists('invoice-settings.json'))->toBeTrue();
        });

        it('returns saved settings when file exists', function () {
            Storage::put('invoice-settings.json', json_encode([
                'from_address' => 'Custom Company',
                'payment_terms' => 'Net 60',
            ]));

            $service = new InvoiceSettingsService;
            $settings = $service->all();

            expect($settings['from_address'])->toBe('Custom Company');
            expect($settings['payment_terms'])->toBe('Net 60');
        });

        it('merges saved settings with defaults', function () {
            Storage::put('invoice-settings.json', json_encode([
                'from_address' => 'Partial Company',
            ]));

            $service = new InvoiceSettingsService;
            $settings = $service->all();

            expect($settings['from_address'])->toBe('Partial Company');
            expect($settings['payment_terms'])->toBe('Net 30'); // default
        });
    });

    describe('get()', function () {
        it('returns specific setting value', function () {
            $service = new InvoiceSettingsService;

            expect($service->get('payment_terms'))->toBe('Net 30');
        });

        it('returns default value when setting not found', function () {
            $service = new InvoiceSettingsService;

            expect($service->get('nonexistent', 'fallback'))->toBe('fallback');
        });
    });

    describe('update()', function () {
        it('updates single setting', function () {
            $service = new InvoiceSettingsService;

            $service->update(['from_address' => 'Updated Company']);

            expect($service->get('from_address'))->toBe('Updated Company');
        });

        it('updates multiple settings', function () {
            $service = new InvoiceSettingsService;

            $service->update([
                'from_address' => 'Multi Company',
                'payment_terms' => 'Due on Receipt',
                'tax_percent' => 8.5,
            ]);

            expect($service->get('from_address'))->toBe('Multi Company');
            expect($service->get('payment_terms'))->toBe('Due on Receipt');
            expect($service->get('tax_percent'))->toBe(8.5);
        });

        it('preserves existing settings when updating', function () {
            $service = new InvoiceSettingsService;

            $service->update(['from_address' => 'First Update']);
            $service->update(['notes' => 'Second Update']);

            expect($service->get('from_address'))->toBe('First Update');
            expect($service->get('notes'))->toBe('Second Update');
        });

        it('returns updated settings', function () {
            $service = new InvoiceSettingsService;

            $result = $service->update(['from_address' => 'Return Test']);

            expect($result['from_address'])->toBe('Return Test');
        });
    });

    describe('reset()', function () {
        it('resets settings to defaults', function () {
            $service = new InvoiceSettingsService;

            $service->update([
                'from_address' => 'Custom Company',
                'payment_terms' => 'Net 90',
                'tax_percent' => 15.0,
            ]);

            $service->reset();

            expect($service->get('from_address'))->toBe('');
            expect($service->get('payment_terms'))->toBe('Net 30');
            expect($service->get('tax_percent'))->toBe(0);
        });

        it('returns default settings after reset', function () {
            $service = new InvoiceSettingsService;

            $service->update(['from_address' => 'To Be Reset']);

            $result = $service->reset();

            expect($result['from_address'])->toBe('');
        });
    });

    describe('getLogoBase64()', function () {
        it('returns null when no logo_url configured', function () {
            $service = new InvoiceSettingsService;

            expect($service->getLogoBase64())->toBeNull();
        });

        it('returns null for empty logo_url', function () {
            $service = new InvoiceSettingsService;
            $service->update(['logo_url' => '']);

            expect($service->getLogoBase64())->toBeNull();
        });

        it('extracts base64 from data URL', function () {
            $service = new InvoiceSettingsService;
            $base64Content = base64_encode('test image data');
            $service->update(['logo_url' => "data:image/png;base64,{$base64Content}"]);

            expect($service->getLogoBase64())->toBe($base64Content);
        });

        it('reads logo from storage path', function () {
            Storage::put('logos/test-logo.png', 'fake image content');

            $service = new InvoiceSettingsService;
            $service->update(['logo_url' => 'storage/logos/test-logo.png']);

            $result = $service->getLogoBase64();

            expect($result)->toBe(base64_encode('fake image content'));

            Storage::delete('logos/test-logo.png');
        });

        it('returns null for non-existent storage file', function () {
            $service = new InvoiceSettingsService;
            $service->update(['logo_url' => 'storage/logos/nonexistent.png']);

            expect($service->getLogoBase64())->toBeNull();
        });
    });
});
