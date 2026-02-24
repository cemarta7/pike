<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeProvisionSiteCommand extends Command
{
    protected $signature = 'forge:provision
        {domain : The domain name for the site}
        {repository : The git repository path (e.g., user/repo)}
        {--branch=main : The branch to deploy}
        {--project-type=php : The project type}
        {--php-version=php83 : The PHP version}
        {--no-isolation : Disable PHP user isolation (enabled by default)}
        {--deploy-script-file= : Path to a file containing the deployment script}
        {--nginx-config-file= : Path to a file containing the nginx configuration}';

    protected $description = 'Provision a complete site on Forge (create, install repo, SSL, deploy)';

    public function handle(ForgeService $forgeService): int
    {
        $params = [
            'domain' => $this->argument('domain'),
            'repository' => $this->argument('repository'),
            'branch' => $this->option('branch'),
            'project_type' => $this->option('project-type'),
            'php_version' => $this->option('php-version'),
            'isolated' => ! $this->option('no-isolation'),
        ];

        if ($deployScriptFile = $this->option('deploy-script-file')) {
            if (! file_exists($deployScriptFile)) {
                $this->error("Deploy script file not found: {$deployScriptFile}");

                return self::FAILURE;
            }
            $params['deployment_script'] = file_get_contents($deployScriptFile);
        }

        if ($nginxConfigFile = $this->option('nginx-config-file')) {
            if (! file_exists($nginxConfigFile)) {
                $this->error("Nginx config file not found: {$nginxConfigFile}");

                return self::FAILURE;
            }
            $params['nginx_configuration'] = file_get_contents($nginxConfigFile);
        }

        $this->info("Provisioning site: {$params['domain']}...");

        $result = $forgeService->provisionSite($params);

        $this->info('Site provisioned successfully!');
        $this->table(
            ['Site ID', 'Domain'],
            [[$result['site_id'], $result['domain']]],
        );

        $this->newLine();
        $this->info('Steps completed:');
        foreach ($result['steps'] as $step => $status) {
            $this->line("  {$step}: {$status}");
        }

        return self::SUCCESS;
    }
}
