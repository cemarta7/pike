<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeInstallGitRepositoryCommand extends Command
{
    protected $signature = 'forge:install-repo
        {site_id : The Forge site ID}
        {repository : The git repository path (e.g., user/repo)}
        {--branch=main : The branch to deploy}
        {--provider=gitlab : The git provider (github, gitlab, bitbucket, custom)}';

    protected $description = 'Install a git repository on a Forge site';

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');
        $repository = $this->argument('repository');

        $this->info("Installing repository {$repository} on site {$siteId}...");

        $forgeService->installGitRepository(
            $siteId,
            $repository,
            $this->option('branch'),
            $this->option('provider'),
        );

        $this->info('Repository installed successfully!');

        return self::SUCCESS;
    }
}
