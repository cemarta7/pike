<?php

use App\Mcp\Servers\PikeServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('pike', PikeServer::class);
