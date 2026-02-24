<?php

namespace App\Services;

use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;

class ForgeService
{
    public function __construct(
        private Forge $forge,
    ) {}

    /**
     * Get the configured server ID.
     */
    protected function serverId(): int
    {
        return (int) config('services.forge.server_id');
    }

    /**
     * List all sites on the configured server.
     *
     * @return Site[]
     */
    public function listSites(): array
    {
        return $this->forge->sites($this->serverId());
    }

    /**
     * Get a specific site's details.
     */
    public function getSite(int $siteId): Site
    {
        return $this->forge->site($this->serverId(), $siteId);
    }

    /**
     * Delete a site from the Forge server.
     */
    public function deleteSite(int $siteId): void
    {
        $this->forge->deleteSite($this->serverId(), $siteId);
    }

    /**
     * Create a new site on the Forge server.
     */
    public function createSite(string $domain, string $projectType = 'php', string $phpVersion = 'php83', bool $isolated = true): Site
    {
        $data = [
            'domain' => $domain,
            'project_type' => $projectType,
            'php_version' => $phpVersion,
            'directory' => '/public',
            'isolated' => $isolated,
        ];

        $site = $this->forge->createSite($this->serverId(), $data);

        $this->forge->enableQuickDeploy($this->serverId(), $site->id);

        return $site;
    }

    /**
     * Get the aliases/domains for a site.
     *
     * @return array<int, string>
     */
    public function getSiteDomains(int $siteId): array
    {
        $site = $this->forge->site($this->serverId(), $siteId);

        return array_merge([$site->name], $site->aliases ?? []);
    }

    /**
     * Add alias domains to a site.
     */
    public function addSiteAliases(int $siteId, array $aliases): Site
    {
        return $this->forge->addSiteAliases($this->serverId(), $siteId, $aliases);
    }

    /**
     * Install a git repository on a site.
     */
    public function installGitRepository(int $siteId, string $repository, string $branch = 'main', string $provider = 'gitlab'): void
    {
        $this->forge->installGitRepositoryOnSite($this->serverId(), $siteId, [
            'provider' => $provider,
            'repository' => $repository,
            'branch' => $branch,
            'composer' => true,
        ]);
    }

    /**
     * Obtain a Let's Encrypt SSL certificate for a site.
     */
    public function obtainLetsEncryptCertificate(int $siteId, string $domain): void
    {
        $this->forge->obtainLetsEncryptCertificate($this->serverId(), $siteId, [
            'domains' => [$domain],
        ]);
    }

    /**
     * Update the deployment script for a site.
     */
    public function updateDeploymentScript(int $siteId, string $script): void
    {
        $this->forge->updateSiteDeploymentScript($this->serverId(), $siteId, $script);
    }

    /**
     * Get the current deployment script for a site.
     */
    public function getDeploymentScript(int $siteId): string
    {
        return $this->forge->siteDeploymentScript($this->serverId(), $siteId);
    }

    /**
     * Trigger a deployment for a site.
     */
    public function deploySite(int $siteId): void
    {
        $this->forge->deploySite($this->serverId(), $siteId);
    }

    /**
     * Update the nginx configuration for a site.
     */
    public function updateNginxConfiguration(int $siteId, string $configuration): void
    {
        $this->forge->updateSiteNginxFile($this->serverId(), $siteId, $configuration);
    }

    /**
     * Get the current nginx configuration for a site.
     */
    public function getNginxConfiguration(int $siteId): string
    {
        return $this->forge->siteNginxFile($this->serverId(), $siteId);
    }

    /**
     * Provision a full site with all steps.
     *
     * @param  array{domain: string, repository: string, branch?: string, project_type?: string, php_version?: string, isolated?: bool, deployment_script?: string, nginx_configuration?: string}  $params
     * @return array{site_id: int, domain: string, steps: array<string, string>}
     */
    public function provisionSite(array $params): array
    {
        $domain = $params['domain'];
        $repository = $params['repository'];
        $branch = $params['branch'] ?? 'main';
        $projectType = $params['project_type'] ?? 'php';
        $phpVersion = $params['php_version'] ?? 'php83';
        $isolated = $params['isolated'] ?? true;

        $steps = [];

        $site = $this->createSite($domain, $projectType, $phpVersion, $isolated);
        $steps['create_site'] = 'success';

        $this->installGitRepository($site->id, $repository, $branch);
        $steps['install_git_repository'] = 'success';

        $this->obtainLetsEncryptCertificate($site->id, $domain);
        $steps['obtain_ssl_certificate'] = 'success';

        if (isset($params['deployment_script'])) {
            $this->updateDeploymentScript($site->id, $params['deployment_script']);
            $steps['update_deployment_script'] = 'success';
        }

        if (isset($params['nginx_configuration'])) {
            $this->updateNginxConfiguration($site->id, $params['nginx_configuration']);
            $steps['update_nginx_configuration'] = 'success';
        }

        $this->deploySite($site->id);
        $steps['deploy_site'] = 'success';

        return [
            'site_id' => $site->id,
            'domain' => $domain,
            'steps' => $steps,
        ];
    }
}
