<?php

namespace Itsmurumba\Mpesa;

use Illuminate\Support\ServiceProvider;

class MpesaServiceProvider extends ServiceProvider{

    /**
     * Publishes all the config file this package needs to function
     */
    public function boot()
    {
        $config = realpath(__DIR__.'/../resources/config/mpesa.php');

        $this->publishes([
           $config => config_path('mpesa.php')
        ]);
    }

    /**
     * Register the application services
     */
    public function register()
    {
        $this->app->bind('laravel-mpesa', function(){

            return new Mpesa;

        });
    }

    /**
     * Get the services provided by the provider
     * @return array
     */
    public function provides(){

        return ['laravel-mpesa'];

    }
}









