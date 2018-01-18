<?php

namespace Larrock\ComponentMigrateRocket;

use Illuminate\Support\ServiceProvider;
use Larrock\ComponentMigrateRocket\Commands\MigrateRocketCommand;

class LarrockComponentMigrateRocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('larrockmigraterocket', function() {
            $class = config('larrock.components.migraterocket', MigrateRocketComponent::class);
            return new $class;
        });

        $this->app->bind('command.migrateRocket:import', MigrateRocketCommand::class);
        $this->commands([
            'command.migrateRocket:import'
        ]);
    }
}