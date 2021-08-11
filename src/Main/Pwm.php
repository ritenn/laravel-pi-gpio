<?php

namespace Ritenn\PiGpio\Main;


use Ritenn\PiGpio\Interfaces\FilesystemInterface;
use Illuminate\Support\Collection;
use Ritenn\PiGpio\Interfaces\PwmInterface;

class Pwm implements PwmInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var Collection - int pin_numbers
     */
    private $pins;

    /**
     * High state
     */
    private const LOW = 0;

    /**
     * Low state
     */
    private const HIGH = 1;

    /**
     * Gpio constructor.
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param int ...$pinsNumbers
     * 
     * @return $this
     * @throws \Exception
     */
    public function set(int ...$pinsNumbers) : PwmInterface
    {
        $pinsNumbers = $this->gpioPinsToPwmNumbers(...$pinsNumbers);

        $this->exportMultipleIfUnexported(... $pinsNumbers);

        $this->pins = collect($pinsNumbers);

        usleep(50000); // wait for pin(s) to be exported

        return $this;
    }

    /**
     * @param int ...$pinsNumbers
     * @return Collection
     */
    private function gpioPinsToPwmNumbers(int ...$pinsNumbers) : Collection
    {
        return collect($pinsNumbers)
            ->map(function($pin) {

                return collect(config('gpio.pwm_pins'))->search($pin);
            });
    }

    /**
     * @return Collection [pin_number => state]
     * @throws \Exception
     */
    public function values() : Collection
    {
        return $this->pins->mapWithKeys(function($pin) {

            return [ $pin => $this->getValue($pin) ];
        });
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function unexport() : bool
    {
        try {

            $this->pins->each(function($pin) {

                $this->unexportSingle($pin);
            });

            $this->pins = collect([]);

        } catch (\Exception $exception) {

            \Log::error( '[Error during GPIO unexport] ' . $exception->getMessage() );

            return false;
        }

        return true;
    }

    /**
     * @param int $pin
     * @param int $state
     *
     * @return bool
     * @throws \Exception
     */
    public function setDutyCycle(int $pin, int $percentage = 0) : bool
    {
        if ( $percentage < 0 || $percentage > 100 )
        {
            throw new \Exception('Invalid pin state.');
        }

        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'pwm' . $pin . DIRECTORY_SEPARATOR . 'duty_cycle';

        $percentage = $percentage * ( $this->getPeriod($pin) / 100 );

        return $this->filesystem->write($filename, $percentage);
    }

    /**
     * @param int $pin
     *
     * @return bool
     * @throws \Exception
     */
    public function getDutyCycle(int $pin) : int
    {
        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'pwm' . $pin . DIRECTORY_SEPARATOR . 'duty_cycle';

        return $this->filesystem->read($filename);
    }

    /**
     * @param int $percentage
     *
     * @return $this
     * @throws \Exception
     */
    public function setThrotle(int $percentage = 0) : PwmInterface
    {
        $this->pins->each(function($pin) use ($percentage) {

            $this->setDutyCycle($pin, $percentage);
        });

        return $this;
    }

    /**
     * @param int $pin
     * @param int $state
     *
     * @return bool
     * @throws \Exception
     */
    public function setPeriod(int $pin, int $period) : bool
    {
        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'pwm' . $pin . DIRECTORY_SEPARATOR . 'period';

        return $this->filesystem->write($filename, $period);
    }

    /**
     * @param int $pin
     *
     * @return int
     * @throws \Exception
     */
    public function getPeriod(int $pin) : int
    {
        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'pwm' . $pin . DIRECTORY_SEPARATOR . 'period';

        return $this->filesystem->read($filename);
    }

    /**
     * @return PwmInterface
     *
     * @throws \Exception
     */
    public function enable() : PwmInterface
    {
        $this->pins->each(function($pin) {

            $this->setEnable($pin, self::HIGH);
        });

        return $this;
    }

    /**
     * @return PwmInterface
     * @throws \Exception
     */
    public function disable() : PwmInterface
    {
        $this->pins->each(function($pin) {

            $this->setEnable($pin, self::LOW);
        });

        $this->unexport();

        return $this;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function isEnabled() : Collection
    {
        return $this->pins->mapWithKeys(function($pin) {

            return [ $pin => $this->getEnable($pin) ];
        });
    }

    /**
     * @param int $pin
     * @param int $enable
     * @return bool
     * @throws \Exception
     */
    private function setEnable(int $pin, int $enable) : bool
    {
        if ( ! in_array($enable, array(0, 1)) )
        {
            throw new \Exception('Invalid enable state.');
        }

        if ( $this->getPeriod($pin) === 0 )
        {
            $this->setPeriod($pin, '10000000');
        }

        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'pwm' . $pin . DIRECTORY_SEPARATOR . 'enable';

        return $this->filesystem->write($filename, $enable);
    }

    /**
     * @param int $pin
     * @return bool
     * @throws \Exception
     */
    private function getEnable(int $pin) : bool
    {
        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'pwm' . $pin . DIRECTORY_SEPARATOR . 'enable';

        return $this->filesystem->read($filename);
    }

    /**
     * @param int ...$pinsNumbers
     *
     * @throws \Exception
     */
    private function exportMultipleIfUnexported(int ...$pinsNumbers) : void
    {
        $this->getUnexportedPins(...$pinsNumbers)
            ->each(function($pin) {

                $this->exportSingle($pin);
            });
    }

    /**
     * @param int ...$pinsNumbers
     *
     * @return Collection
     */
    private function getUnexportedPins(int ...$pinsNumbers) : Collection
    {
        return collect($pinsNumbers)
            ->diff( $this->filesystem->getExportedPins(true) );
    }

    /**
     * @param int $pin
     *
     * @return bool
     * @throws \Exception
     */
    private function exportSingle(int $pin) : bool
    {
        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'export';

        return $this->filesystem->write($filename, $pin);
    }

    /**
     * @param int $pin
     *
     * @return bool
     * @throws \Exception
     */
    private function unexportSingle(int $pin) : bool
    {
        $filename = $this->filesystem->pwmPath . DIRECTORY_SEPARATOR . 'unexport';

        return $this->filesystem->write($filename, $pin);
    }
}