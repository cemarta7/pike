<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeUpdateDeploymentScriptCommand extends Command
{
    protected $signature = 'forge:update-deploy-script
        {site_id : The Forge site ID}
        {--script-file= : Path to a file containing the deployment script}';

    protected $description = 'Update the deployment script for a Forge site';

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');
        $scriptFile = $this->option('script-file');

        if (! $scriptFile) {
            $this->error('The --script-file option is required.');

            return self::FAILURE;
        }

        if (! file_exists($scriptFile)) {
            $this->error("File not found: {$scriptFile}");

            return self::FAILURE;
        }

        $script = file_get_contents($scriptFile);

        $this->info("Updating deployment script for site {$siteId}...");

        $forgeService->updateDeploymentScript($siteId, $script);

        $this->info('Deployment script updated successfully!');

        return self::SUCCESS;
    }
}
