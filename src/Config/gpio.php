<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Define your pwm pins, by default on pi 4 it's GPIO18 and GPIO19
    |
    | Order is important:
    | first (key 0) is pwm channel 0 and second (key 1) is pwm channel 2
    |--------------------------------------------------------------------------
    */
    'pwm_pins' => [
        0 => 18,
        1 => 19
    ],
    /*
    |--------------------------------------------------------------------------
    | PWM path
    |--------------------------------------------------------------------------
    */
    'pwm_path' => '/sys/class/pwm/pwmchip0'
];