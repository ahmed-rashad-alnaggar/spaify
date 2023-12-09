<?php

namespace Alnaggar\Spaify;

use Illuminate\Support\ServiceProvider;

class SpaifyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot() : void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ScaffoldCommand::class
            ]);
        }
    }
}
