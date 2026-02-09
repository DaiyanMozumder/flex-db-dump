<?php

namespace Flex\DbDump;

use Illuminate\Support\ServiceProvider;

class FlexDbDumpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/flex-db-dump.php' => config_path('flex-db-dump.php'),
        ], 'flex-db-dump-config');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
