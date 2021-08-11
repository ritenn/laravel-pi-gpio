<?php

namespace Ritenn\PiGpio\Main;


use Ritenn\PiGpio\Interfaces\FilesystemInterface;
use Ritenn\PiGpio\Interfaces\GpioInterface;
use Ritenn\PiGpio\Interfaces\PwmInterface;

class Pin
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var GpioInterface
     */
    private $gpio;

    /**
     * @var PwmInterface
     */
    private $pwm;

    /**
     * Pin constructor.
     * @param FilesystemInterface $filesystem
     * @param GpioInterface $gpio
     * @param PwmInterface $pwm
     */
    public function __construct(FilesystemInterface $filesystem, GpioInterface $gpio, PwmInterface $pwm)
    {
        $this->filesystem = $filesystem;
        $this->gpio = $gpio;
        $this->pwm = $pwm;
    }

    /**
     * @param int ...$pinsNumbers
     *
     * @return GpioInterface
     * @throws \Exception
     */
    public function set(int ...$pinsNumbers) : GpioInterface
    {
        return $this->gpio->set(...$pinsNumbers);
    }

    /**
     * @param int ...$pinsNumbers
     *
     * @return PwmInterface
     * @throws \Exception
     */
    public function setPwm(int ...$pinsNumbers) : PwmInterface
    {
        if ( ! $this->isPwmPin($pinsNumbers) )
        {
            throw new \Exception('Not all given pins are PWM');
        }

        return $this->pwm->set(...$pinsNumbers);
    }

    /**
     * @param array $pinsNumbers
     *
     * @return bool
     */
    public function isPwmPin(array $pinsNumbers) : bool
    {
        return collect($pinsNumbers)->filter(function($pin) {

                return in_array($pin, config('gpio.pwm_pins'));
            })
                ->count() === count($pinsNumbers);
    }
}