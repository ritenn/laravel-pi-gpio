<?php

declare(strict_types=1);

namespace Ritenn\PiGpio;


use Illuminate\Support\ServiceProvider;
use Ritenn\PiGpio\Filesystem\Filesystem;
use Ritenn\PiGpio\Interfaces\FilesystemInterface;
use Ritenn\PiGpio\Interfaces\GpioInterface;
use Ritenn\PiGpio\Interfaces\PwmInterface;
use Ritenn\PiGpio\Main\Gpio;
use Ritenn\PiGpio\Main\Pin;
use Ritenn\PiGpio\Main\Pwm;


class PiGpioServiceProvider extends ServiceProvider
{
    /**
     * Boot
     *
     * @return void
     */
    public function boot() : void
    {
//        $this->setCommands();
        $this->setConfig();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() : void
    {
        $this->bindings();

        $pin = app()->make(Pin::class);
    }

    /**
     * Set configs & allows to publish
     */
    private function setConfig() : void
    {
        $vendorPath = __DIR__ . '/Config/gpio.php';

        $this->publishes([
            $vendorPath  => config_path('gpio.php')
        ]);

        $this->mergeConfigFrom(
            $vendorPath, 'rplidar'
        );

    }

    /**
     * Set commands
     */
    private function setCommands() : void
    {
        $this->commands([
            ExecProcess::class,
        ]);
    }

    /**
     * Set bindings
     */
    private function bindings() : void
    {
        $this->app->bind(FilesystemInterface::class, Filesystem::class);
        $this->app->bind(GpioInterface::class, Gpio::class);
        $this->app->bind(PwmInterface::class, Pwm::class);
    }
}