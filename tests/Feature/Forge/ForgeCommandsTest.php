<?php

use App\Services\ForgeService;
use Laravel\Forge\Resources\Site;

use function Pest\Laravel\mock;

describe('Forge Artisan Commands', function () {
    describe('forge:list-sites', function () {
        it('lists sites in a table', function () {
            mock(ForgeService::class)
                ->shouldReceive('listSites')
                ->once()
                ->andReturn([
                    new Site(['id' => 1, 'name' => 'example.com', 'status' => 'installed', 'repository' => 'user/repo', 'repositoryBranch' => 'main', 'phpVersion' => 'php83']),
                ]);

            $this->artisan('forge:list-sites')
                ->assertSuccessful();
        });
    });

    describe('forge:site', function () {
        it('displays site details', function () {
            mock(ForgeService::class)
                ->shouldReceive('getSite')
                ->once()
                ->with(1)
                ->andReturn(new Site([
                    'id' => 1, 'name' => 'example.com', 'status' => 'installed',
                    'directory' => '/public', 'phpVersion' => 'php83',
                    'projectType' => 'php', 'repository' => 'user/repo',
                    'repositoryProvider' => 'gitlab', 'repositoryBranch' => 'main',
                    'repositoryStatus' => 'installed', 'quickDeploy' => false,
                    'deploymentStatus' => null, 'isSecured' => true,
                    'username' => 'forge', 'createdAt' => '2024-01-01',
                ]));

            $this->artisan('forge:site', ['site_id' => 1])
                ->assertSuccessful();
        });
    });

    describe('forge:delete-site', function () {
        it('deletes a site when confirmed', function () {
            mock(ForgeService::class)
                ->shouldReceive('deleteSite')
                ->once()
                ->with(1);

            $this->artisan('forge:delete-site', ['site_id' => 1])
                ->expectsConfirmation('Are you sure you want to delete site 1? This action is irreversible.', 'yes')
                ->assertSuccessful();
        });

        it('cancels when not confirmed', function () {
            mock(ForgeService::class)
                ->shouldNotReceive('deleteSite');

            $this->artisan('forge:delete-site', ['site_id' => 1])
                ->expectsConfirmation('Are you sure you want to delete site 1? This action is irreversible.', 'no')
                ->assertSuccessful();
        });
    });

    describe('forge:site-domains', function () {
        it('lists site domains', function () {
            mock(ForgeService::class)
                ->shouldReceive('getSiteDomains')
                ->once()
                ->with(1)
                ->andReturn(['example.com', 'www.example.com', 'app.example.com']);

            $this->artisan('forge:site-domains', ['site_id' => 1])
                ->assertSuccessful();
        });
    });

    describe('forge:create-site', function () {
        it('creates an isolated site by default', function () {
            mock(ForgeService::class)
                ->shouldReceive('createSite')
                ->once()
                ->with('example.com', 'php', 'php83', true)
                ->andReturn(new Site(['id' => 1, 'name' => 'example.com', 'status' => 'installed', 'phpVersion' => 'php83']));

            $this->artisan('forge:create-site', ['domain' => 'example.com'])
                ->assertSuccessful();
        });
    });

    describe('forge:install-repo', function () {
        it('installs a git repository', function () {
            mock(ForgeService::class)
                ->shouldReceive('installGitRepository')
                ->once()
                ->with(1, 'user/repo', 'main', 'gitlab');

            $this->artisan('forge:install-repo', [
                'site_id' => 1,
                'repository' => 'user/repo',
            ])->assertSuccessful();
        });
    });

    describe('forge:setup-ssl', function () {
        it('obtains ssl certificate', function () {
            mock(ForgeService::class)
                ->shouldReceive('obtainLetsEncryptCertificate')
                ->once()
                ->with(1, 'example.com');

            $this->artisan('forge:setup-ssl', [
                'site_id' => 1,
                'domain' => 'example.com',
            ])->assertSuccessful();
        });
    });

    describe('forge:update-deploy-script', function () {
        it('fails without script-file option', function () {
            $this->artisan('forge:update-deploy-script', ['site_id' => 1])
                ->assertFailed();
        });

        it('fails with non-existent file', function () {
            $this->artisan('forge:update-deploy-script', [
                'site_id' => 1,
                '--script-file' => '/non/existent/file.sh',
            ])->assertFailed();
        });

        it('updates deployment script from file', function () {
            $tempFile = tempnam(sys_get_temp_dir(), 'deploy');
            file_put_contents($tempFile, 'cd /home/forge && git pull');

            mock(ForgeService::class)
                ->shouldReceive('updateDeploymentScript')
                ->once()
                ->with(1, 'cd /home/forge && git pull');

            $this->artisan('forge:update-deploy-script', [
                'site_id' => 1,
                '--script-file' => $tempFile,
            ])->assertSuccessful();

            unlink($tempFile);
        });
    });

    describe('forge:deploy', function () {
        it('triggers deployment', function () {
            mock(ForgeService::class)
                ->shouldReceive('deploySite')
                ->once()
                ->with(1);

            $this->artisan('forge:deploy', ['site_id' => 1])
                ->assertSuccessful();
        });
    });

    describe('forge:update-nginx', function () {
        it('fails without config-file option', function () {
            $this->artisan('forge:update-nginx', ['site_id' => 1])
                ->assertFailed();
        });

        it('updates nginx configuration from file', function () {
            $tempFile = tempnam(sys_get_temp_dir(), 'nginx');
            file_put_contents($tempFile, 'server { listen 80; }');

            mock(ForgeService::class)
                ->shouldReceive('updateNginxConfiguration')
                ->once()
                ->with(1, 'server { listen 80; }');

            $this->artisan('forge:update-nginx', [
                'site_id' => 1,
                '--config-file' => $tempFile,
            ])->assertSuccessful();

            unlink($tempFile);
        });
    });

    describe('forge:provision', function () {
        it('provisions a complete site', function () {
            mock(ForgeService::class)
                ->shouldReceive('provisionSite')
                ->once()
                ->andReturn([
                    'site_id' => 42,
                    'domain' => 'example.com',
                    'steps' => [
                        'create_site' => 'success',
                        'install_git_repository' => 'success',
                        'obtain_ssl_certificate' => 'success',
                        'deploy_site' => 'success',
                    ],
                ]);

            $this->artisan('forge:provision', [
                'domain' => 'example.com',
                'repository' => 'user/repo',
            ])->assertSuccessful();
        });
    });
});
