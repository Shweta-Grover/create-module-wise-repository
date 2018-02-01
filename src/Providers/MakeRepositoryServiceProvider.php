<?php

namespace createmodulewiserepo\repo\Providers;

use Illuminate\Support\ServiceProvider;
use createmodulewiserepo\repo\Commands\MakeRepositoryCommand;

class MakeRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
               MakeRepositoryCommand::class
            ]);
        }
    }
}