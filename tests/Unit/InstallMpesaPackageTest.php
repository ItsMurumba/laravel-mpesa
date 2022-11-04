<?php

namespace Itsmurumba\Mpesa\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallMpesaPackageTest extends TestCase
{

    function the_install_command_copis_the_configuration()
    {
        if (File::exists(config_path('mpesa.php'))) {
            unlink(config_path('mpesa.php'));
        }

        $this->assertFalse(File::exists(config_path('mpesa.php')));

        Artisan::call('mpesa:install');

        $this->assertTrue(File::exists(config_path('mpesa.php')));
    }

    public function when_a_config_file_is_present_users_can_choose_to_not_overwrite_it(){
        File::put(config_path('mpesa.php'), 'test contens');
        $this->assertTrue(File::exists(config_path('mpesa.php')));

        $command = $this->artisan('mpesa:install');

        $command->expectsConfirmation(
            'Config file already exists. Do you want to overwrite it?',
            'no'
        );

        // We should see a message that our file was not overwritten
        $command->expectsOutput('Existing configuration was not overwritten');

        // Assert that the original contents of the config file remain
        $this->assertEquals('test contents', file_get_contents(config_path('mpesa.php')));

        //Clean up
        unlink(config_path('mpesa.php'));

    }


    public function when_a_config_file_is_present_users_can_choose_to_overwrite_it(){
        File::put(config_path('mpesa.php'), 'test contens');
        $this->assertTrue(File::exists(config_path('mpesa.php')));

        $command = $this->artisan('mpesa:install');

        $command->expectsConfirmation(
            'Config file already exists. Do you want to overwrite it?',
            'yes'
        );

        $command->execute();

        // We should see a message that our file was not overwritten
        $command->expectsOutput('Overwriting configuration file...');

        // Assert that the original contents of the config file remain
        $this->assertEquals(
            file_get_contents(__DIR__.'/../config/config.php'),
            file_get_contents(config_path('mpesa.php'))
        );

        //Clean up
        unlink(config_path('mpesa.php'));

    }
}
