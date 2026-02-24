<?php

use App\Mcp\Servers\PikeServer;
use App\Mcp\Tools\Forge\ForgeCreateSiteTool;
use App\Mcp\Tools\Forge\ForgeDeleteSiteTool;
use App\Mcp\Tools\Forge\ForgeDeploySiteTool;
use App\Mcp\Tools\Forge\ForgeGetDeploymentScriptTool;
use App\Mcp\Tools\Forge\ForgeGetNginxConfigurationTool;
use App\Mcp\Tools\Forge\ForgeGetSiteDomainsTool;
use App\Mcp\Tools\Forge\ForgeGetSiteTool;
use App\Mcp\Tools\Forge\ForgeInstallGitRepositoryTool;
use App\Mcp\Tools\Forge\ForgeListSitesTool;
use App\Mcp\Tools\Forge\ForgeProvisionSiteTool;
use App\Mcp\Tools\Forge\ForgeSetupSslTool;
use App\Mcp\Tools\Forge\ForgeUpdateDeploymentScriptTool;
use App\Mcp\Tools\Forge\ForgeUpdateNginxConfigurationTool;
use App\Services\ForgeService;
use Laravel\Forge\Resources\Site;

use function Pest\Laravel\mock;

describe('Forge MCP Tools', function () {
    describe('ForgeListSitesTool', function () {
        it('returns list of sites', function () {
            mock(ForgeService::class)
                ->shouldReceive('listSites')
                ->once()
                ->andReturn([
                    new Site(['id' => 1, 'name' => 'example.com', 'status' => 'installed', 'repository' => 'user/repo', 'repositoryBranch' => 'main', 'phpVersion' => 'php83', 'projectType' => 'php']),
                ]);

            $response = PikeServer::tool(ForgeListSitesTool::class, []);

            $response->assertOk();
        });
    });

    describe('ForgeGetSiteTool', function () {
        it('returns site details', function () {
            mock(ForgeService::class)
                ->shouldReceive('getSite')
                ->once()
                ->with(1)
                ->andReturn(new Site([
                    'id' => 1, 'name' => 'example.com', 'status' => 'installed',
                    'aliases' => [], 'directory' => '/public', 'wildcards' => false,
                    'repository' => 'user/repo', 'repositoryProvider' => 'gitlab',
                    'repositoryBranch' => 'main', 'repositoryStatus' => 'installed',
                    'quickDeploy' => false, 'deploymentStatus' => null,
                    'projectType' => 'php', 'phpVersion' => 'php83',
                    'isSecured' => true, 'username' => 'forge',
                    'deploymentUrl' => '', 'createdAt' => '2024-01-01',
                    'tags' => [],
                ]));

            $response = PikeServer::tool(ForgeGetSiteTool::class, [
                'site_id' => 1,
            ]);

            $response->assertOk();
        });

        it('validates site_id is required', function () {
            $response = PikeServer::tool(ForgeGetSiteTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeDeleteSiteTool', function () {
        it('deletes a site', function () {
            mock(ForgeService::class)
                ->shouldReceive('deleteSite')
                ->once()
                ->with(1);

            $response = PikeServer::tool(ForgeDeleteSiteTool::class, [
                'site_id' => 1,
            ]);

            $response->assertOk();
        });

        it('validates site_id is required', function () {
            $response = PikeServer::tool(ForgeDeleteSiteTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeCreateSiteTool', function () {
        it('creates a site with required fields', function () {
            mock(ForgeService::class)
                ->shouldReceive('createSite')
                ->once()
                ->with('example.com', 'php', 'php83')
                ->andReturn(new Site(['id' => 1, 'name' => 'example.com', 'status' => 'installed', 'phpVersion' => 'php83']));

            $response = PikeServer::tool(ForgeCreateSiteTool::class, [
                'domain' => 'example.com',
            ]);

            $response->assertOk();
        });

        it('validates domain is required', function () {
            $response = PikeServer::tool(ForgeCreateSiteTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeInstallGitRepositoryTool', function () {
        it('installs a git repository', function () {
            mock(ForgeService::class)
                ->shouldReceive('installGitRepository')
                ->once()
                ->with(1, 'user/repo', 'main', 'gitlab');

            $response = PikeServer::tool(ForgeInstallGitRepositoryTool::class, [
                'site_id' => 1,
                'repository' => 'user/repo',
            ]);

            $response->assertOk();
        });

        it('validates site_id and repository are required', function () {
            $response = PikeServer::tool(ForgeInstallGitRepositoryTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeSetupSslTool', function () {
        it('obtains ssl certificate', function () {
            mock(ForgeService::class)
                ->shouldReceive('obtainLetsEncryptCertificate')
                ->once()
                ->with(1, 'example.com');

            $response = PikeServer::tool(ForgeSetupSslTool::class, [
                'site_id' => 1,
                'domain' => 'example.com',
            ]);

            $response->assertOk();
        });

        it('validates required fields', function () {
            $response = PikeServer::tool(ForgeSetupSslTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeUpdateDeploymentScriptTool', function () {
        it('updates deployment script', function () {
            mock(ForgeService::class)
                ->shouldReceive('updateDeploymentScript')
                ->once()
                ->with(1, 'cd /home/forge && git pull');

            $response = PikeServer::tool(ForgeUpdateDeploymentScriptTool::class, [
                'site_id' => 1,
                'script' => 'cd /home/forge && git pull',
            ]);

            $response->assertOk();
        });

        it('validates required fields', function () {
            $response = PikeServer::tool(ForgeUpdateDeploymentScriptTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeGetDeploymentScriptTool', function () {
        it('returns the deployment script', function () {
            mock(ForgeService::class)
                ->shouldReceive('getDeploymentScript')
                ->once()
                ->with(1)
                ->andReturn('cd /home/forge && git pull');

            $response = PikeServer::tool(ForgeGetDeploymentScriptTool::class, [
                'site_id' => 1,
            ]);

            $response->assertOk();
        });

        it('validates site_id is required', function () {
            $response = PikeServer::tool(ForgeGetDeploymentScriptTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeDeploySiteTool', function () {
        it('triggers deployment', function () {
            mock(ForgeService::class)
                ->shouldReceive('deploySite')
                ->once()
                ->with(1);

            $response = PikeServer::tool(ForgeDeploySiteTool::class, [
                'site_id' => 1,
            ]);

            $response->assertOk();
        });

        it('validates site_id is required', function () {
            $response = PikeServer::tool(ForgeDeploySiteTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeUpdateNginxConfigurationTool', function () {
        it('updates nginx configuration', function () {
            mock(ForgeService::class)
                ->shouldReceive('updateNginxConfiguration')
                ->once()
                ->with(1, 'server { }');

            $response = PikeServer::tool(ForgeUpdateNginxConfigurationTool::class, [
                'site_id' => 1,
                'configuration' => 'server { }',
            ]);

            $response->assertOk();
        });

        it('validates required fields', function () {
            $response = PikeServer::tool(ForgeUpdateNginxConfigurationTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeGetNginxConfigurationTool', function () {
        it('returns the nginx configuration', function () {
            mock(ForgeService::class)
                ->shouldReceive('getNginxConfiguration')
                ->once()
                ->with(1)
                ->andReturn('server { listen 80; }');

            $response = PikeServer::tool(ForgeGetNginxConfigurationTool::class, [
                'site_id' => 1,
            ]);

            $response->assertOk();
        });

        it('validates site_id is required', function () {
            $response = PikeServer::tool(ForgeGetNginxConfigurationTool::class, []);

            $response->assertHasErrors();
        });
    });

    describe('ForgeProvisionSiteTool', function () {
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

            $response = PikeServer::tool(ForgeProvisionSiteTool::class, [
                'domain' => 'example.com',
                'repository' => 'user/repo',
            ]);

            $response->assertOk();
        });

        it('validates domain and repository are required', function () {
            $response = PikeServer::tool(ForgeProvisionSiteTool::class, []);

            $response->assertHasErrors();
        });
    });
});
