<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeGetSiteCommand extends Command
{
    protected $signature = 'forge:site
        {site_id : The Forge site ID}';

    protected $description = 'Get detailed information about a specific Forge site';

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');

        $this->info("Fetching site {$siteId}...");

        $site = $forgeService->getSite($siteId);

        $this->table(['Property', 'Value'], [
            ['ID', $site->id],
            ['Domain', $site->name],
            ['Status', $site->status],
            ['Directory', $site->directory ?: '-'],
            ['PHP Version', $site->phpVersion ?: '-'],
            ['Project Type', $site->projectType ?: '-'],
            ['Repository', $site->repository ?: '-'],
            ['Provider', $site->repositoryProvider ?: '-'],
            ['Branch', $site->repositoryBranch ?: '-'],
            ['Repo Status', $site->repositoryStatus ?: '-'],
            ['Quick Deploy', $site->quickDeploy ? 'Yes' : 'No'],
            ['Deploy Status', $site->deploymentStatus ?: '-'],
            ['HTTPS', $site->isSecured ? 'Yes' : 'No'],
            ['Username', $site->username ?: '-'],
            ['Created At', $site->createdAt ?: '-'],
        ]);

        return self::SUCCESS;
    }
}
