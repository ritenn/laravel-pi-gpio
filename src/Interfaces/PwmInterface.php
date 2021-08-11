<?php

namespace Ritenn\PiGpio\Interfaces;


use Illuminate\Support\Collection;
use Ritenn\PiGpio\RplidarA1ServiceProvider;

interface PwmInterface
{
    /**
     * @param int ...$pinsNumbers
     *
     * @return $this
     * @throws \Exception
     */
    public function set(int ...$pinsNumbers) : PwmInterface;

    /**
     * @return Collection [pin_number => state]
     * @throws \Exception
     */
    public function values() : Collection;

    /**
     * @return PwmInterface
     *
     * @throws \Exception
     */
    public function enable() : PwmInterface;

    /**
     * @return PwmInterface
     * @throws \Exception
     */
    public function disable() : PwmInterface;

    /**
     * @return Collection
     * @throws \Exception
     */
    public function isEnabled() : Collection;

    /**
     * @param int $percentage
     *
     * @return $this
     * @throws \Exception
     */
    public function setThrotle(int $percentage = 0) : self;
}