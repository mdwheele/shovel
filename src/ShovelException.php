<?php

namespace Shovel;

class ShovelException extends \Exception
{
    public static function unsupportedDriver($driver)
    {
        return new static(sprintf('[%s] is not a supported driver type.', $driver));
    }

    public static function sourceRequired()
    {
        return new static('You must specify a source to dig from in your instructions.');
    }

    public static function destinationRequired()
    {
        return new static('You must specify a destination in your instructions.');
    }
}