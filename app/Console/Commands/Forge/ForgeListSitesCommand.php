<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeListSitesCommand extends Command
{
    protected $signature = 'forge:list-sites';

    protected $description = 'List all sites on the configured Laravel Forge server';

    public function handle(ForgeService $forgeService): int
    {
        $this->info('Fetching sites...');

        $sites = $forgeService->listSites();

        if (empty($sites)) {
            $this->info('No sites found on this server.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Domain', 'Status', 'Repository', 'Branch', 'PHP Version'],
            array_map(fn ($site) => [
                $site->id,
                $site->name,
                $site->status,
                $site->repository ?: '-',
                $site->repositoryBranch ?: '-',
                $site->phpVersion ?: '-',
            ], $sites),
        );

        return self::SUCCESS;
    }
}
