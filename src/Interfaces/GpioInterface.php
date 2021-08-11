<?php

namespace Ritenn\PiGpio\Interfaces;


use Illuminate\Support\Collection;
use Ritenn\PiGpio\RplidarA1ServiceProvider;

interface GpioInterface
{
    /**
     * @param int ...$pinsNumbers
     *
     * @return $this
     * @throws \Exception
     */
    public function set(int ...$pinsNumbers) : GpioInterface;

    /**
     * @return $this
     * @throws \Exception
     */
    public function asOutput() : GpioInterface;

    /**
     * @return $this
     * @throws \Exception
     */
    public function asInput() : GpioInterface;

    /**
     * @return $this
     * @throws \Exception
     */
    public function setLow() : GpioInterface;

    /**
     * @return $this
     * @throws \Exception
     */
    public function setHigh() : GpioInterface;

    /**
     * @return Collection [pin_number => state]
     * @throws \Exception
     */
    public function values() : Collection;

    /**
     * @return Collection
     * @throws \Exception
     */
    public function directions() : Collection;

    /**
     * @return bool
     * @throws \Exception
     */
    public function unexport() : bool;
}