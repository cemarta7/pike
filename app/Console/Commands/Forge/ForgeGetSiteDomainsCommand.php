<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeGetSiteDomainsCommand extends Command
{
    protected $signature = 'forge:site-domains
        {site_id : The Forge site ID}';

    protected $description = 'List all domains (primary + aliases) for a Forge site';

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');

        $this->info("Fetching domains for site {$siteId}...");

        $domains = $forgeService->getSiteDomains($siteId);

        $this->table(
            ['Type', 'Domain'],
            collect($domains)->map(fn (string $domain, int $index) => [
                $index === 0 ? 'Primary' : 'Alias',
                $domain,
            ])->all(),
        );

        return self::SUCCESS;
    }
}
