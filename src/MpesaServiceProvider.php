<?php

namespace Itsmurumba\Mpesa;

use Illuminate\Support\ServiceProvider;
use Itsmurumba\Mpesa\Console\InstallMpesaPackage;

class MpesaServiceProvider extends ServiceProvider
{

    /**
     * Publishes all the config file this package needs to function
     */
    public function boot()
    {
        $config = realpath(__DIR__ . '/../config/mpesa.php');
        $migrations = realpath(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $config => $this->app->configPath('mpesa.php')
            ], 'mpesa-config');

            if ($migrations) {
                $this->publishes([
                    $migrations => $this->app->databasePath('migrations')
                ], 'mpesa-migrations');
            }

            $this->commands([
                InstallMpesaPackage::class,
            ]);
        }
    }

    /**
     * Register the application services
     */
    public function register()
    {
        // Manager used by the facade: Mpesa::for('profile-key')->...
        $this->app->singleton('mpesa', function () {
            return new MpesaManager();
        });

        // Backwards compatible binding for anyone resolving the original service.
        $this->app->bind('laravel-mpesa', function ($app) {
            return $app->make('mpesa')->defaultInstance();
        });
    }

    /**
     * Get the services provided by the provider
     * @return array
     */
    public function provides()
    {

        return ['laravel-mpesa', 'mpesa'];
    }
}
