<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Forge\ForgeCreateSiteTool;
use App\Mcp\Tools\Forge\ForgeDeleteSiteTool;
use App\Mcp\Tools\Forge\ForgeDeploySiteTool;
use App\Mcp\Tools\Forge\ForgeGetDeploymentScriptTool;
use App\Mcp\Tools\Forge\ForgeGetNginxConfigurationTool;
use App\Mcp\Tools\Forge\ForgeGetSiteDomainsTool;
use App\Mcp\Tools\Forge\ForgeGetSiteTool;
use App\Mcp\Tools\Forge\ForgeInstallGitRepositoryTool;
use App\Mcp\Tools\Forge\ForgeListSitesTool;
use App\Mcp\Tools\Forge\ForgeProvisionSiteTool;
use App\Mcp\Tools\Forge\ForgeSetupSslTool;
use App\Mcp\Tools\Forge\ForgeUpdateDeploymentScriptTool;
use App\Mcp\Tools\Forge\ForgeUpdateNginxConfigurationTool;
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
        Pike Server provides invoice generation and Laravel Forge site provisioning capabilities.

        Available tools:
        - generate-invoice-pdf: Create PDF invoices from invoice data
        - get-invoice-settings: View current default settings
        - update-invoice-settings: Update default settings (from_address, payment_terms, notes, terms, logo_url, tax_percent)
        - forge-list-sites: List all sites on the Forge server
        - forge-get-site: Get detailed information about a specific site
        - forge-get-site-domains: Get all domains (primary + aliases) for a site
        - forge-delete-site: Delete a site from the Forge server (irreversible)
        - forge-create-site: Create a new site on the Forge server (isolated with quick deploy by default)
        - forge-install-git-repository: Install a git repository on a Forge site
        - forge-setup-ssl: Obtain a Let's Encrypt SSL certificate for a site
        - forge-update-deployment-script: Update the deployment script for a site
        - forge-get-deployment-script: Get the current deployment script for a site
        - forge-deploy-site: Trigger a deployment for a site
        - forge-update-nginx-configuration: Update the nginx configuration for a site
        - forge-get-nginx-configuration: Get the current nginx configuration for a site
        - forge-provision-site: Full site provisioning (create site, install repo, SSL, deploy script, nginx, deploy)

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
        ForgeListSitesTool::class,
        ForgeGetSiteTool::class,
        ForgeGetSiteDomainsTool::class,
        ForgeDeleteSiteTool::class,
        ForgeCreateSiteTool::class,
        ForgeInstallGitRepositoryTool::class,
        ForgeSetupSslTool::class,
        ForgeUpdateDeploymentScriptTool::class,
        ForgeGetDeploymentScriptTool::class,
        ForgeDeploySiteTool::class,
        ForgeUpdateNginxConfigurationTool::class,
        ForgeGetNginxConfigurationTool::class,
        ForgeProvisionSiteTool::class,
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
