<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeUpdateNginxConfigurationCommand extends Command
{
    protected $signature = 'forge:update-nginx
        {site_id : The Forge site ID}
        {--config-file= : Path to a file containing the nginx configuration}';

    protected $description = 'Update the nginx configuration for a Forge site';

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');
        $configFile = $this->option('config-file');

        if (! $configFile) {
            $this->error('The --config-file option is required.');

            return self::FAILURE;
        }

        if (! file_exists($configFile)) {
            $this->error("File not found: {$configFile}");

            return self::FAILURE;
        }

        $configuration = file_get_contents($configFile);

        $this->info("Updating nginx configuration for site {$siteId}...");

        $forgeService->updateNginxConfiguration($siteId, $configuration);

        $this->info('Nginx configuration updated successfully!');

        return self::SUCCESS;
    }
}
