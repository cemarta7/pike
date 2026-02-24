<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeDeleteSiteCommand extends Command
{
    protected $signature = 'forge:delete-site
        {site_id : The Forge site ID to delete}';

    protected $description = 'Delete a site from the configured Laravel Forge server';

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');

        if (! $this->confirm("Are you sure you want to delete site {$siteId}? This action is irreversible.")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $this->info("Deleting site {$siteId}...");

        $forgeService->deleteSite($siteId);

        $this->info('Site deleted successfully!');

        return self::SUCCESS;
    }
}
