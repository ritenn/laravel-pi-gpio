<?php

namespace Ritenn\PiGpio\Filesystem;


use Illuminate\Support\Collection;
use Ritenn\PiGpio\Interfaces\FilesystemInterface;

class Filesystem implements FilesystemInterface
{
    public $gpioPath = '/sys/class/gpio';

    public $pwmPath = '/sys/class/pwm/pwmchip0';

    /**
     * @param string $filename
     *
     * @return string
     * @throws \Exception
     */
    public function read(string $filename) : string
    {
        if ( ! is_writable($filename) )
        {
            throw new \Exception('File is not writeable, check permissions.');
        }

        $fileResource = fopen($filename, 'rb');

        if ( ! is_resource($fileResource) )
        {
            throw new \Exception('Couldn\'t open file, check permissions.');
        }

        $content = fread($fileResource, filesize($filename));

        if ( $content === false )
        {
            throw new \Exception('Couldn\'t read file, check permissions.');
        }

        fclose($fileResource);

        return trim($content);
    }

    /**
     * @param string $filename
     * @param string $content
     *
     * @return bool
     * @throws \Exception
     */
    public function write(string $filename, string $content) : bool
    {
        if ( ! is_writable($filename) )
        {
            throw new \Exception('File is not writeable, check permissions.');
        }

        $fileResource = fopen($filename, 'a');

        if ( ! is_resource($fileResource) )
        {
            throw new \Exception('Couldn\'t open file, check permissions.');
        }

        if ( fwrite($fileResource, $content) === false )
        {
            throw new \Exception('Couldn\'t wirte to file.');
        }

        fclose($fileResource);

        return true;
    }

    /**
     * @param bool $pwmPins
     *
     * @return Collection
     */
    public function getExportedPins(bool $pwmPins = false) : Collection
    {
        $path = $pwmPins ? $this->pwmPath : $this->gpioPath;

        return collect(
            array_diff(
                scandir($path), array('..', '.', 'export', 'unexport')
            )
        )->filter(function($file) {

            return preg_match('/gpio[0-9]{1,2}/', $file, $matches);
        })
        ->map(function($file) {

            return (int) str_replace('gpio', '', $file);
        })
        ->values();
    }
}
