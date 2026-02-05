# Pike

A Laravel-based MCP (Model Context Protocol) server for generating PDF invoices. Designed for LLM clients to create professional invoices programmatically.

## Requirements

- PHP 8.3+
- Laravel 12
- Composer

## Installation

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

## MCP Server

Pike exposes an MCP server with tools for invoice generation and settings management.

### Starting the MCP Inspector

```bash
php artisan mcp:inspector pike
```

### Available Tools

| Tool | Description |
|------|-------------|
| `generate-invoice-pdf` | Generate a PDF invoice from provided data |
| `get-invoice-settings` | Retrieve current default settings |
| `update-invoice-settings` | Update default invoice settings |

---

## Tools Documentation

### generate-invoice-pdf

Generates a PDF invoice and returns it as a base64-encoded string.

**Required Parameters:**
- `invoice_number` (string) - Invoice number (e.g., "INV-001")
- `bill_to_address` (string) - Customer billing address
- `line_items` (array) - Array of line items, each with:
  - `description` (string) - Item description
  - `quantity` (integer) - Quantity (default: 1)
  - `rate` (number, required) - Price per item

**Optional Parameters:**
- `from_address` (string) - Sender/company address
- `ship_to_address` (string) - Shipping address if different from billing
- `purchase_order` (string) - PO number reference
- `payment_terms` (string) - Payment terms (e.g., "Net 30")
- `notes` (string) - Additional notes
- `terms` (string) - Terms and conditions
- `tax_percent` (number) - Tax percentage (e.g., 8.25)
- `discount` (number) - Discount amount
- `shipping` (number) - Shipping cost
- `amount_paid` (number) - Amount already paid
- `logo_base64` (string) - Base64-encoded logo image

**Example:**
```json
{
  "invoice_number": "INV-001",
  "bill_to_address": "John Doe\n123 Main Street\nNew York, NY 10001",
  "line_items": [
    {
      "description": "Web Development Services",
      "quantity": 10,
      "rate": 150.00
    },
    {
      "description": "Hosting (Annual)",
      "quantity": 1,
      "rate": 299.00
    }
  ],
  "tax_percent": 8.25,
  "notes": "Thank you for your business!"
}
```

**Response:**
```json
{
  "pdf_base64": "JVBERi0xLjcK...",
  "mime_type": "application/pdf",
  "invoice_number": "INV-001"
}
```

---

### get-invoice-settings

Retrieves the current default settings for invoice generation.

**Parameters:** None

**Response:**
```json
{
  "from_address": "Your Company\n123 Business St\nCity, ST 12345",
  "payment_terms": "Net 30",
  "notes": "Thank you for your business!",
  "terms": "Payment due within 30 days.",
  "logo_url": "https://example.com/logo.png",
  "tax_percent": 8.25
}
```

---

### update-invoice-settings

Updates default invoice settings. Only provide fields you want to change.

**Parameters (all optional):**
- `from_address` (string) - Default sender/company address
- `payment_terms` (string) - Default payment terms
- `notes` (string) - Default notes
- `terms` (string) - Default terms and conditions
- `logo_url` (string) - URL or storage path to company logo
- `tax_percent` (number) - Default tax percentage (0-100)

**Example:**
```json
{
  "from_address": "Acme Corp\n456 Corporate Blvd\nLos Angeles, CA 90001",
  "payment_terms": "Due on Receipt",
  "tax_percent": 7.5
}
```

**Response:**
```json
{
  "success": true,
  "settings": {
    "from_address": "Acme Corp\n456 Corporate Blvd\nLos Angeles, CA 90001",
    "payment_terms": "Due on Receipt",
    "notes": "",
    "terms": "",
    "logo_url": "",
    "tax_percent": 7.5
  }
}
```

---

## Settings Storage

Invoice settings are stored in `storage/app/invoice-settings.json`. This file is created automatically with default values on first use.

### Logo Configuration

The `logo_url` setting supports:
- **Remote URLs**: `https://example.com/logo.png`
- **Local storage**: `storage/logos/company-logo.png`

For local logos, place the image file in `storage/app/logos/` and reference it as `storage/logos/filename.png`.

---

## Testing

```bash
# Run all tests
php artisan test --compact

# Run specific test files
php artisan test --compact --filter=GenerateInvoicePdfToolTest
php artisan test --compact --filter=InvoiceSettingsToolsTest

# Run static analysis
./vendor/bin/phpstan analyse
```

### Test Coverage

| Test File | Description |
|-----------|-------------|
| `GenerateInvoicePdfToolTest` | MCP tool tests for PDF generation |
| `InvoiceSettingsToolsTest` | MCP tool tests for settings management |
| `InvoiceServiceTest` | Unit tests for PDF generation service |
| `InvoiceSettingsServiceTest` | Unit tests for settings service |
| `ArchitectureTest` | Architecture tests for code conventions |

---

## Code Quality

```bash
# Run code formatter
vendor/bin/pint

# Run static analysis
./vendor/bin/phpstan analyse
```

---

## Project Structure

```
app/
├── Mcp/
│   ├── Servers/
│   │   └── PikeServer.php          # MCP server definition
│   └── Tools/
│       ├── GenerateInvoicePdfTool.php
│       ├── GetInvoiceSettingsTool.php
│       └── UpdateInvoiceSettingsTool.php
├── Services/
│   ├── InvoiceService.php          # PDF generation logic
│   └── InvoiceSettingsService.php  # Settings management
resources/
└── views/
    └── invoicePdf.blade.php        # Invoice PDF template
storage/
└── app/
    └── invoice-settings.json       # Settings file (auto-created)
```

---

## License

Proprietary - All rights reserved.
