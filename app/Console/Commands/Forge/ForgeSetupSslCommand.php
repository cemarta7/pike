<?php

namespace App\Console\Commands\Forge;

use App\Services\ForgeService;
use Illuminate\Console\Command;

class ForgeSetupSslCommand extends Command
{
    protected $signature = 'forge:setup-ssl
        {site_id : The Forge site ID}
        {domain : The domain to issue the certificate for}';

    protected $description = "Obtain a Let's Encrypt SSL certificate for a Forge site";

    public function handle(ForgeService $forgeService): int
    {
        $siteId = (int) $this->argument('site_id');
        $domain = $this->argument('domain');

        $this->info("Obtaining SSL certificate for {$domain} on site {$siteId}...");

        $forgeService->obtainLetsEncryptCertificate($siteId, $domain);

        $this->info('SSL certificate obtained successfully!');

        return self::SUCCESS;
    }
}
