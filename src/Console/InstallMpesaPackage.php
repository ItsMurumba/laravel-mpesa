<?php

namespace Itsmurumba\Mpesa\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallMpesaPackage extends Command
{
    protected $signature = 'mpesa:install';

    protected $description = 'Install Mpesa Laravel Package';

    public function handle()
    {
        $this->info('Installing Laravel Mpesa......');
        $this->info('Publishing mpesa configuration');

        if (!$this->configExists('mpesa.php')) {
            $this->publishConfiguration();
            $this->info('Publishing mpesa configuration');
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwitting mpesa configuration file......');
                $this->publishConfiguration($force = true);
            } else {
                $this->info('Exiting. Mpesa configuration was not overwritten');
            }
        }

        $this->info('Installed Mpesa Package');
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function shouldOverwriteConfig()
    {
        return $this->confirm('Config file already exists. Do you want to overwrite it?', false);
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "Itsmurumba\Mpesa\MpesaServiceProvider",
            '--tag' => "mpesa-config"
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
