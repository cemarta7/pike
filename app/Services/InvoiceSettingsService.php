<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class InvoiceSettingsService
{
    private const SETTINGS_PATH = 'invoice-settings.json';

    /**
     * Default settings structure.
     *
     * @var array<string, mixed>
     */
    private array $defaults = [
        'from_address' => '',
        'payment_terms' => 'Net 30',
        'notes' => '',
        'terms' => '',
        'logo_url' => '',
        'tax_percent' => 0,
    ];

    /**
     * Get all invoice settings.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (! Storage::exists(self::SETTINGS_PATH)) {
            $this->save($this->defaults);

            return $this->defaults;
        }

        $content = Storage::get(self::SETTINGS_PATH);
        $settings = json_decode($content, true) ?? [];

        return array_merge($this->defaults, $settings);
    }

    /**
     * Get a specific setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return $settings[$key] ?? $default;
    }

    /**
     * Update settings with the provided values.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function update(array $values): array
    {
        $settings = $this->all();
        $settings = array_merge($settings, $values);
        $this->save($settings);

        return $settings;
    }

    /**
     * Save settings to the JSON file.
     *
     * @param  array<string, mixed>  $settings
     */
    private function save(array $settings): void
    {
        Storage::put(
            self::SETTINGS_PATH,
            json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Reset settings to defaults.
     *
     * @return array<string, mixed>
     */
    public function reset(): array
    {
        $this->save($this->defaults);

        return $this->defaults;
    }

    /**
     * Get the logo as base64, fetching from URL if configured.
     */
    public function getLogoBase64(): ?string
    {
        $logoUrl = $this->get('logo_url');

        if (empty($logoUrl)) {
            return null;
        }

        // If it's already base64, return as-is
        if (str_starts_with($logoUrl, 'data:')) {
            return preg_replace('/^data:image\/\w+;base64,/', '', $logoUrl);
        }

        // If it's a local path in storage
        if (str_starts_with($logoUrl, 'storage/')) {
            $path = str_replace('storage/', '', $logoUrl);
            if (Storage::exists($path)) {
                return base64_encode(Storage::get($path));
            }

            return null;
        }

        // If it's a URL, fetch and convert to base64
        try {
            $contents = file_get_contents($logoUrl);
            if ($contents !== false) {
                return base64_encode($contents);
            }
        } catch (\Exception) {
            // Failed to fetch logo, return null
        }

        return null;
    }
}
