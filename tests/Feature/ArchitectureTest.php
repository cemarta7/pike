<?php

describe('Architecture', function () {
    arch('mcp tools extend base tool')
        ->expect('App\Mcp\Tools')
        ->toExtend('Laravel\Mcp\Server\Tool')
        ->toHaveSuffix('Tool');

    arch('mcp servers extend base server')
        ->expect('App\Mcp\Servers')
        ->toExtend('Laravel\Mcp\Server')
        ->toHaveSuffix('Server');

    arch('services are classes')
        ->expect('App\Services')
        ->toBeClasses()
        ->toHaveSuffix('Service');

    arch('no debugging statements')
        ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
        ->not->toBeUsed();
});
