<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GenerateInvoicePdfTool;
use App\Mcp\Tools\GetInvoiceSettingsTool;
use App\Mcp\Tools\UpdateInvoiceSettingsTool;
use Laravel\Mcp\Server;

class PikeServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Pike Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        Pike Server provides invoice generation capabilities.

        Available tools:
        - generate-invoice-pdf: Create PDF invoices from invoice data
        - get-invoice-settings: View current default settings
        - update-invoice-settings: Update default settings (from_address, payment_terms, notes, terms, logo_url, tax_percent)

        Default settings are automatically applied when generating invoices unless overridden.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        GenerateInvoicePdfTool::class,
        GetInvoiceSettingsTool::class,
        UpdateInvoiceSettingsTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
