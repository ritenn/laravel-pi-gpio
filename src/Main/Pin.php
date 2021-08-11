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

        $this->exportMultipleIfUnexported(... $pinsNumbers);

        $this->pins = collect($pinsNumbers);

        usleep(50000); // wait for pin(s) to be exported

        return $this;

        $this->pins->each(function($pin) {

            $this->setDirection($pin, 'out');
        });

        return $this;

        $this->pins->each(function($pin) {

            $this->setDirection($pin, 'in');
        });

        return $this;

        $this->pins->each(function($pin) {

            $this->setValue($pin, self::LOW);
        });

        return $this;

        $this->pins->each(function($pin) {

            $this->setValue($pin, self::HIGH);
        });

        return $this;

        return $this->pins->mapWithKeys(function($pin) {

            return [ $pin => $this->getValue($pin) ];
        });

        return $this->pins->mapWithKeys(function($pin) {

            return [ $pin => $this->getDirection($pin) ];
        });

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

        if ( ! in_array($state, array(0, 1)) )
        {
            throw new \Exception('Invalid pin state.');
        }

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'value';

        return $this->filesystem->write($filename, $state);

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'value';

        return $this->filesystem->read($filename);

        if ( ! in_array($direction, array('in', 'out')) )
        {
            throw new \Exception('Invalid pin direction.');
        }

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'direction';

        return $this->filesystem->write($filename, $direction);

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'gpio' . $pin . DIRECTORY_SEPARATOR . 'direction';

        return $this->filesystem->read($filename) === 'in' ? 'INPUT' : 'OUTPUT';

        $this->getUnexportedPins(...$pinsNumbers)
            ->each(function($pin) {

                $this->exportSingle($pin);
            });

        return collect($pinsNumbers)
            ->diff( $this->filesystem->getExportedPins() );

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'export';

        return $this->filesystem->write($filename, $pin);

        $filename = $this->filesystem->gpioPath . DIRECTORY_SEPARATOR . 'unexport';

        return $this->filesystem->write($filename, $pin);
    }
}