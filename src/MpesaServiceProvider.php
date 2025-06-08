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

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $config => $this->app->configPath('mpesa.php')
            ], 'mpesa-config');

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
        $this->app->bind('laravel-mpesa', function () {

            return new Mpesa();
        });
    }

    /**
     * Get the services provided by the provider
     * @return array
     */
    public function provides()
    {

        return ['laravel-mpesa'];
    }
}
