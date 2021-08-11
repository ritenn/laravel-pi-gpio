<?php

namespace Ritenn\PiGpio\Main;


use Ritenn\PiGpio\Interfaces\FilesystemInterface;
use Ritenn\PiGpio\Interfaces\GpioInterface;
use Illuminate\Support\Collection;

class Gpio implements GpioInterface
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
    public function set(int ...$pinsNumbers) : GpioInterface
    {
        $this->exportMultipleIfUnexported(... $pinsNumbers);

        $this->pins = collect($pinsNumbers);

        usleep(50000); // wait for pin(s) to be exported

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function asOutput() : GpioInterface
    {
        $this->pins->each(function($pin) {

            $this->setDirection($pin, 'out');
        });

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function asInput() : GpioInterface
    {
        $this->pins->each(function($pin) {

            $this->setDirection($pin, 'in');
        });

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setLow() : GpioInterface
    {
        $this->pins->each(function($pin) {

            $this->setValue($pin, self::LOW);
        });

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setHigh() : GpioInterface
    {
        $this->pins->each(function($pin) {

            $this->setValue($pin, self::HIGH);
        });

        return $this;
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
     * @return Collection
     * @throws \Exception
     */
    public function directions() : Collection
    {
        return $this->pins->mapWithKeys(function($pin) {

            return [ $pin => $this->getDirection($pin) ];
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
    private function setValue(int $pin, int $state) : bool
    {
        if ( ! in_array($state, array(0, 1)) )
        {
            throw new \Exception('Invalid pin state.');
        }

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'value';

        return $this->filesystem->write($filename, $state);
    }

    /**
     * @param int $pin
     *
     * @return bool
     * @throws \Exception
     */
    private function getValue(int $pin) : bool
    {
        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'value';

        return $this->filesystem->read($filename);
    }

    /**
     * @param int $pin
     * @param string $direction
     *
     * @return bool
     * @throws \Exception
     */
    private function setDirection(int $pin, string $direction) : bool
    {
        if ( ! in_array($direction, array('in', 'out')) )
        {
            throw new \Exception('Invalid pin direction.');
        }

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'direction';

        return $this->filesystem->write($filename, $direction);
    }

    /**
     * @param int $pin
     *
     * @return bool
     * @throws \Exception
     */
    private function getDirection(int $pin) : string
    {
        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'direction';

        return $this->filesystem->read($filename) === 'in' ? 'INPUT' : 'OUTPUT';
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
            ->diff( $this->filesystem->getExportedPins() );
    }

    /**
     * @param int $pin
     *
     * @return bool
     * @throws \Exception
     */
    private function exportSingle(int $pin) : bool
    {
        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'export';

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
        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'unexport';

        return $this->filesystem->write($filename, $pin);
    }
}