<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeCreateSiteCommand extends Command
{
    protected $signature = 'forge:create-site
        {domain : The domain name for the site}
        {--project-type=php : The project type (php, html, symfony, symfony_dev, symfony_four)}
        {--php-version=php83 : The PHP version}
        {--no-isolation : Disable PHP user isolation (enabled by default)}';

    protected $description = 'Create a new site on the configured Laravel Forge server';

    public function handle(ForgeService $forgeService): int
    {
        $domain = $this->argument('domain');

        $this->info("Creating site: {$domain}...");

        $site = $forgeService->createSite(
            $domain,
            $this->option('project-type'),
            $this->option('php-version'),
            ! $this->option('no-isolation'),
        );

        $this->info('Site created successfully!');
        $this->table(
            ['ID', 'Domain', 'Status', 'PHP Version'],
            [[$site->id, $site->name, $site->status, $site->phpVersion]],
        );

        return self::SUCCESS;
    }
}
