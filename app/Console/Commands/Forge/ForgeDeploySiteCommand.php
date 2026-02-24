<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeDeploySiteCommand extends Command
{
    protected $signature = 'forge:deploy
        {site_id : The Forge site ID}';

    protected $description = 'Trigger a deployment for a Forge site';

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');

        $this->info("Deploying site {$siteId}...");

        $forgeService->deploySite($siteId);

        $this->info('Deployment triggered successfully!');

        return self::SUCCESS;
    }
}
