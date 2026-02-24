<?php

use App\Services\ForgeService;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Certificate;
use Laravel\Forge\Resources\Site;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->forge = Mockery::mock(Forge::class);
    $this->service = new ForgeService($this->forge);

    config(['services.forge.server_id' => 123]);
});

describe('ForgeService', function () {
    describe('listSites()', function () {
        it('returns all sites on the server', function () {
            $sites = [
                new Site(['id' => 1, 'name' => 'example.com', 'status' => 'installed']),
                new Site(['id' => 2, 'name' => 'test.com', 'status' => 'installed']),
            ];

            $this->forge->shouldReceive('sites')
                ->once()
                ->with(123)
                ->andReturn($sites);

            $result = $this->service->listSites();

            expect($result)->toHaveCount(2);
            expect($result[0]->name)->toBe('example.com');
            expect($result[1]->name)->toBe('test.com');
        });

        it('returns empty array when no sites exist', function () {
            $this->forge->shouldReceive('sites')
                ->once()
                ->with(123)
                ->andReturn([]);

            $result = $this->service->listSites();

            expect($result)->toBeEmpty();
        });
    });

    describe('getSite()', function () {
        it('returns a specific site', function () {
            $site = new Site(['id' => 1, 'name' => 'example.com', 'status' => 'installed']);

            $this->forge->shouldReceive('site')
                ->once()
                ->with(123, 1)
                ->andReturn($site);

            $result = $this->service->getSite(1);

            expect($result)->toBeInstanceOf(Site::class);
            expect($result->id)->toBe(1);
            expect($result->name)->toBe('example.com');
        });
    });

    describe('deleteSite()', function () {
        it('deletes a site from the server', function () {
            $this->forge->shouldReceive('deleteSite')
                ->once()
                ->with(123, 1);

            $this->service->deleteSite(1);
        });
    });

    describe('createSite()', function () {
        it('creates an isolated site with quick deploy by default', function () {
            $site = new Site(['id' => 1, 'name' => 'example.com', 'status' => 'installed', 'phpVersion' => 'php83']);

            $this->forge->shouldReceive('createSite')
                ->once()
                ->with(123, [
                    'domain' => 'example.com',
                    'project_type' => 'php',
                    'php_version' => 'php83',
                    'directory' => '/public',
                    'isolated' => true,
                ])
                ->andReturn($site);

            $this->forge->shouldReceive('enableQuickDeploy')
                ->once()
                ->with(123, 1);

            $result = $this->service->createSite('example.com');

            expect($result)->toBeInstanceOf(Site::class);
            expect($result->id)->toBe(1);
            expect($result->name)->toBe('example.com');
        });

        it('accepts custom project type and php version', function () {
            $site = new Site(['id' => 2, 'name' => 'test.com']);

            $this->forge->shouldReceive('createSite')
                ->once()
                ->with(123, [
                    'domain' => 'test.com',
                    'project_type' => 'html',
                    'php_version' => 'php82',
                    'directory' => '/public',
                    'isolated' => true,
                ])
                ->andReturn($site);

            $this->forge->shouldReceive('enableQuickDeploy')
                ->once()
                ->with(123, 2);

            $result = $this->service->createSite('test.com', 'html', 'php82');

            expect($result->id)->toBe(2);
        });

        it('can disable isolation', function () {
            $site = new Site(['id' => 3, 'name' => 'shared.com']);

            $this->forge->shouldReceive('createSite')
                ->once()
                ->with(123, [
                    'domain' => 'shared.com',
                    'project_type' => 'php',
                    'php_version' => 'php83',
                    'directory' => '/public',
                    'isolated' => false,
                ])
                ->andReturn($site);

            $this->forge->shouldReceive('enableQuickDeploy')
                ->once()
                ->with(123, 3);

            $result = $this->service->createSite('shared.com', 'php', 'php83', false);

            expect($result->id)->toBe(3);
        });
    });

    describe('getSiteDomains()', function () {
        it('returns primary domain, aliases, and certificate domains', function () {
            $site = new Site(['id' => 1, 'name' => 'example.com', 'aliases' => ['www.example.com']]);

            $this->forge->shouldReceive('site')
                ->once()
                ->with(123, 1)
                ->andReturn($site);

            $this->forge->shouldReceive('certificates')
                ->once()
                ->with(123, 1)
                ->andReturn([
                    new Certificate(['domain' => 'example.com']),
                    new Certificate(['domain' => 'custom.example.com']),
                ]);

            $result = $this->service->getSiteDomains(1);

            expect($result)->toBe(['example.com', 'www.example.com', 'custom.example.com']);
        });

        it('returns only primary domain when no aliases or extra certs', function () {
            $site = new Site(['id' => 1, 'name' => 'example.com', 'aliases' => []]);

            $this->forge->shouldReceive('site')
                ->once()
                ->with(123, 1)
                ->andReturn($site);

            $this->forge->shouldReceive('certificates')
                ->once()
                ->with(123, 1)
                ->andReturn([
                    new Certificate(['domain' => 'example.com']),
                ]);

            $result = $this->service->getSiteDomains(1);

            expect($result)->toBe(['example.com']);
        });
    });

    describe('addSiteAliases()', function () {
        it('adds aliases to a site', function () {
            $site = new Site(['id' => 1, 'name' => 'example.com', 'aliases' => ['www.example.com']]);

            $this->forge->shouldReceive('addSiteAliases')
                ->once()
                ->with(123, 1, ['www.example.com'])
                ->andReturn($site);

            $result = $this->service->addSiteAliases(1, ['www.example.com']);

            expect($result)->toBeInstanceOf(Site::class);
        });
    });

    describe('installGitRepository()', function () {
        it('installs a git repository on a site', function () {
            $this->forge->shouldReceive('installGitRepositoryOnSite')
                ->once()
                ->with(123, 1, [
                    'provider' => 'gitlab',
                    'repository' => 'user/repo',
                    'branch' => 'main',
                    'composer' => true,
                ]);

            $this->service->installGitRepository(1, 'user/repo');
        });

        it('accepts custom branch and provider', function () {
            $this->forge->shouldReceive('installGitRepositoryOnSite')
                ->once()
                ->with(123, 1, [
                    'provider' => 'github',
                    'repository' => 'org/project',
                    'branch' => 'develop',
                    'composer' => true,
                ]);

            $this->service->installGitRepository(1, 'org/project', 'develop', 'github');
        });
    });

    describe('obtainLetsEncryptCertificate()', function () {
        it('obtains ssl certificate for a site', function () {
            $this->forge->shouldReceive('obtainLetsEncryptCertificate')
                ->once()
                ->with(123, 1, [
                    'domains' => ['example.com'],
                ])
                ->andReturn(new Certificate(['id' => 1]));

            $this->service->obtainLetsEncryptCertificate(1, 'example.com');
        });
    });

    describe('updateDeploymentScript()', function () {
        it('updates the deployment script', function () {
            $this->forge->shouldReceive('updateSiteDeploymentScript')
                ->once()
                ->with(123, 1, 'cd /home/forge && git pull');

            $this->service->updateDeploymentScript(1, 'cd /home/forge && git pull');
        });
    });

    describe('getDeploymentScript()', function () {
        it('returns the current deployment script', function () {
            $this->forge->shouldReceive('siteDeploymentScript')
                ->once()
                ->with(123, 1)
                ->andReturn('cd /home/forge && git pull');

            $result = $this->service->getDeploymentScript(1);

            expect($result)->toBe('cd /home/forge && git pull');
        });
    });

    describe('deploySite()', function () {
        it('triggers a deployment', function () {
            $this->forge->shouldReceive('deploySite')
                ->once()
                ->with(123, 1);

            $this->service->deploySite(1);
        });
    });

    describe('updateNginxConfiguration()', function () {
        it('updates the nginx configuration', function () {
            $this->forge->shouldReceive('updateSiteNginxFile')
                ->once()
                ->with(123, 1, 'server { }');

            $this->service->updateNginxConfiguration(1, 'server { }');
        });
    });

    describe('getNginxConfiguration()', function () {
        it('returns the current nginx configuration', function () {
            $this->forge->shouldReceive('siteNginxFile')
                ->once()
                ->with(123, 1)
                ->andReturn('server { listen 80; }');

            $result = $this->service->getNginxConfiguration(1);

            expect($result)->toBe('server { listen 80; }');
        });
    });

    describe('provisionSite()', function () {
        it('runs all provisioning steps in sequence', function () {
            $site = new Site(['id' => 42, 'name' => 'example.com']);

            $this->forge->shouldReceive('createSite')
                ->once()
                ->andReturn($site);

            $this->forge->shouldReceive('enableQuickDeploy')
                ->once()
                ->with(123, 42);

            $this->forge->shouldReceive('installGitRepositoryOnSite')
                ->once()
                ->with(123, 42, Mockery::type('array'));

            $this->forge->shouldReceive('obtainLetsEncryptCertificate')
                ->once()
                ->with(123, 42, ['domains' => ['example.com']])
                ->andReturn(new Certificate(['id' => 1]));

            $this->forge->shouldReceive('updateSiteDeploymentScript')
                ->once()
                ->with(123, 42, 'deploy.sh content');

            $this->forge->shouldReceive('updateSiteNginxFile')
                ->once()
                ->with(123, 42, 'nginx config');

            $this->forge->shouldReceive('deploySite')
                ->once()
                ->with(123, 42);

            $result = $this->service->provisionSite([
                'domain' => 'example.com',
                'repository' => 'user/repo',
                'deployment_script' => 'deploy.sh content',
                'nginx_configuration' => 'nginx config',
            ]);

            expect($result['site_id'])->toBe(42);
            expect($result['domain'])->toBe('example.com');
            expect($result['steps'])->toHaveKeys([
                'create_site',
                'install_git_repository',
                'obtain_ssl_certificate',
                'update_deployment_script',
                'update_nginx_configuration',
                'deploy_site',
            ]);
        });

        it('skips optional steps when not provided', function () {
            $site = new Site(['id' => 10, 'name' => 'minimal.com']);

            $this->forge->shouldReceive('createSite')->once()->andReturn($site);
            $this->forge->shouldReceive('enableQuickDeploy')->once()->with(123, 10);
            $this->forge->shouldReceive('installGitRepositoryOnSite')->once();
            $this->forge->shouldReceive('obtainLetsEncryptCertificate')->once()->andReturn(new Certificate(['id' => 1]));
            $this->forge->shouldReceive('deploySite')->once();

            $this->forge->shouldNotReceive('updateSiteDeploymentScript');
            $this->forge->shouldNotReceive('updateSiteNginxFile');

            $result = $this->service->provisionSite([
                'domain' => 'minimal.com',
                'repository' => 'user/repo',
            ]);

            expect($result['steps'])->not->toHaveKey('update_deployment_script');
            expect($result['steps'])->not->toHaveKey('update_nginx_configuration');
        });
    });
});
